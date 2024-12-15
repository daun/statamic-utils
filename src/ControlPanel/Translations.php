<?php

namespace Daun\StatamicUtils\ControlPanel;

use Statamic\Facades\Collection as Collections;
use Statamic\Facades\Taxonomy as Taxonomies;

class Translations
{
    /**
     * Ensure that for each collection and taxonomy there is an existing translation key
     * for the "Create Entry" button in the control panel.
     */
    public static function ensureCreateButtonLabels(): void
    {
        $collections = Collections::all()
            ->map(fn ($collection) => "messages.{$collection->handle()}_collection_create_entry");
        $taxonomies = Taxonomies::all()
            ->map(fn ($taxonomy) => "messages.{$taxonomy->handle()}_taxonomy_create_term");

        $collections->concat($taxonomies)->each(function ($key) {
            if (trans($key) === $key) {
                throw new \Exception("Missing translation key: {$key}");
            }
        });
    }
}
