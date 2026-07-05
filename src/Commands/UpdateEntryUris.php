<?php

namespace Daun\StatamicUtils\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Statamic\Facades\Collection as Collections;
use Statamic\Facades\Entry as Entries;

#[Signature('app:update-entry-uris')]
#[Description('Update URIs for all entries in all collections')]
class UpdateEntryUris extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Collections::all()
            ->each(function ($collection) {
                Entries::updateUris($collection);
                gc_collect_cycles();
                $this->info("Updated URIs for collection: {$collection->handle()}");
            });
    }
}
