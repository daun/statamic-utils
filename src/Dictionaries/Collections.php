<?php

namespace Daun\StatamicUtils\Dictionaries;

use Statamic\Dictionaries\BasicDictionary;
use Statamic\Facades\Collection as CollectionFacade;

class Collections extends BasicDictionary
{
    protected string $labelKey = 'title';

    protected string $valueKey = 'handle';

    protected function getItems(): array
    {
        return CollectionFacade::all()->map(fn ($collection) => [
            'handle' => $collection->handle(),
            'title' => $collection->title(),
        ])->all();
    }
}
