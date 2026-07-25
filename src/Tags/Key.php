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

        return preg_replace_callback(
            '/^(\s*<[^\s>\/]+)((?:"[^"]*"|\'[^\']*\'|[^\'">])*)(>)/s',
            function (array $matches) use ($key): string {
                $hasId = preg_match(
                    '/(?:"[^"]*"|\'[^\']*\')(*SKIP)(*F)|(?:^|\s)id\s*=/i',
                    $matches[2],
                ) === 1;
                $id = $hasId ? '' : " id='key-{$key}'";
                $attributes = "{$id} key='{$key}' data-skip-morph-if-keys-equal";

                return $matches[1].$attributes.$matches[2].$matches[3];
            },
            $content,
        );
    }
}
