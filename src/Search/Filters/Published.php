<?php

namespace Daun\StatamicUtils\Search\Filters;

class Published
{
    public function handle($item)
    {
        return $item->status() === 'published';
    }
}
