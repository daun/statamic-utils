<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class P2Br extends Modifier
{
    /**
     * Convert paragraph tags to line breaks.
     *
     * @param  mixed  $value  The html string
     * @param  array  $params  Number of line breaks to replace each paragraph
     */
    public function index($value, $params): string
    {
        $html = (string) $value;
        $numBreaks = $params[0] ?? 2;
        if (! str_contains($html, '<p')) return $html;

        return $this->p2br($html, $numBreaks);
    }

    protected function p2br(string $html, int $numBreaks = 2): string
    {
        // Remove opening <p> tags
        $html = preg_replace('#<p[^>]*?>#', '', $html);

        // Remove trailing </p> to prevent it to be replaced with unneeded <br />
        $html = preg_replace('#</p>$#', '', $html);

        // Replace each end of paragraph with two <br />
        $breaks = str_repeat('<br />', $numBreaks);
        $html = str_replace('</p>', $breaks, $html);

        return $html;
    }
}
