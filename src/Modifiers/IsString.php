<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class IsString extends Modifier
{
    /**
     * Check if a value is a string.
     *
     * @param mixed  $value  The value to be checked
     *
     * @return bool
     */
    public function index($value): bool
    {
        return is_string($value);
    }
}
