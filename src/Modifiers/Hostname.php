<?php

namespace Daun\StatamicUtils\Modifiers;

use Illuminate\Support\Str;
use Statamic\Modifiers\Modifier;

class Hostname extends Modifier
{
    public function index($value, $params, $context)
    {
        if (! $value || ! Str::isUrl($value)) {
            return null;
        }

        return Str::of($value)
            ->after('://')
            ->chopStart('www.')
            ->before('/')
            ->toString();
    }
}
