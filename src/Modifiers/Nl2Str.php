<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class Nl2Str extends Modifier
{
    /**
     * Replace newlines with a specified string.
     *
     * @param  mixed  $value  The html string
     * @param  array  $params  String to replace newlines with
     */
    public function index($value, $params): string
    {
        $html = (string) $value;
        $glue = $params[0] ?? ' ';

        return $this->nl2str($html, $glue);
    }

    protected function nl2str(string $html, string $glue): string
    {
        $lines = explode("\n", $html);
        $lines = array_map(fn ($line) => trim(rtrim($line, $glue)), $lines);
        $lines = array_filter($lines);
        $html = implode($glue, $lines);

        return $html;
    }
}
