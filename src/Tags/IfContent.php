<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class IfContent extends Tags
{
    private const CONTENT_TAGS = [
        'img',
        'svg',
        'video',
        'audio',
        'iframe',
        'script',
        'canvas',
        'embed',
        'hr',
        'input',
        'button',
        'select',
        'textarea',
        'meter',
        'progress',
    ];

    public function index()
    {
        throw_unless($this->isPair, new \Exception('{{ if_content }} tag must be a pair'));

        $content = $this->parse();
        $cleaned = trim(strip_tags($content, self::CONTENT_TAGS));

        if ($cleaned) {
            return $content;
        }
    }
}
