<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class QueryAppend extends Modifier
{
    /**
     * Modify a value.
     *
     * @param  mixed  $value  The value to be modified
     * @param  array  $params  Any parameters used in the modifier
     * @param  array  $context  Contextual values
     * @return mixed
     */
    public function index($value, $params, $context)
    {
        $query = $params[0] ?? null;
        if (! $query) {
            return $value;
        }

        return str_contains($value, '?')
            ? $value.'&'.$query
            : $value.'?'.$query;
    }
}
