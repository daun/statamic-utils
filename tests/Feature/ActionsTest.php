<?php

use Daun\StatamicUtils\Actions\EditCollectionMount;
use Daun\StatamicUtils\Actions\ShowMountEntries;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection as Collections;

/*
|--------------------------------------------------------------------------
| Edit Collection Mount
|--------------------------------------------------------------------------
*/

test('edit collection mount has a title and is never available in bulk', function () {
    expect(EditCollectionMount::title())->toBe('Edit Mount Page');
    expect((new EditCollectionMount)->visibleToBulk([]))->toBeFalse();
});

test('edit collection mount is only visible for collections with a mount', function () {
    $action = new EditCollectionMount;

    $mounted = Mockery::mock(Collection::class);
    $mounted->shouldReceive('mount')->andReturn(Mockery::mock(Entry::class));

    expect($action->visibleTo($mounted))->toBeTrue();
    expect($action->visibleTo(new stdClass))->toBeFalse();
});

test('edit collection mount redirects to the mount edit url', function () {
    $action = new EditCollectionMount;

    $mount = Mockery::mock(Entry::class);
    $mount->shouldReceive('editUrl')->andReturn('/cp/collections/pages/entries/home');

    $collection = Mockery::mock(Collection::class);
    $collection->shouldReceive('mount')->andReturn($mount);

    expect($action->redirect(collect([$collection]), []))->toBe('/cp/collections/pages/entries/home');
});

test('edit collection mount redirect returns false when there is no mount', function () {
    $action = new EditCollectionMount;

    $collection = Mockery::mock(Collection::class);
    $collection->shouldReceive('mount')->andReturn(null);

    expect($action->redirect(collect([$collection]), []))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Show Mount Entries
|--------------------------------------------------------------------------
*/

test('show mount entries has a title and is never available in bulk', function () {
    expect(ShowMountEntries::title())->toBe('Show Entries');
    expect((new ShowMountEntries)->visibleToBulk([]))->toBeFalse();
});

test('show mount entries is not visible for non-entries', function () {
    expect((new ShowMountEntries)->visibleTo(new stdClass))->toBeFalse();
});

test('show mount entries redirects to the mounting collection show url', function () {
    $collection = Mockery::mock(Collection::class);
    $collection->shouldReceive('showUrl')->andReturn('/cp/collections/pages/entries');

    $entry = Mockery::mock(Entry::class);
    Collections::shouldReceive('findByMount')->with($entry)->andReturn($collection);

    expect((new ShowMountEntries)->redirect(collect([$entry]), []))
        ->toBe('/cp/collections/pages/entries');
});

test('show mount entries redirect returns false when the entry is not a mount', function () {
    $entry = Mockery::mock(Entry::class);
    Collections::shouldReceive('findByMount')->with($entry)->andReturn(null);

    expect((new ShowMountEntries)->redirect(collect([$entry]), []))->toBeFalse();
});
