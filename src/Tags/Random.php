<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class Random extends Tags
{
    /**
     * The {{ random }} tag.
     *
     * @return string
     */
    public function index()
    {
        return md5(uniqid((string) rand(), true));
    }

    /**
     * The {{ random:int }} tag.
     *
     * @return string|array
     */
    public function int()
    {
        return random_int(
            $this->params->get('min', 1),
            $this->params->get('max', PHP_INT_MAX)
        );
    }
}
