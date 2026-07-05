<?php

namespace Daun\StatamicUtils\Tags;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class GetMountRoot extends Tags
{
    /**
     * The {{ get_mount_root }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        $url = $this->params->get('of', URL::getCurrent());
        $uri = Str::start(Str::after(URL::makeAbsolute($url), Site::current()->absoluteUrl()), '/');

        $entries = Entry::query()
            ->where('site', Site::current()->handle())
            ->where('uri', $uri)
            ->get();

        return $entries->first(fn ($entry) => $entry->page()?->isRoot());
    }
}
