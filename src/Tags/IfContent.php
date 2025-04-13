<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class IfContent extends Tags
{
    public function index()
    {
        throw_unless($this->isPair, new \Exception('{{ if_content }} tag must be a pair'));

        $content = $this->parse();
        $cleaned = trim(strip_tags($content, '<img><svg><video><audio><iframe>'));

        if ($cleaned) {
            return $content;
        }
    }
}
