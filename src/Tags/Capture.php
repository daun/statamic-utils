<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Facades\Cascade;
use Statamic\Tags\Tags;

class Capture extends Tags
{
    public function wildcard($method)
    {
        throw_unless($this->isPair, new \Exception('{{ capture }} tag must be a pair'));

        if ($this->params->has('when') && ! $this->params->bool('when')) {
            return;
        }

        $var = $method === 'index'
            ? $this->params->get('to', 'captured')
            : $method;

        $trim = $this->params->bool('trim', false);

        $content = $trim ? trim($this->parse()) : $this->parse();

        Cascade::set($var, $content);
    }
}
