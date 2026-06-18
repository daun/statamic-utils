<?php

/*
|--------------------------------------------------------------------------
| PHP version compatibility
|--------------------------------------------------------------------------
|
| composer.json declares a minimum PHP version of ^8.3, but some source
| files use the "new without parentheses" member-access syntax
| (`new Foo('x')->bar()`) that was only introduced in PHP 8.4. On PHP 8.3
| those files are a fatal parse error, so the whole package fails to load.
|
| The fix raised the minimum PHP requirement to ^8.4, so the syntax is now
| allowed. This test keeps guarding the invariant: if the package ever drops
| back to advertising 8.3 support, the 8.4-only syntax must not be present.
|
*/

test('source code does not use PHP 8.4-only syntax while requiring PHP 8.3', function () {
    $composer = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
    $phpConstraint = $composer['require']['php'] ?? '';

    // The `new ...()->` syntax is only valid on PHP 8.4+. If the package no
    // longer advertises support for 8.3, there is nothing to enforce.
    if (! str_contains($phpConstraint, '8.3')) {
        expect(true)->toBeTrue();

        return;
    }

    $offenders = [];
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../../src', FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        foreach (file($file->getPathname()) as $number => $line) {
            // Matches `new ClassName(...)->` which only parses on PHP 8.4+.
            if (preg_match('/new\s+[A-Za-z_\\\\][A-Za-z0-9_\\\\]*\s*\([^;]*\)\s*->/', $line)) {
                $offenders[] = sprintf('%s:%d %s', $file->getFilename(), $number + 1, trim($line));
            }
        }
    }

    expect($offenders)->toBe([], 'PHP 8.4-only `new ...()->` syntax found:'.PHP_EOL.implode(PHP_EOL, $offenders));
});
