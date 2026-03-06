<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Site as Sites;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;

class MultiLanguageRelationshipTitle extends RelationshipTitle
{
    public function handle($value, $field = null, $searchable = null)
    {
        $items = $this->augment($searchable, $field);

        $value = $this->localizations($items)
            ->map($this->title(...))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [$field => $value];
    }

    public function localizations($item): Collection
    {
        $locales = Sites::all()->map->handle();

        return match (true) {
            $item instanceof Collection => $item->map($this->localizations(...))->flatten(),
            $item instanceof Entry => $locales->map(fn($locale) => $item->in($locale)),
            $item instanceof Term => $locales->map(fn($locale) => $item->in($locale)),
            $item instanceof LocalizedTerm => $locales->map(fn($locale) => $item->in($locale)),
            default => collect($item),
        };
    }
}
