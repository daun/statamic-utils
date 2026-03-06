<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class StandardRatio extends Modifier
{
    protected static $enabled = true;

    protected static $ratios = [
        '1/1',
        '4/3',
        '3/4',
        '3/2',
        '2/3',
        '16/9',
        '9/16',
    ];

    /**
     * Return the ratio closest to the given value.
     */
    public function index($value, $params)
    {
        if (! static::$enabled) {
            return $value;
        }

        if (! $value) {
            return '';
        }

        $ratio = $this->getClosestRatio($value);
        $format = $params[0] ?? false;

        return $format ? number_format($ratio, 2, '.', '') : $ratio;
    }

    protected function getClosestRatio($value)
    {
        $value = $this->resolveRatio($value);

        return collect(static::$ratios)->reduce(function ($carry, $item) use ($value) {
            $current = $this->resolveRatio($item);
            $best = $this->resolveRatio($carry);

            return abs($current - $value) < abs($best - $value) ? $current : $best;
        }, 1);
    }

    protected function resolveRatio(mixed $value): float
    {
        if (is_string($value)) {
            [$width, $height] = explode('/', $value);

            return $width / $height;
        } else {
            return floatval($value);
        }
    }

    public static function enable(bool $enabled): void
    {
        static::$enabled = $enabled;
    }

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function define(array $ratios): void
    {
        static::$ratios = $ratios;
    }
}
