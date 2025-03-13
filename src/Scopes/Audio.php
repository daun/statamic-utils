<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class Audio extends Scope
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
            'aac',
            'aiff',
            'flac',
            'm4a',
            'mp3',
            'ogg',
            'wav',
        ]);
    }
}
