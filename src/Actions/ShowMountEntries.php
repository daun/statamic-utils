<?php

namespace Daun\StatamicUtils\Actions;

use Statamic\Actions\Action;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection as Collections;

class ShowMountEntries extends Action
{
    protected $confirm = false;

    public static function title()
    {
        return __('Show Entries');
    }

    public function run($items, $values)
    {
        // No operation needed, redirect will handle it.
    }

    public function redirect($items, $values)
    {
        return Collections::findByMount($items->first())?->showUrl() ?? false;
    }

    public function visibleTo($item)
    {
        return ($item instanceof Entry)
            && Collections::findByMount($item);
    }

    public function visibleToBulk($items)
    {
        return false;
    }
}
