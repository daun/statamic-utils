<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Tags\Tags;

class IfContent extends Tags
{
    /**
     * Tag names that count as rendering inner content.
     */
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
        'has-content',
    ];

    /**
     * Patterns that render text at runtime: Vue/Alpine text/html attributes.
     */
    private const TEXT_DIRECTIVE_PATTERN = '/<[a-z][^>]*?\s(?:x|v)-(?:text|html)\s*=/is';

    public function index()
    {
        throw_unless($this->isPair, new \Exception('{{ if_content }} tag must be a pair'));

        $content = $this->parse();

        $content = $this->markRenderedText($content);

        $cleaned = trim(strip_tags($content, self::CONTENT_TAGS));

        if ($cleaned) {
            return $content;
        }
    }

    /**
     * Prepend a <has-content> marker to elements that render text at runtime via x-text / x-html
     */
    private function markRenderedText(string $html): string
    {
        return preg_match(self::TEXT_DIRECTIVE_PATTERN, $html)
            ? '<has-content />' . $html
            : $html;
    }
}
