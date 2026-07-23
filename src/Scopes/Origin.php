<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class Origin extends Scope
{
    public function apply($query, $values)
    {
        $query->whereNull('origin');
    }
}
