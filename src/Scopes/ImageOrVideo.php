<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Scopes\Scope;

class ImageOrVideo extends Scope
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
            'gif',
            'jpg',
            'jpeg',
            'png',
            'apng',
            'webp',
            'avif',
            'svg',
            'h264',
            'mp4',
            'm4v',
            'ogv',
            'webm',
            'mov',
        ]);
    }
}
