<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class Repeat extends Tags
{
    public function index()
    {
        throw_unless($this->isPair, new \Exception('{{ repeat }} tag must be a pair'));
        throw_unless($this->params->has('times'), new \Exception('{{ repeat }} tag requires a "times" parameter'));

        $times = max(0, (int) $this->params->get('times'));
        $content = $this->parse();

        if ($content = $this->parse()) {
            return str_repeat($content, $times);
        }
    }
}
