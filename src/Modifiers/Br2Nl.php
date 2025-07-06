<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class Br2Nl extends Modifier
{
    /**
     * Strip tags but keep line breaks as intended by HTML
     *
     * @param  mixed  $value  The html string
     */
    public function index($value, $params): string
    {
        $html = (string) $value;

        return $this->br2nl($html);
    }

    protected function br2nl(string $html): string
    {
        // Remove tags except <p> and <br>, but keep whitespace around elements
        $html = strip_tags(str_replace('<', ' <', $html), '<br><p>');

        // Transform <br> into \n and <p> into \n\n
        $html = preg_replace('#\s*<br[^>]*[/]?>\s*#msi', "\n", $html);
        $html = preg_replace('#</p>.*?<p>#msi', "\n\n", $html);

        // Strip remaining tags
        $html = strip_tags($html);

        // Trim whole string, and each line separately
        $html = trim($html);
        $html = implode("\n", array_map('trim', explode("\n", $html)));

        // Remove consecutive spaces
        $html = preg_replace('#[ ]+#', ' ', $html);

        return $html;
    }
}
