<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Builder;
use Statamic\Query\Scopes\Scope;

class Video extends Scope
{
    /**
     * Apply the scope.
     *
     * @param  Builder  $query
     * @param  array  $values
     * @return void
     */
    public function apply($query, $values)
    {
        $query->whereIn('extension', [
            'h264', 'H264',
            'mp4', 'MP4',
            'm4v', 'M4V',
            'ogv', 'OGV',
            'webm', 'WEBM',
            'mov', 'MOV',
        ]);
    }
}
