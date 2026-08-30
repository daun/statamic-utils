<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class Localization extends Scope
{
    protected static $aliases = [
        'descendant',
    ];

    public function apply($query, $values)
    {
        $query->whereNotNull('origin');
    }
}
