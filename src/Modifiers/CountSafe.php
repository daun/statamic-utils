<?php

namespace Daun\StatamicUtils\Modifiers;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Statamic\Facades\Compare;
use Statamic\Modifiers\Modifier;

class CountSafe extends Modifier
{
    /**
     * Count the number of items in an array or iterable.
     * Returns 0 for `null` values and 1 for non-array values.
     *
     * @param mixed  $value    The array or iterable to be counted
     *
     * @return int
     */
    public function index($value)
    {
        if (Compare::isQueryBuilder($value) || $value instanceof Countable) {
            return $value->count();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        $value = Arr::wrap($value);

        return count($value);
    }
}
