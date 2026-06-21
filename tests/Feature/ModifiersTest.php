<?php

use Daun\StatamicUtils\Modifiers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Query\Builder;

/*
|--------------------------------------------------------------------------
| Asset
|--------------------------------------------------------------------------
*/

test('asset modifier returns null for empty values', function () {
    $m = new Modifiers\Asset;

    expect($m->index(null))->toBeNull();
    expect($m->index(''))->toBeNull();
    expect($m->index(0))->toBeNull();
});

test('asset modifier returns null for non-asset objects', function () {
    $m = new Modifiers\Asset;

    expect($m->index(new stdClass))->toBeNull();
});

test('asset modifier returns the asset when an asset is passed', function () {
    $asset = Mockery::mock(AssetContract::class);
    $m = new Modifiers\Asset;

    expect($m->index($asset))->toBe($asset);
});

/*
|--------------------------------------------------------------------------
| Asset Meta
|--------------------------------------------------------------------------
*/

test('asset_meta returns null when no key is given', function () {
    $asset = Mockery::mock(Asset::class);

    expect((new Modifiers\AssetMeta)->index($asset, [], []))->toBeNull();
});

test('asset_meta returns null for non-asset values', function () {
    expect((new Modifiers\AssetMeta)->index('not an asset', ['caption'], []))->toBeNull();
    expect((new Modifiers\AssetMeta)->index(null, ['caption'], []))->toBeNull();
});

test('asset_meta reads a localized meta value', function () {
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('get')->andReturnUsing(
        fn ($key) => $key === 'caption_en' ? 'Hello' : null
    );

    expect((new Modifiers\AssetMeta)->index($asset, ['caption'], []))->toBe('Hello');
});

test('asset_meta falls back to the unsuffixed key', function () {
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('get')->andReturnUsing(
        fn ($key) => $key === 'caption' ? 'Plain' : null
    );

    expect((new Modifiers\AssetMeta)->index($asset, ['caption'], []))->toBe('Plain');
});

test('asset_meta prioritizes an explicit locale parameter', function () {
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('get')->andReturnUsing(
        fn ($key) => $key === 'caption_de' ? 'Hallo' : null
    );

    expect((new Modifiers\AssetMeta)->index($asset, ['caption', 'de'], []))->toBe('Hallo');
});

/*
|--------------------------------------------------------------------------
| Br2Nl
|--------------------------------------------------------------------------
*/

test('br2nl converts <br> tags to newlines', function () {
    $m = new Modifiers\Br2Nl;

    expect($m->index('<p>Hello<br>World</p>', []))->toBe("Hello\nWorld");
});

test('br2nl converts paragraphs to double newlines', function () {
    $m = new Modifiers\Br2Nl;

    expect($m->index('<p>a</p><p>b</p>', []))->toBe("a\n\nb");
});

test('br2nl strips other tags', function () {
    $m = new Modifiers\Br2Nl;

    expect($m->index('<strong>bold</strong> text', []))->toBe('bold text');
});

/*
|--------------------------------------------------------------------------
| Count Safe
|--------------------------------------------------------------------------
*/

test('count_safe returns 0 for null', function () {
    expect((new Modifiers\CountSafe)->index(null))->toBe(0);
});

test('count_safe returns 1 for non-iterable values', function () {
    expect((new Modifiers\CountSafe)->index('hello'))->toBe(1);
    expect((new Modifiers\CountSafe)->index(42))->toBe(1);
});

