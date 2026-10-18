<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class ToInt extends Modifier
{
    /**
     * Modify a value.
     *
     * @param  mixed  $value  The value to be modified
     * @return mixed
     */
    public function index($value)
    {
        if (is_iterable($value)) {
            return array_map(fn ($item) => intval($item, 10), (array) $value);
        }

        return intval($value, 10);
    }
}
