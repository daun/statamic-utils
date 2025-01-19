<?php

namespace Daun\StatamicUtils\Modifiers;

use Illuminate\Support\Collection;
use Statamic\Modifiers\Modifier;

class Push extends Modifier
{
    /**
     * Push an item onto an array or collection.
     *
     * @example {{ items = (items | push:{newitem}) }}
     *
     * @param  mixed  $value  The array or collection to modify
     * @param  array  $params  The items to push
     * @return mixed
     */
    public function index($value, $params)
    {
        if (is_array($value)) {
            array_push($value, ...$params);
        } elseif ($value instanceof Collection) {
            $value->push(...$params);
        }

        return $value;
    }
}