test('count_safe counts arrays and collections', function () {
    expect((new Modifiers\CountSafe)->index([1, 2, 3]))->toBe(3);
    expect((new Modifiers\CountSafe)->index(collect([1, 2])))->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Except
|--------------------------------------------------------------------------
*/

test('except removes keys from an array and returns an array', function () {
    $m = new Modifiers\Except;
    $result = $m->index(['a' => 1, 'b' => 2, 'page' => 3], ['page'], []);

    expect($result)->toBe(['a' => 1, 'b' => 2]);
});

test('except removes multiple keys', function () {
    $m = new Modifiers\Except;
    $result = $m->index(['a' => 1, 'page' => 2, 'q' => 3], ['page', 'q'], []);

    expect($result)->toBe(['a' => 1]);
});

test('except keeps collections as collections', function () {
    $m = new Modifiers\Except;
    $result = $m->index(collect(['a' => 1, 'page' => 2]), ['page'], []);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->all())->toBe(['a' => 1]);
});

/*
|--------------------------------------------------------------------------
| Hostname
|--------------------------------------------------------------------------
*/

test('hostname extracts the host from a url', function () {
    expect((new Modifiers\Hostname)->index('https://example.com/path', [], []))->toBe('example.com');
});

test('hostname strips a leading www.', function () {
    expect((new Modifiers\Hostname)->index('https://www.example.com', [], []))->toBe('example.com');
});

test('hostname returns non-url values as null', function () {
    expect((new Modifiers\Hostname)->index('not a url', [], []))->toBe(null);
    expect((new Modifiers\Hostname)->index(42, [], []))->toBe(null);
});

/*
|--------------------------------------------------------------------------
| Is Current
|--------------------------------------------------------------------------
*/

function setCurrentPath(string $path): void
{
    app()->instance('request', Request::create($path));
}

test('is_current matches the current url', function () {
    setCurrentPath('/blog/post');
    $m = new Modifiers\IsCurrent;

    expect($m->index('/blog/post', [], []))->toBeTrue();
    expect($m->index('/blog/other', [], []))->toBeFalse();
});

test('is_current ignores trailing slashes', function () {
    setCurrentPath('/blog/post');
    $m = new Modifiers\IsCurrent;

    expect($m->index('/blog/post/', [], []))->toBeTrue();
});

test('is_current matches ancestors only when enabled', function () {
    setCurrentPath('/blog/post');
    $m = new Modifiers\IsCurrent;

    expect($m->index('/blog', [true], []))->toBeTrue();
    expect($m->index('/blog', [false], []))->toBeFalse();
    expect($m->index('/blog', [], []))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Max / Min
|--------------------------------------------------------------------------
*/

test('max returns the highest value', function () {
    expect((new Modifiers\Max)->index([3, 1, 2]))->toBe(3);
    expect((new Modifiers\Max)->index(collect([5, 9, 2])))->toBe(9);
});

test('min returns the lowest value', function () {
    expect((new Modifiers\Min)->index([3, 1, 2]))->toBe(1);
    expect((new Modifiers\Min)->index(collect([5, 9, 2])))->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Nl2Str
|--------------------------------------------------------------------------
*/

test('nl2str replaces newlines with a space by default', function () {
    expect((new Modifiers\Nl2Str)->index("a\nb\nc", []))->toBe('a b c');
});

test('nl2str replaces newlines with a custom string', function () {
    expect((new Modifiers\Nl2Str)->index("a\nb\nc", [', ']))->toBe('a, b, c');
});

/*
|--------------------------------------------------------------------------
| Orientation
|--------------------------------------------------------------------------
*/

test('orientation returns null for empty values', function () {
    expect((new Modifiers\Orientation)->index(null, []))->toBeNull();
    expect((new Modifiers\Orientation)->index(0, []))->toBeNull();
});

test('orientation classifies an aspect ratio', function () {
    $m = new Modifiers\Orientation;

    expect($m->index(2.0, []))->toBe('landscape');
    expect($m->index(0.5, []))->toBe('portrait');
    expect($m->index(1.0, []))->toBe('square');
});

test('orientation treats values within 5% of 1:1 as square by default', function () {
    $m = new Modifiers\Orientation;

    expect($m->index(1.03, []))->toBe('square');
    expect($m->index(0.97, []))->toBe('square');
});

test('orientation accepts a custom threshold', function () {
    $m = new Modifiers\Orientation;

    expect($m->index(1.2, [1.25]))->toBe('square');
    expect($m->index(1.3, [1.25]))->toBe('landscape');
});

/*
|--------------------------------------------------------------------------
| P2Br
|--------------------------------------------------------------------------
*/

test('p2br converts paragraphs to line breaks', function () {
    $m = new Modifiers\P2Br;

    expect($m->index('<p>a</p><p>b</p>', []))->toBe('a<br /><br />b');
});

test('p2br accepts a custom number of breaks', function () {
    $m = new Modifiers\P2Br;

    expect($m->index('<p>a</p><p>b</p>', [1]))->toBe('a<br />b');
});

test('p2br leaves content without paragraphs untouched', function () {
    $m = new Modifiers\P2Br;

    expect($m->index('plain text', []))->toBe('plain text');
});

/*
|--------------------------------------------------------------------------
| Push
|--------------------------------------------------------------------------
*/

test('push appends an item to an array', function () {
    $m = new Modifiers\Push;

    expect($m->index([1, 2], [3]))->toBe([1, 2, 3]);
});

test('push appends an item to a collection', function () {
    $m = new Modifiers\Push;
    $result = $m->index(collect([1, 2]), [3]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->all())->toBe([1, 2, 3]);
});

/*
|--------------------------------------------------------------------------
| Resolve
|--------------------------------------------------------------------------
*/

test('resolve passes through values that are not query builders', function () {
    $m = new Modifiers\Resolve;

    expect($m->index(['a', 'b'], [], []))->toBe(['a', 'b']);
    expect($m->index('plain', [], []))->toBe('plain');
});

test('resolve resolves a query builder to its results', function () {
    $results = collect(['one', 'two']);
    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('get')->once()->andReturn($results);

    expect((new Modifiers\Resolve)->index($builder, [], []))->toBe($results);
});

/*
|--------------------------------------------------------------------------
| Standard Ratio
|--------------------------------------------------------------------------
*/

test('standard_ratio returns an empty string for empty values', function () {
    expect((new Modifiers\StandardRatio)->index(null, []))->toBe('');
});

test('standard_ratio maps to the closest standard ratio', function () {
    $m = new Modifiers\StandardRatio;

    // 1.0 -> 1:1
    expect($m->index(1.0, []))->toBe(1.0);
    // 1.7 -> 16:9 (1.777)
    expect($m->index(1.7, []))->toBe(16 / 9);
    // 1.4 -> 4:3 (1.333) is closer than 3:2 (1.5)
    expect($m->index(1.4, []))->toBe(4 / 3);
});

test('standard_ratio can format the result with two decimals', function () {
    $m = new Modifiers\StandardRatio;

    expect($m->index(1.0, [true]))->toBe('1.00');
});

test('standard_ratio honours globally defined custom ratios', function () {
    $default = ['1/1', '4/3', '3/4', '3/2', '2/3', '16/9', '9/16'];

    try {
        Modifiers\StandardRatio::define(['5/4', '4/5']);
        $m = new Modifiers\StandardRatio;

        expect($m->index(1.2, []))->toBe(5 / 4);
        expect($m->index(0.8, []))->toBe(4 / 5);
    } finally {
        Modifiers\StandardRatio::define($default);
    }
});

/*
|--------------------------------------------------------------------------
| To Int / To Float
|--------------------------------------------------------------------------
*/

test('to_int converts a value to an integer', function () {
    expect((new Modifiers\ToInt)->index('42'))->toBe(42);
    expect((new Modifiers\ToInt)->index(3.9))->toBe(3);
});

test('to_int converts a mixed array to an array of integers', function () {
    expect((new Modifiers\ToInt)->index(['1', '2', '3']))->toBe([1, 2, 3]);
});

test('to_float converts a value to a float', function () {
    expect((new Modifiers\ToFloat)->index('4.5'))->toBe(4.5);
    expect((new Modifiers\ToFloat)->index('3'))->toBe(3.0);
});

test('to_float converts a mixed array to an array of floats', function () {
    expect((new Modifiers\ToFloat)->index(['1.5', '2', '3.25']))->toBe([1.5, 2.0, 3.25]);
});

/*
|--------------------------------------------------------------------------
| To Iterable
|--------------------------------------------------------------------------
*/

test('to_iterable wraps a non-iterable value in an array', function () {
    expect((new Modifiers\ToIterable)->index('a', [], []))->toBe(['a']);
});

test('to_iterable leaves arrays untouched', function () {
    expect((new Modifiers\ToIterable)->index(['a', 'b'], [], []))->toBe(['a', 'b']);
});

test('to_iterable leaves collections untouched', function () {
    $collection = collect(['a']);

    expect((new Modifiers\ToIterable)->index($collection, [], []))->toBe($collection);
});

/*
|--------------------------------------------------------------------------
| Wrap Words
|--------------------------------------------------------------------------
*/

test('wrap_words wraps each word in a span by default', function () {
    $m = new Modifiers\WrapWords;

    expect($m->index('hello world', [], []))->toBe('<span>hello</span> <span>world</span>');
});

test('wrap_words accepts a custom wrapper tag', function () {
    $m = new Modifiers\WrapWords;

    expect($m->index('hello world', ['li'], []))->toBe('<li>hello</li> <li>world</li>');
});
