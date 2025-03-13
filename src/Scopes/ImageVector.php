<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class ImageVector extends Scope
{
    /**
     * Apply the scope.
     *
     * @param \Statamic\Query\Builder $query
     * @param array $values
     * @return void
     */
    public function apply($query, $values)
    {
        $query->whereIn('extension', [
            'svg'
        ]);
    }
}
