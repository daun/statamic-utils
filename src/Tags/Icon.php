<?php

namespace Daun\StatamicUtils\Tags;

use Illuminate\Support\Str;
use Statamic\Tags\Tags;

class Icon extends Tags
{
    public function wildcard($icon)
    {
        throw_if($this->isPair, new \Exception('{{ icon }} tag cannot be a pair'));

        $icon = Str::slug($icon);
        $ratio = $this->params->get('ratio', 'xMinYMid');
        $ratioAttr = $ratio ? "preserveAspectRatio=\"{$ratio}\"" : '';

        $label = $this->params->get('label');
        $aria = $label
            ? sprintf('aria-label="%s"', htmlentities($label))
            : 'aria-hidden="true"';

        return <<<EOT
            <svg class="icon icon-{$icon}" {$ratioAttr} {$aria}>
                <use xlink:href="#icon-{$icon}" />
            </svg>
        EOT;
    }
}
