<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class Orientation extends Modifier
{
    public function index($value, $params)
    {
        if (! $value) {
            return null;
        }

        $square = $params[0] ?? 1.05;

        return match (true) {
            $value > $square => 'landscape',
            $value < 1 / $square => 'portrait',
            default => 'square',
        };
    }
}
