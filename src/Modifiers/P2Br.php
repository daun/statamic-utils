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
        $numBreaks = $params[0] ?? 2;

        $html = (string) $value;

        if (stripos($html, '<p') === false) {
            return $html;
        }

        $breaks = str_repeat('<br />', $numBreaks);

        // Remove opening <p> tags
        $html = preg_replace('#<p[^>]*?>#', '', $html);

        // Remove trailing </p> to prevent it to be replaced with unneeded <br />
        $html = preg_replace('#</p>$#', '', $html);

        // Replace each end of paragraph with two <br />
        $html = str_replace('</p>', $breaks, $html);

        return $html;
    }
}
