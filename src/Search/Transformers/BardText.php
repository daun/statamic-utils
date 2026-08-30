<?php

namespace Daun\StatamicUtils\Search\Transformers;

use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Stringy\StaticStringy as Stringy;

/**
 * Flatten a Bard value to plain text for indexing.
 *
 * Statamic's `bard_text` modifier joins text nodes without a separator and only
 * spaces out paragraphs, so a hard break or heading indexes as "…Konzept &
 * RegieMartin Finnland" and reads that way in search results.
 */
class BardText
{
    public function handle($value, $field = null, $searchable = null)
    {
        if (! $value) {
            return null;
        }

        if ($searchable && $field) {
            $value = $searchable->augmentedValue($field);
        }

        return static::text($value);
    }

    public static function text(mixed $value): ?string
    {
        if ($value instanceof Value) {
            $value = $value->raw();
        }

        $text = match (true) {
            is_string($value) => strip_tags(static::breakTags($value)),
            is_array($value) => static::flatten($value),
            default => null,
        };

        return $text === null ? null : (Stringy::collapseWhitespace($text) ?: null);
    }

    /**
     * Only line and block boundaries separate words; inline tags do not, or
     * `in <strong>Vienna</strong>.` would flatten to `in Vienna .`.
     */
    protected static function breakTags(string $html): string
    {
        return preg_replace('~<\s*(?:br|/p|/div|/li|/h[1-6]|/blockquote|/tr|/t[dh])\b[^>]*>~i', ' ', $html);
    }

    /**
     * @param  array<mixed>  $nodes
     */
    protected static function flatten(array $nodes): string
    {
        if (Arr::isAssoc($nodes)) {
            $nodes = [$nodes];
        }

        $text = '';

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (($node['type'] ?? null) === 'text') {
                $text .= $node['text'] ?? '';

                continue;
            }

            // Anything else is a block or a break — both end the current run
            $text .= static::flatten($node['content'] ?? []).' ';
        }

        return $text;
    }
}
