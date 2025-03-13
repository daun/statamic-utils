<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class Published extends Scope
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
        // The Comb search driver doesn't support whereStatus()
        if (method_exists($query, 'whereStatus')) {
            $query->whereStatus('published');
        } else {
            $query->where('status', 'published');
        }
    }
}
