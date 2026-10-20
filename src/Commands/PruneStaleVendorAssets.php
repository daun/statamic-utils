<?php

namespace Daun\StatamicUtils\Commands;

use Composer\InstalledVersions;
use Daun\StatamicUtils\Commands\Concerns\RegistersCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Prunes stale Vite-hashed assets left behind under public/vendor after
 * addon rebuilds. Statamic's AddonServiceProvider::registerVite() always
 * publishes into public_path('vendor'), so that's the only root scanned.
 */
#[Signature('utils:assets:prune {--force : Delete stale assets instead of running a dry run}')]
#[Description('Report and optionally delete stale Vite vendor assets published under public/vendor.')]
class PruneStaleVendorAssets extends Command
{
    use RegistersCommand;

    /**
     * Filenames that are always preserved, regardless of whether they
     * follow the hashed asset naming convention.
     */
    private const PRESERVED_FILENAMES = ['manifest.json', '.DS_Store', '.gitkeep', 'hot', '.htaccess'];

    private bool $failed = false;

    /** @var list<string>|null */
    private ?array $installedPackages = null;

    public function handle(): int
    {
        $this->failed = false;

        $root = public_path('vendor');

        if (! is_dir($root)) {
            $this->components->info("No vendor asset root found at [{$root}]. Nothing to prune.");

            return self::SUCCESS;
        }

        // Resolve symlinks once so it lines up with the realpath()-resolved
        // manifest/candidate paths discovered below (macOS commonly symlinks
        // temp directories, which would otherwise break prefix matching).
        $root = realpath($root) ?: $root;

        $manifests = $this->discoverManifests($root);

        if ($manifests === []) {
            $this->components->info("No Vite manifests found under [{$root}]. Nothing to prune.");

            return self::SUCCESS;
        }

        $manifestsByOutputRoot = [];

        foreach ($manifests as $manifest) {
            $outputRoot = $this->outputRootFor($manifest);

            if (! $this->pathIsWithin($outputRoot, $root)) {
                $this->failed = true;
                $this->components->error("Manifest resolves outside vendor asset root, skipped: {$manifest}");

                continue;
            }

            $manifestsByOutputRoot[$outputRoot][] = $manifest;
        }

        $force = (bool) $this->option('force');
        $totalStale = 0;
        $totalDeleted = 0;
        $affectedPackages = [];

        foreach ($manifestsByOutputRoot as $outputRoot => $outputManifests) {
            if (count($outputManifests) > 1) {
                $this->failed = true;
                $this->components->error("Multiple Vite manifests share output root [{$outputRoot}], skipped:");

                foreach ($outputManifests as $manifest) {
                    $this->line("  {$manifest}");
                }

                continue;
            }

            $result = $this->processManifest($outputManifests[0], $root, $force);

            if ($result === null) {
                continue;
            }

            $totalStale += $result['stale'];
            $totalDeleted += $result['deleted'];

            if ($result['package'] !== null) {
                $affectedPackages[$result['package']] = true;
            }
        }

        $this->newLine();

        if ($totalStale === 0) {
            if ($this->failed) {
                $this->components->error('No assets were pruned from skipped or invalid manifests.');

                return self::FAILURE;
            }

            $this->components->info('No stale vendor assets found.');

            return self::SUCCESS;
        }

        $packageCount = count($affectedPackages);

        if ($force) {
            $this->components->info("Deleted {$totalDeleted} stale asset(s) across {$packageCount} package(s).");
        } else {
            $this->components->warn(
                "Dry run: found {$totalStale} stale asset(s) across {$packageCount} package(s). No files were deleted."
            );
            $this->line('Rerun with --force to delete them: php artisan utils:assets:prune --force');
        }

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Recursively locate every manifest.json (flat or under a .vite
     * directory) beneath the given root, without visiting the same
     * manifest twice.
     *
     * @return list<string>
     */
    private function discoverManifests(string $root): array
    {
        $finder = Finder::create()
            ->files()
            ->in($root)
            ->ignoreDotFiles(false)
            ->name('manifest.json')
            ->sortByName();

        $seen = [];
        $manifests = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $path = $file->getRealPath() ?: $file->getPathname();

            if (isset($seen[$path])) {
                continue;
            }

            $seen[$path] = true;
            $manifests[] = $path;
        }

        return $manifests;
    }

