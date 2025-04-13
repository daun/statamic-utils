<?php

namespace Daun\StatamicUtils\Modifiers;

use Illuminate\Support\Arr;
use Statamic\Modifiers\Modifier;

class Except extends Modifier
{
    /**
     * Modify a value.
     *
     * @param mixed  $value    The value to be modified
     * @param array  $params   Any parameters used in the modifier
     * @param array  $context  Contextual values
     * @return mixed
     */
    public function index($value, $params, $context)
    {
        $keys = Arr::wrap($params);

        if ($wasArray = is_array($value)) {
            $value = collect($value);
        }

        $items = $value->except($keys);

        return $wasArray ? $items->all() : $items;
    }
}
