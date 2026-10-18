<?php

use Daun\StatamicUtils\Commands\PruneStaleVendorAssets;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command;

beforeEach(function () {
    $this->publicPath = sys_get_temp_dir().'/prune-assets-test-'.uniqid();

    File::ensureDirectoryExists($this->publicPath);

    $this->app->usePublicPath($this->publicPath);
    $this->app->make(Kernel::class)->registerCommand(new PruneStaleVendorAssets);
});

afterEach(function () {
    File::deleteDirectory($this->publicPath);
});

function vendorPath(string $path = ''): string
{
    return public_path('vendor'.($path !== '' ? '/'.$path : ''));
}

function writeAsset(string $relativePath, string $contents = ''): void
{
    $path = vendorPath($relativePath);
    File::ensureDirectoryExists(dirname($path));
    File::put($path, $contents);
}

function writeManifest(string $relativePath, array $data): void
{
    writeAsset($relativePath, json_encode($data, JSON_PRETTY_PRINT));
}

function assertKept(string $relativePath): void
{
    expect(vendorPath($relativePath))->toBeFile("Expected {$relativePath} to still exist.");
}

test('reports success when vendor root is missing', function () {
    $this->artisan('app:assets:prune')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('Nothing to prune');
});

test('reports success when no manifests exist', function () {
    File::ensureDirectoryExists(vendorPath());

    $this->artisan('app:assets:prune')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('No Vite manifests found');
});

test('dry run leaves all files and reports keep and delete', function () {
    writeManifest('demo/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('demo/build/assets/app-11112222.js', 'stale');

    $this->artisan('app:assets:prune')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('KEEP    assets/app-AbCdEfGh.js')
        ->expectsOutputToContain('DELETE  assets/app-11112222.js')
        ->expectsOutputToContain('php artisan app:assets:prune --force');

    // Dry run: nothing was actually deleted.
    assertKept('demo/build/assets/app-AbCdEfGh.js');
    assertKept('demo/build/assets/app-11112222.js');
});

test('force deletes stale hashed asset but preserves current and manual files', function () {
    writeManifest('demo/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('demo/build/assets/app-11112222.js', 'stale');
    writeAsset('demo/build/assets/logo.png', 'manual asset without a hash');
    writeAsset('demo/build/assets/.DS_Store', 'finder junk');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('DELETED assets/app-11112222.js');

    assertKept('demo/build/assets/app-AbCdEfGh.js');
    assertKept('demo/build/manifest.json');
    assertKept('demo/build/assets/logo.png');
    assertKept('demo/build/assets/.DS_Store');
    expect(vendorPath('demo/build/assets/app-11112222.js'))->not->toBeFile();
});

test('fully orphaned hashed trunk is reported and deleted', function () {
    writeManifest('demo/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('demo/build/assets/orphan-99998888.js', 'orphaned, trunk never referenced');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('KEEP — (not present in manifest)')
        ->expectsOutputToContain('DELETED assets/orphan-99998888.js');

    expect(vendorPath('demo/build/assets/orphan-99998888.js'))->not->toBeFile();
    assertKept('demo/build/assets/app-AbCdEfGh.js');
});

test('css and assets manifest arrays are kept', function () {
    writeManifest('demo/build/manifest.json', [
        'resources/js/app.js' => [
            'file' => 'assets/app-AbCdEfGh.js',
            'isEntry' => true,
            'css' => ['assets/app-CcCcCcCc.css'],
            'assets' => ['assets/logo-DdDdDdDd.png'],
        ],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current js');
    writeAsset('demo/build/assets/app-CcCcCcCc.css', 'current css');
    writeAsset('demo/build/assets/logo-DdDdDdDd.png', 'current image');
    writeAsset('demo/build/assets/app-99990000.css', 'stale css');

    $this->artisan('app:assets:prune', ['--force' => true])->assertExitCode(Command::SUCCESS);

    assertKept('demo/build/assets/app-AbCdEfGh.js');
    assertKept('demo/build/assets/app-CcCcCcCc.css');
    assertKept('demo/build/assets/logo-DdDdDdDd.png');
    expect(vendorPath('demo/build/assets/app-99990000.css'))->not->toBeFile();
});

test('current sourcemap is kept and stale sourcemap is deleted', function () {
    writeManifest('demo/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current js');
    writeAsset('demo/build/assets/app-AbCdEfGh.js.map', 'current sourcemap');
    writeAsset('demo/build/assets/app-11112222.js', 'stale js');
    writeAsset('demo/build/assets/app-11112222.js.map', 'stale sourcemap');

    $this->artisan('app:assets:prune', ['--force' => true])->assertExitCode(Command::SUCCESS);

    assertKept('demo/build/assets/app-AbCdEfGh.js');
    assertKept('demo/build/assets/app-AbCdEfGh.js.map');
    expect(vendorPath('demo/build/assets/app-11112222.js'))->not->toBeFile();
    expect(vendorPath('demo/build/assets/app-11112222.js.map'))->not->toBeFile();
});

test('modern dot vite manifest layout resolves output root correctly', function () {
    writeManifest('demo/build/.vite/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('demo/build/assets/app-11112222.js', 'stale');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutputToContain('[.vite/manifest.json]');

    assertKept('demo/build/assets/app-AbCdEfGh.js');
    expect(vendorPath('demo/build/assets/app-11112222.js'))->not->toBeFile();
    assertKept('demo/build/.vite/manifest.json');
});

test('multiple manifests for the same output root are skipped', function () {
    $manifest = [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ];

    writeManifest('demo/build/manifest.json', $manifest);
    writeManifest('demo/build/.vite/manifest.json', $manifest);
    writeAsset('demo/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('demo/build/assets/app-11112222.js', 'stale');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutputToContain('Multiple Vite manifests share output root');

    assertKept('demo/build/assets/app-AbCdEfGh.js');
    assertKept('demo/build/assets/app-11112222.js');
});

test('malformed or empty manifest returns failure and deletes nothing', function () {
    writeAsset('broken/build/manifest.json', '{not valid json');
    writeManifest('empty/build/manifest.json', []);
    writeAsset('broken/build/assets/app-11112222.js', 'untouched');
    writeAsset('empty/build/assets/app-11112222.js', 'untouched');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutputToContain('Malformed or unsafe manifest');

    assertKept('broken/build/assets/app-11112222.js');
    assertKept('empty/build/assets/app-11112222.js');
});

test('manifest with unsafe absolute path is rejected and valid manifests still run', function () {
    writeManifest('unsafe/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'C:\\outside\\app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('unsafe/build/assets/app-11112222.js', 'untouched');

    writeManifest('safe/build/manifest.json', [
        'resources/js/app.js' => ['file' => 'assets/app-AbCdEfGh.js', 'isEntry' => true],
    ]);
    writeAsset('safe/build/assets/app-AbCdEfGh.js', 'current');
    writeAsset('safe/build/assets/app-11112222.js', 'stale');

    $this->artisan('app:assets:prune', ['--force' => true])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutputToContain('Malformed or unsafe manifest');

    assertKept('unsafe/build/assets/app-11112222.js');
    expect(vendorPath('safe/build/assets/app-11112222.js'))->not->toBeFile();
});
