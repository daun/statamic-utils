<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Assets\Asset;
use Statamic\Facades\Site;
use Statamic\Modifiers\Modifier;
use Statamic\Statamic;

class AssetMeta extends Modifier
{
    /**
     * Get an asset's meta value by key, iterating over possible locales.
     *
     * @param  mixed  $value  The Asset object
     * @return mixed
     */
    public function index($value, $params, $context)
    {
        $key = $params[0] ?? null;
        $locale = $params[1] ?? null;

        if (! $key) {
            return null;
        }

        if (! $value instanceof Asset) {
            return null;
        }

        $candidates = collect([
            $locale,
            app()->getLocale(),
            Site::current()->shortLocale(),
            Site::current()->handle(),
        ])
            ->filter()
            ->unique()
            ->map(fn ($locale) => "{$key}_{$locale}")
            ->merge([$key]);

        $value = $candidates->reduce(fn ($result, $key) => $result ?? $value->get($key));

        if (is_array($value)) {
            return Statamic::modify($value)->bardHtml()->fetch();
        } else {
            return (string) $value;
        }
    }
}
