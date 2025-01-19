<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class Ray extends Tags
{
    /**
     * The {{ ray:* }} tag. Send a single context variable to Ray.
     */
    public function wildcard($tag)
    {
        $this->send([$this->context->get($tag)], $tag);
    }

    /**
     * The {{ ray }} tag. Send all context to Ray.
     */
    public function index()
    {
        $this->send($this->context->all(), 'context');
    }

    protected function send($args, $label = null)
    {
        $ray = ray(...$args);
        if ($label) {
            $ray->label($label);
        }
    }
}
