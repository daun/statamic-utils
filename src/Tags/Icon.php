<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class Icon extends Tags
{
    public function wildcard($icon)
    {
        throw_if($this->isPair, new \Exception('{{ icon }} tag cannot be a pair'));

        $ratio = $this->params->get('ratio', 'xMinYMid');
        $ratioAttr = $ratio ? "preserveAspectRatio=\"{$ratio}\"" : "";

        return <<<EOT
            <svg class="icon icon-{$icon}" {$ratioAttr} aria-hidden="true">
                <use xlink:href="#icon-{$icon}" />
            </svg>
        EOT;
    }
}
