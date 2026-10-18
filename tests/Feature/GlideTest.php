<?php

use Daun\StatamicUtils\Glide\GlideHash;
use Statamic\Facades\Glide;

test('glide hash spells out simple params', function () {
    expect(GlideHash::make('a.jpg', ['fit' => 'contain', 'fm' => 'avif', 'q' => 75, 'w' => 400]))
        ->toBe('fit-contain_fm-avif_q-75_w-400');
});

test('glide hash keeps dashes, dots and negative numbers in values', function () {
    expect(GlideHash::make('a.jpg', ['bri' => -10, 'fit' => 'crop-48-63-1.5']))
        ->toBe('bri--10_fit-crop-48-63-1.5');
});

test('glide hash keeps underscores in values', function () {
    expect(GlideHash::make('a.jpg', ['fit' => 'crop_focal', 'h' => 630, 'q' => 80, 'w' => 1200]))
        ->toBe('fit-crop_focal_h-630_q-80_w-1200');
});

test('glide hash skips nulls and casts booleans like http_build_query', function () {
    expect(GlideHash::make('a.jpg', ['flip' => null, 'gam' => true, 'w' => 400]))
        ->toBe('gam-1_w-400');
});

test('glide hash escapes unsafe characters in values', function (string $value, string $expected) {
    expect(GlideHash::make('a.jpg', ['mark' => $value]))->toBe($expected);
})->with([
    'slash' => ['watermarks/logo.png', 'mark-watermarks~2flogo.png'],
    'uppercase on case-insensitive disks' => ['Logo.png', 'mark-~4cogo.png'],
    'hash sign' => ['#fff', 'mark-~23fff'],
    'space' => ['my logo.png', 'mark-my~20logo.png'],
    'tilde itself' => ['a~2f', 'mark-a~7e2f'],
    'multibyte' => ['ü', 'mark-~c3~bc'],
]);

test('glide hash escaping keeps distinct values distinct', function () {
    $names = array_map(
        fn (string $value) => GlideHash::make('a.jpg', ['mark' => $value]),
        ['a/b', 'a~2fb', 'A', 'a', '~41', 'a b', 'a+b'],
    );

    expect(array_unique($names))->toHaveCount(count($names));
});

test('glide hash falls back to a short hash', function (array $params) {
    expect(GlideHash::make('a.jpg', $params))
        ->toBe(hash('xxh3', 'a.jpg?'.http_build_query($params)))
        ->toMatch('/^[0-9a-f]{16}$/');
})->with([
    'array value' => [['filt' => ['a', 'b']]],
    'non-letter key' => [['w2' => 400]],
    'no params' => [[]],
    'too long' => [['fit' => str_repeat('a', 120)]],
]);

test('glide hash readable names never look like a fallback hash', function () {
    // Every pair contains a dash, which a hex hash never does
    expect(GlideHash::make('a.jpg', ['w' => 400]))->not->toMatch('/^[0-9a-f]{16}$/');
});

test('glide hash gives different params different names', function () {
    $names = array_map(fn (array $params) => GlideHash::make('a.jpg', $params), [
        ['w' => 400], ['w' => 4000], ['w' => 400, 'q' => 75], ['w' => 400, 'fm' => 'avif'],
        ['w' => 400, 'fm' => 'webp'], ['fit' => 'crop-50-50', 'w' => 400], ['fit' => 'crop-50', 'w' => 50],
        ['fit' => 'crop_focal'], ['fit' => 'crop', 'focal' => 1],
    ]);

    expect(array_unique($names))->toHaveCount(count($names));
});

test('glide hash can be registered for all renders', function () {
    GlideHash::register();

    expect(Glide::server()->getCachePath('photos/a.jpg', ['w' => 400, 'fm' => 'webp']))
        ->toContain('/fm-webp_w-400/a.webp');
});
