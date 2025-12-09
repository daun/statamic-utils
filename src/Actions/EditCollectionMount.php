<?php

namespace Daun\StatamicUtils\Actions;

use Statamic\Actions\Action;
use Statamic\Entries\Collection;

class EditCollectionMount extends Action
{
    protected $confirm = false;

    public static function title()
    {
        return __('Edit Mount Page');
    }

    public function run($items, $values)
    {
        // Redirect only
    }

    public function redirect($items, $values)
    {
        return $items->first()?->mount()?->editUrl() ?? false;
    }

    public function visibleTo($item)
    {
        return $item instanceof Collection && $item->mount();
    }

    public function visibleToBulk($items)
    {
        return false;
    }
}
