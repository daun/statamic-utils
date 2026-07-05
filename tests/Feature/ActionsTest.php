<?php

use Daun\StatamicUtils\Actions\ConnectToOrigin;
use Daun\StatamicUtils\Actions\EditCollectionMount;
use Daun\StatamicUtils\Actions\SetAssetAttribution;
use Daun\StatamicUtils\Actions\ShowMountEntries;
use Statamic\Contracts\Assets\Asset;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection as Collections;
use Statamic\Facades\Entry as Entries;

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

/*
|--------------------------------------------------------------------------
| Connect to Origin
|--------------------------------------------------------------------------
*/

test('connect to origin has a title and is never available in bulk', function () {
    expect(ConnectToOrigin::title())->toBe('Connect to Origin');
    expect((new ConnectToOrigin)->visibleToBulk([]))->toBeFalse();
});

test('connect to origin exposes button and confirmation text', function () {
    $action = new ConnectToOrigin;

    expect($action->buttonText())->toContain('Connect to Origin');
    expect($action->confirmationText())->toContain('chosen origin');
});

test('connect to origin authorizes users who can edit the item', function () {
    $item = Mockery::mock(Entry::class);

    $user = Mockery::mock();
    $user->shouldReceive('can')->with('edit', $item)->andReturn(true);

    expect((new ConnectToOrigin)->authorize($user, $item))->toBeTrue();
});

test('connect to origin is only visible in the edit form for multi-site entries without an origin', function () {
    $sites = Mockery::mock();
    $sites->shouldReceive('count')->andReturn(2);

    $collection = Mockery::mock(Collection::class);
    $collection->shouldReceive('sites')->andReturn($sites);

    $entry = Mockery::mock(Entry::class);
    $entry->shouldReceive('collection')->andReturn($collection);
    $entry->shouldReceive('hasOrigin')->andReturn(false);
    $entry->shouldReceive('locale')->andReturn('en');

    $action = (new ConnectToOrigin)->context(['view' => 'form', 'collection' => 'pages']);

    expect($action->visibleTo($entry))->toBeTrue();
});

test('connect to origin is hidden outside the edit form', function () {
    $entry = Mockery::mock(Entry::class);

    $action = (new ConnectToOrigin)->context(['view' => 'list', 'collection' => 'pages']);

    expect($action->visibleTo($entry))->toBeFalse();
});

test('connect to origin throws when the chosen origin cannot be found', function () {
    $query = Mockery::mock();
    $query->shouldReceive('where')->andReturnSelf();
    $query->shouldReceive('first')->andReturn(null);

    Entries::shouldReceive('query')->andReturn($query);

    expect(fn () => (new ConnectToOrigin)->run(collect(), ['origin' => null]))
        ->toThrow(Exception::class, 'Origin not found.');
});

/*
|--------------------------------------------------------------------------
| Set Asset Attribution
|--------------------------------------------------------------------------
*/

test('set asset attribution has a title', function () {
    expect(SetAssetAttribution::title())->toBe('Set Attribution');
});

test('set asset attribution is only visible for media assets', function () {
    $action = new SetAssetAttribution;

    $media = Mockery::mock(Asset::class);
    $media->shouldReceive('isMedia')->andReturn(true);

    $document = Mockery::mock(Asset::class);
    $document->shouldReceive('isMedia')->andReturn(false);

    expect($action->visibleTo($media))->toBeTrue();
    expect($action->visibleTo($document))->toBeFalse();
    expect($action->visibleTo(new stdClass))->toBeFalse();
});

test('set asset attribution writes the attribution to selected assets', function () {
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('get')->with('attribution')->andReturn(null);
    $asset->shouldReceive('set')->with('attribution', 'Jane Doe')->once();
    $asset->shouldReceive('saveQuietly')->once();

    $result = (new SetAssetAttribution)->run(collect([$asset]), [
        'attribution' => 'Jane Doe',
        'overwrite' => true,
    ]);

    expect($result)->toBe('Attribution updated.');
});

test('set asset attribution skips assets with existing data when overwrite is off', function () {
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('get')->with('attribution')->andReturn('Existing');
    $asset->shouldNotReceive('set');
    $asset->shouldNotReceive('saveQuietly');

    $result = (new SetAssetAttribution)->run(collect([$asset]), [
        'attribution' => 'Jane Doe',
        'overwrite' => false,
    ]);

    expect($result)->toBe('No assets were updated.');
});
