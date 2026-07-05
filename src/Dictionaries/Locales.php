<?php

namespace Daun\StatamicUtils\Dictionaries;

use Statamic\Dictionaries\BasicDictionary;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class Locales extends BasicDictionary
{
    protected string $labelKey = 'name';

    protected string $valueKey = 'locale';

    protected function getItems(): array
    {
        return SiteFacade::all()->map(fn (Site $site) => [
            'handle' => $site->handle(),
            'locale' => $site->shortLocale(),
            'name' => $site->name(),
        ])->all();
    }
}
