<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Contracts\Query\Builder;
use Statamic\Entries\Entry;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;

class RelationshipTitle
{
    public function handle($value, $field = null, $searchable = null)
    {
        $items = $this->augment($searchable, $field);

        $value = match (true) {
            $items instanceof Collection => $items->map($this->title(...))->filter()->values()->all(),
            default => Arr::wrap($this->title($items)),
        };

        return [$field => $value];
    }

    public function augment($searchable, $field)
    {
        $items = $searchable->augmentedValue($field)->value();

        return $items instanceof Builder
            ? $items->get()
            : $items;
    }

    public function title($item)
    {
        return match (true) {
            $item instanceof Entry => $item->value('title'),
            $item instanceof Term => $item->value('title'),
            $item instanceof LocalizedTerm => $item->value('title'),
            default => $item->title ?? null,
        };
    }
}
