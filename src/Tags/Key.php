<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class Key extends Tags
{
    /**
     * The {{ key:tag }} tag.
     *
     * @return string|array
     */
    public function tag()
    {
        throw_unless($this->isPair, new \Exception('{{ key:tag }} must be a pair'));

        $content = $this->parse();
        $key = md5($content);
        $attr = " key='{$key}' data-skip-morph-if-keys-equal";
        $content = preg_replace('/^(\s*<\S+)(>|\s)/', '\\1'.$attr.'\\2', $content);

        return $content;
    }
}
