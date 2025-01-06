<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class Max extends Modifier
{
    /**
     * Return the highest value in an array or collection.
     *
     * @param mixed  $value  The array or collection
     *
     * @return mixed
     */
    public function index($value)
    {
        return max(collect($value)->all());
    }
}
