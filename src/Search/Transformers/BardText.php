<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Statamic\Statamic;

class BardText
{
    public function handle($value, $field, $searchable)
    {
        return Statamic::modify($searchable->augmentedValue($field))
            ->bardText()
            ->fetch();
    }
}
