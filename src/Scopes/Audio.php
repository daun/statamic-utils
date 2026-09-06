<?php

namespace Daun\StatamicUtils\Scopes;

use Statamic\Query\Builder;
use Statamic\Query\Scopes\Scope;

class Audio extends Scope
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
            'aac', 'AAC',
            'aiff', 'AIFF',
            'flac', 'FLAC',
            'm4a', 'M4A',
            'mp3', 'MP3',
            'ogg', 'OGG',
            'wav', 'WAV',
        ]);
    }
}
