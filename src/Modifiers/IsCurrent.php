<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Modifiers\Modifier;

class IsCurrent extends Modifier
{
    /**
     * Modify a value.
     *
     * @param mixed  $value    The value to be modified
     * @param array  $params   Any parameters used in the modifier
     * @param array  $context  Contextual values
     * @return mixed
     */
    public function index($value, $params, $context)
    {
        $url = $value;

        $includeParents = $params[0] ?? false;

        $currentUrl = URL::getCurrent();
        $absoluteUrl = URL::makeAbsolute($value);
        $siteAbsoluteUrl = Site::current()->absoluteUrl();

        $isCurrent = ! is_null($url) && rtrim($url, '/') === rtrim($currentUrl, '/');
        $isCurrentParent = ! $isCurrent && $includeParents && ! is_null($url) && $siteAbsoluteUrl !== $absoluteUrl && URL::isAncestorOf($currentUrl, $url);

        return $isCurrent || $isCurrentParent;
    }
}
