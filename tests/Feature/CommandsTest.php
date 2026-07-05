<?php

use Daun\StatamicUtils\Commands\UpdateEntryUris;
use Illuminate\Contracts\Console\Kernel;
use Statamic\Facades\Collection;

beforeEach(function () {
    $this->app->make(Kernel::class)->registerCommand(new UpdateEntryUris);
});

/*
|--------------------------------------------------------------------------
| Update Entry URIs
|--------------------------------------------------------------------------
*/

test('update entry uris runs successfully with no collections', function () {
    $this->artisan('app:update-entry-uris')->assertSuccessful();
});

test('update entry uris reports each processed collection', function () {
    Collection::make('blog')->title('Blog')->save();

    $this->artisan('app:update-entry-uris')
        ->expectsOutputToContain('Updated URIs for collection: blog')
        ->assertSuccessful();
});
