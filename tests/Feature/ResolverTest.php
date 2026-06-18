<?php

use Daun\StatamicUtils\Data\Resolver;
use Statamic\Contracts\Query\Builder;
use Statamic\Fields\Value;

test('resolver returns plain values unchanged', function () {
    expect(Resolver::actual('plain'))->toBe('plain');
    expect(Resolver::actual(42))->toBe(42);
    expect(Resolver::actual(['a', 'b']))->toBe(['a', 'b']);
});

test('resolver returns null when no value is given', function () {
    expect(Resolver::actual())->toBeNull();
    expect(Resolver::actual(null))->toBeNull();
});

test('resolver falls back to the next argument when earlier ones are null', function () {
    expect(Resolver::actual(null, 'fallback'))->toBe('fallback');
    expect(Resolver::actual(null, null, 'third'))->toBe('third');
});

test('resolver unwraps a Value object', function () {
    expect(Resolver::actual(new Value('hello')))->toBe('hello');
});

test('resolver resolves a query builder to its results', function () {
    $results = collect(['one', 'two']);
    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('get')->once()->andReturn($results);

    expect(Resolver::actual($builder))->toBe($results);
});
