<?php

namespace Daun\StatamicUtils\Glide;

use Statamic\Facades\Glide;

/**
 * Names the folder of each Glide render after its params, instead of an md5 of them.
 * Improves gzip compression of image urls. The input is the same params Statamic would hash,
 * already limited to known Glide keys. e.g. `fit-contain_fm-avif_q-75_w-400`.
 */
class GlideHash
{
    private const MAX_LENGTH = 100;

    /**
     * Use readable folder names for all Glide renders.
     */
    public static function register(): void
    {
        Glide::generateHashUsing(static::make(...));
    }

    public static function make(string $source, array $params): string
    {
        return self::readable($params) ?? hash('xxh3', $source.'?'.http_build_query($params));
    }

    /**
     * Pairs of `key-value` joined by underscores.
     */
    private static function readable(array $params): ?string
    {
        $pairs = [];

        foreach ($params as $key => $value) {
            // Same as http_build_query, which leaves out nulls
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $value = (int) $value;
            }

            if (! is_scalar($value) || ! preg_match('/^[a-z]+$/', (string) $key)) {
                return null;
            }

            $pairs[] = $key.'-'.self::escape((string) $value);
        }

        $readable = implode('_', $pairs);

        return ($readable !== '' && strlen($readable) <= self::MAX_LENGTH) ? $readable : null;
    }

    /**
     * Keep the value path-safe and lowercase: any other byte becomes `~` plus its hex code,
     * like percent-encoding. Escaping `~` itself keeps distinct values distinct, and escaping
     * `_` keeps it reserved as the pair separator so names always split back unambiguously.
     * e.g. `watermarks/Logo_2.png` → `watermarks~2f~4cogo~5f2.png`
     */
    private static function escape(string $value): string
    {
        return preg_replace_callback(
            '/[^a-z0-9.\-]/',
            fn (array $match) => sprintf('~%02x', ord($match[0])),
            $value,
        );
    }
}
