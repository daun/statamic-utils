<?php

namespace Daun\StatamicUtils\Modifiers;

use Countable;
use Traversable;
use Illuminate\Support\Arr;
use Statamic\Facades\Compare;
use Statamic\Modifiers\Modifier;

class ToIterable extends Modifier
{
    /**
     * Wrap a value in an array if it is not already iterable.
     *
     * @param mixed  $value  The value to make iterable
     *
     * @return Countable|Traversable|array
     */
    public function index($value, $params, $context)
    {
        if (is_iterable($value) || Compare::isQueryBuilder($value) || $value instanceof Countable) {
            return $value;
        }

        return Arr::wrap($value);
    }
}
