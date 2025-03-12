<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Statamic\Statamic;

class BardText
{
    public function handle($value, $field = null, $searchable = null)
    {
        if (! $value) {
            return null;
        }

        if (! $searchable || ! $field) {
            return $value;
        }

        return Statamic::modify($searchable->augmentedValue($field))
            ->bardText()
            ->fetch();
    }
}
