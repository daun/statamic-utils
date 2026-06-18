<?php

use Daun\StatamicUtils\Search\Filters;
use Daun\StatamicUtils\Search\Transformers;
use Statamic\Entries\Entry;
use Statamic\Facades\Site as Sites;

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

test('the All filter includes every item', function () {
    $filter = new Filters\All;

    expect($filter->handle(Mockery::mock()))->toBeTrue();
    expect($filter->handle(null))->toBeTrue();
});

test('the Published filter only includes published items', function () {
    $filter = new Filters\Published;

    $published = Mockery::mock();
    $published->shouldReceive('status')->andReturn('published');

    $draft = Mockery::mock();
    $draft->shouldReceive('status')->andReturn('draft');

    expect($filter->handle($published))->toBeTrue();
    expect($filter->handle($draft))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| BardText transformer
|--------------------------------------------------------------------------
*/

test('the BardText transformer returns null for empty values', function () {
    expect((new Transformers\BardText)->handle(null))->toBeNull();
    expect((new Transformers\BardText)->handle(''))->toBeNull();
});

test('the BardText transformer returns the value when field or searchable is missing', function () {
    expect((new Transformers\BardText)->handle('some text'))->toBe('some text');
    expect((new Transformers\BardText)->handle('some text', 'content'))->toBe('some text');
});

/*
|--------------------------------------------------------------------------
| RelationshipTitle transformer
|--------------------------------------------------------------------------
*/

function fakeSearchable($returnValue): object
{
    $augmented = Mockery::mock();
    $augmented->shouldReceive('value')->andReturn($returnValue);

    $searchable = Mockery::mock();
    $searchable->shouldReceive('augmentedValue')->andReturn($augmented);

    return $searchable;
}

test('the RelationshipTitle transformer resolves the title of a single relationship', function () {
    $item = (object) ['title' => 'Hello World'];
    $searchable = fakeSearchable($item);

    $result = (new Transformers\RelationshipTitle)->handle(null, 'related', $searchable);

    expect($result)->toBe(['related' => ['Hello World']]);
});

test('the RelationshipTitle transformer resolves the titles of a collection of relationships', function () {
    $items = collect([
        (object) ['title' => 'One'],
        (object) ['title' => 'Two'],
    ]);
    $searchable = fakeSearchable($items);

    $result = (new Transformers\RelationshipTitle)->handle(null, 'related', $searchable);

    expect($result)->toBe(['related' => ['One', 'Two']]);
});

test('the RelationshipTitle transformer reads the title of an Entry', function () {
    $entry = Mockery::mock(Entry::class);
    $entry->shouldReceive('value')->with('title')->andReturn('Entry Title');

    expect((new Transformers\RelationshipTitle)->title($entry))->toBe('Entry Title');
});

/*
|--------------------------------------------------------------------------
| RelationshipTitleLocalizations transformer
|--------------------------------------------------------------------------
*/

test('the localizations transformer collects relationship titles across sites', function () {
    $site = Mockery::mock();
    $site->shouldReceive('handle')->andReturn('en');
    Sites::shouldReceive('all')->andReturn(collect([$site]));

    $localized = Mockery::mock(Entry::class);
    $localized->shouldReceive('value')->with('title')->andReturn('Localized Title');

    $entry = Mockery::mock(Entry::class);
    $entry->shouldReceive('in')->with('en')->andReturn($localized);

    $searchable = fakeSearchable($entry);

    $result = (new Transformers\RelationshipTitleLocalizations)->handle(null, 'related', $searchable);

    expect($result)->toBe(['related' => ['Localized Title']]);
});

test('the localizations transformer extends the relationship title transformer', function () {
    expect(new Transformers\RelationshipTitleLocalizations)
        ->toBeInstanceOf(Transformers\RelationshipTitle::class);
});