    private function isViteManifest(string $manifestPath): bool
    {
        return basename(dirname($manifestPath)) === '.vite';
    }

    private function outputRootFor(string $manifestPath): string
    {
        return $this->isViteManifest($manifestPath)
            ? dirname(dirname($manifestPath))
            : dirname($manifestPath);
    }

    private function pathIsWithin(string $path, string $root): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return $path === $root || str_starts_with($path, $root.'/');
    }

    /**
     * Process a single manifest. Returns null when the manifest could not
     * be processed at all (the error has already been reported).
     *
     * @return array{kept: int, stale: int, deleted: int, package: string|null}|null
     */
    private function processManifest(string $manifestPath, string $vendorRoot, bool $force): ?array
    {
        $outputRoot = $this->outputRootFor($manifestPath);

        $keepSet = $this->parseManifest($manifestPath);

        if ($keepSet === null) {
            $this->failed = true;
            $this->components->error("Malformed or unsafe manifest, skipped: {$manifestPath}");

            return null;
        }

        $candidates = $this->collectCandidates($outputRoot);
        $keepSet = $this->withReferencedAssets($outputRoot, $keepSet, $candidates);
        $families = $this->groupIntoFamilies($candidates, $keepSet);

        $affected = array_filter($families, fn (array $family) => $family['stale'] !== []);

        $kept = 0;
        $stale = 0;
        $deleted = 0;

        foreach ($families as $family) {
            $kept += count($family['keep']);
            $stale += count($family['stale']);
        }

        if ($affected === []) {
            return ['kept' => $kept, 'stale' => 0, 'deleted' => 0, 'package' => null];
        }

        $label = $this->packageLabel($vendorRoot, $outputRoot);
        $context = 'vendor'.str_replace('\\', '/', substr($outputRoot, strlen($vendorRoot)));
        $manifestSuffix = $this->isViteManifest($manifestPath) ? '.vite/manifest.json' : 'manifest.json';

        $this->newLine();
        $this->line("<fg=cyan;options=bold>{$label}</> — {$context} [{$manifestSuffix}]");

        foreach ($affected as $trunkExt => $family) {
            $this->line("  <options=bold>{$trunkExt}</>");

            if ($family['keep'] === []) {
                $this->line('    KEEP — (not present in manifest)');
            } else {
                foreach ($family['keep'] as $relative) {
                    $this->line("    KEEP    {$relative}");
                }
            }

            foreach ($family['stale'] as $relative) {
                if (! $force) {
                    $this->line("    DELETE  {$relative}");

                    continue;
                }

                $absolute = $outputRoot.DIRECTORY_SEPARATOR.$relative;

                if (@unlink($absolute)) {
                    $this->line("    DELETED {$relative}");
                    $deleted++;
                } else {
                    $this->failed = true;
                    $this->components->error("    Failed to delete {$relative}");
                }
            }
        }

        $this->line("  <fg=gray>summary: {$kept} kept, {$stale} stale".($force ? ", {$deleted} deleted" : '').'</>');

        return ['kept' => $kept, 'stale' => $stale, 'deleted' => $deleted, 'package' => $label];
    }

    /**
     * Parse a manifest and return the set of normalized, output-root
     * relative paths it references, or null if the manifest is malformed
     * or contains an unsafe path.
     *
     * @return array<string, true>|null
     */
    private function parseManifest(string $manifestPath): ?array
    {
        $contents = @file_get_contents($manifestPath);

        if ($contents === false) {
            return null;
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (! is_array($data) || $data === []) {
            return null;
        }

        $keep = [];

        foreach ($data as $entry) {
            if (! is_array($entry) || ! array_key_exists('file', $entry)) {
                return null;
            }

            $paths = [$entry['file']];

            foreach (['css', 'assets'] as $key) {
                if (! isset($entry[$key])) {
                    continue;
                }

                if (! is_array($entry[$key])) {
                    return null;
                }

                foreach ($entry[$key] as $path) {
                    $paths[] = $path;
                }
            }

            foreach ($paths as $path) {
                if (! is_string($path) || $path === '') {
                    return null;
                }

                $normalized = $this->normalizeManifestPath($path);

                if ($normalized === null) {
                    return null;
                }

                $keep[$normalized] = true;
            }
        }

        return $keep;
    }

    /**
     * Extend the keep set with hashed assets that kept JS or CSS files refer to,
     * recursively. Some chunks aren't in the manifest, e.g. a worker loaded via
     * `new URL('pdf.worker-[hash].js', import.meta.url)` from a stub that is.
     * Vite writes hashed file names into its output as literal strings, so a
     * reference is found by searching for the file's name.
     *
     * @param  array<string, true>  $keepSet
     * @param  list<array{relative: string, directory: string, trunk: string, ext: string, isMap: bool}>  $candidates
     * @return array<string, true>
     */
    private function withReferencedAssets(string $outputRoot, array $keepSet, array $candidates): array
    {
        // Index by `[hash].ext` to find references without searching for every name
        $bySuffix = [];

        foreach ($candidates as $candidate) {
            if ($candidate['isMap'] || isset($keepSet[$candidate['relative']])) {
                continue;
            }

            $basename = basename($candidate['relative']);
            $suffix = substr($basename, strlen($candidate['trunk']) + 1);
            $bySuffix[$suffix][$candidate['relative']] = $basename;
        }

        $queue = array_keys($keepSet);

        while ($bySuffix !== [] && ($relative = array_pop($queue)) !== null) {
            if (! preg_match('/\.(?:m?js|css)$/i', $relative)) {
                continue;
            }

            $contents = @file_get_contents($outputRoot.DIRECTORY_SEPARATOR.$relative);

            // Lookahead, so overlapping hash-like sequences are all considered
            if ($contents === false || ! preg_match_all('/(?=-([A-Za-z0-9_-]{8}\.[A-Za-z0-9]+))/', $contents, $matches)) {
                continue;
            }

            foreach (array_unique($matches[1]) as $suffix) {
                foreach ($bySuffix[$suffix] ?? [] as $referenced => $basename) {
                    if (! str_contains($contents, $basename)) {
                        continue;
                    }

                    $keepSet[$referenced] = true;
                    $queue[] = $referenced;
                    unset($bySuffix[$suffix][$referenced]);
                }

                if (($bySuffix[$suffix] ?? null) === []) {
                    unset($bySuffix[$suffix]);
                }
            }
        }

        return $keepSet;
    }

    private function normalizeManifestPath(string $path): ?string
    {
        if (str_contains($path, "\0")) {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);

        if (
            str_starts_with($normalized, '/')
            || preg_match('/^[A-Za-z]:\//', $normalized)
            || preg_match('/^[A-Za-z][A-Za-z0-9+.-]*:/', $normalized)
        ) {
            return null;
        }

        $segments = [];

        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '..') {
                return null;
            }

            if ($segment !== '' && $segment !== '.') {
                $segments[] = $segment;
            }
        }

        return $segments === [] ? null : implode('/', $segments);
    }

    /**
     * @return list<array{relative: string, directory: string, trunk: string, ext: string, isMap: bool}>
     */
    private function collectCandidates(string $outputRoot): array
    {
        if (! is_dir($outputRoot)) {
            return [];
        }

        $finder = Finder::create()
            ->files()
            ->in($outputRoot)
            ->ignoreDotFiles(false)
            ->notName(self::PRESERVED_FILENAMES)
            ->sortByName();

        $candidates = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $parsed = $this->parseHashedAssetName(basename($relative));

            if ($parsed === null) {
                continue;
            }

            $candidates[] = [
                'relative' => $relative,
                'directory' => dirname($relative),
                'trunk' => $parsed['trunk'],
                'ext' => $parsed['ext'],
                'isMap' => $parsed['isMap'],
            ];
        }

        return $candidates;
    }

    /**
     * Recognize the conservative Vite/Rollup `[name]-[hash].ext` (or
     * `[name]-[hash].ext.map`) naming convention. The hash is exactly 8
     * URL-safe base64 characters (A-Z, a-z, 0-9, `_`, `-`).
     *
     * @return array{trunk: string, ext: string, isMap: bool}|null
     */
    private function parseHashedAssetName(string $basename): ?array
    {
        if (! preg_match('/^(?<trunk>.+)-(?<hash>[A-Za-z0-9_-]{8})\.(?<ext>[A-Za-z0-9]+)(?<map>\.map)?$/', $basename, $m)) {
            return null;
        }

        return [
            'trunk' => $m['trunk'],
            'ext' => $m['ext'],
            'isMap' => ($m['map'] ?? '') !== '',
        ];
    }

    /**
     * Group candidate files into logical families (trunk + base extension,
     * ignoring `.map`), classifying each candidate as kept or stale
     * relative to the manifest's keep set.
     *
     * @param  list<array{relative: string, directory: string, trunk: string, ext: string, isMap: bool}>  $candidates
     * @param  array<string, true>  $keepSet
     * @return array<string, array{keep: list<string>, stale: list<string>}>
     */
    private function groupIntoFamilies(array $candidates, array $keepSet): array
    {
        $families = [];

        foreach ($candidates as $candidate) {
            $directory = $candidate['directory'] === '.' ? '' : $candidate['directory'].'/';
            $familyKey = "{$directory}{$candidate['trunk']}.{$candidate['ext']}";
            $families[$familyKey] ??= ['keep' => [], 'stale' => []];

            $relative = $candidate['relative'];
            $isKept = isset($keepSet[$relative]);

            if (! $isKept && $candidate['isMap']) {
                $sibling = substr($relative, 0, -4); // strip trailing ".map"
                $isKept = isset($keepSet[$sibling]);
            }

            if ($isKept) {
                $families[$familyKey]['keep'][] = $relative;
            } else {
                $families[$familyKey]['stale'][] = $relative;
            }
        }

        ksort($families);

        return $families;
    }

    /**
     * Resolve a human-readable package label for the given output root,
     * using Composer's installed package metadata where possible. Falls
     * back to a deterministic path-derived label when no mapping can be
     * made — mapping failures never block cleanup.
     */
    private function packageLabel(string $vendorRoot, string $outputRoot): string
    {
        $relative = trim(str_replace('\\', '/', substr($outputRoot, strlen($vendorRoot))), '/');
        $segments = explode('/', $relative);

        // Drop a trailing Vite build directory segment (e.g. "build").
        if (count($segments) > 1 && end($segments) === 'build') {
            array_pop($segments);
        }

        $addonPath = implode('/', $segments);

        if ($addonPath === 'statamic/cp' || $addonPath === 'statamic/cp-dev') {
            return 'statamic/cms';
        }

        if ($addonPath === 'app/cp') {
            return 'app/cp (local)';
        }

        if (count($segments) >= 2) {
            $candidate = $segments[0].'/'.$segments[1];

            if ($this->isInstalledPackage($candidate)) {
                return $candidate;
            }
        }

        $basename = $segments[0];
        $matches = $this->installedPackagesWithBasename($basename);

        if (count($matches) === 1) {
            return $matches[0];
        }

        return $addonPath !== '' ? $addonPath : $relative;
    }

    private function isInstalledPackage(string $name): bool
    {
        return in_array($name, $this->installedPackages(), true);
    }

    /**
     * @return list<string>
     */
    private function installedPackagesWithBasename(string $basename): array
    {
        return array_values(array_filter(
            $this->installedPackages(),
            fn (string $package) => str_contains($package, '/') && substr($package, strrpos($package, '/') + 1) === $basename
        ));
    }

    /**
     * @return list<string>
     */
    private function installedPackages(): array
    {
        return $this->installedPackages ??= class_exists(InstalledVersions::class)
            ? InstalledVersions::getInstalledPackages()
            : [];
    }
}
