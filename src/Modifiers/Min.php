<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class Min extends Modifier
{
    /**
     * Return the lowest value in an array or collection.
     *
     * @param  mixed  $value  The array or collection
     * @return mixed
     */
    public function index($value)
    {
        return min(collect($value)->all());
    }
}
