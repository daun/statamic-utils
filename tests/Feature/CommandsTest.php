<?php

use Daun\StatamicUtils\Commands\UpdateEntryUris;
use Statamic\Facades\Collection;

beforeEach(function () {
    UpdateEntryUris::register();
});

/*
|--------------------------------------------------------------------------
| Update Entry URIs
|--------------------------------------------------------------------------
*/

test('update entry uris runs successfully with no collections', function () {
    $this->artisan('utils:update-entry-uris')->assertSuccessful();
});

test('update entry uris reports each processed collection', function () {
    Collection::make('blog')->title('Blog')->save();

    $this->artisan('utils:update-entry-uris')
        ->expectsOutputToContain('Updated URIs for collection: blog')
        ->assertSuccessful();
});
