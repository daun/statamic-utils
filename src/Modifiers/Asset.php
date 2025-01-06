<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Modifiers\Modifier;

class Asset extends Modifier
{
    /**
     * Return or find an asset by id or url.
     *
     * @param mixed  $value  The asset id/url or potential Asset object
     *
     * @return Statamic\Contracts\Assets\Asset|null
     */
    public function index($value): ?AssetContract
    {
        if (!$value) {
            return null;
        }

        if (is_string($value)) {
            return AssetFacade::find($value);
        }

        if ($value instanceof AssetContract) {
            return $value;
        }

        return null;
    }
}
