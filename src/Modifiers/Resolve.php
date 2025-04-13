<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Facades\Compare;
use Statamic\Modifiers\Modifier;

class Resolve extends Modifier
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
        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

        return $value;
    }
}
