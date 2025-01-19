<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Illuminate\Support\Collection;
use Statamic\Contracts\Query\Builder;

class RelationshipTitle
{
    public function handle($value, $field, $searchable)
    {
        if (! $value) {
            return null;
        }

        $items = $searchable->augmentedValue($field)->value();

        if ($items instanceof Collection) {
            return $items->map->get('title')->all();
        } else if ($items instanceof Builder) {
            return $items->get()->pluck('title');
        } else {
            return $items->title ?? null;
        }
    }
}
