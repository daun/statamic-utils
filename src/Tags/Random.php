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
     * @return int
     */
    public function int()
    {
        return random_int(
            $this->params->int('min', 1),
            $this->params->int('max', PHP_INT_MAX)
        );
    }
}
