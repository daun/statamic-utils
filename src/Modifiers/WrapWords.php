<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class WrapWords extends Modifier
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
        $tag = $params[0] ?? 'span';

        $wrapped = array_map(
            fn ($word) => "<{$tag}>{$word}</{$tag}>",
            explode(' ', $value ?: '')
        );

        return implode(' ', $wrapped);
    }
}
