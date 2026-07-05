<?php

namespace Daun\StatamicUtils\Actions;

use Statamic\Actions\Action;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as Entries;

class ConnectToOrigin extends Action
{
    protected ?Entry $visibleToEntry = null;

    protected ?Entry $updatedEntry = null;

    public static function title()
    {
        return __('Connect to Origin');
    }

    public function visibleTo($item)
    {
        if ($this->isInEditForm() && $this->isEntryMissingOrigin($item)) {
            $this->visibleToEntry = $item;

            return true;
        } else {
            return false;
        }
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('edit', $item);
    }

    protected function fieldItems()
    {
        return [
            'origin' => [
                'type' => 'select',
                'display' => 'Origin',
                'placeholder' => 'Choose Entry...',
                'required' => true,
                'options' => $this->getOriginCandidates(),
            ],
        ];
    }

    public function buttonText()
    {
        return 'Connect to Origin|Connect :count Entries to Origin';
    }

    public function confirmationText()
    {
        return 'This will connect the current entry to the chosen origin.|This will connect :count entries to the chosen origin.';
    }

    public function run($items, $values)
    {
        $origin = Entries::query()
            ->where('id', $values['origin'] ?? null)
            ->first();

        if (! $origin) {
            throw new \Exception('Origin not found.');
        }

        $items->each(function (Entry $item) use ($origin) {
            if ($item->hasOrigin()) {
                throw new \Exception('Entry already has an origin.');
            }
            if ($origin->descendants()->count()) {
                throw new \Exception('Origin is already assigned to another entry.');
            }
            if ($item->locale() === $origin->locale()) {
                throw new \Exception('Entry and origin have the same language.');
            }

            $this->updatedEntry = $item;
            $item->origin($origin);
            $item->save();
        });

        return __('Origin assigned: :title', ['title' => $origin->get('title')]);
    }

    public function redirect($items, $values)
    {
        if ($this->isInEditForm() && $this->updatedEntry) {
            return $this->updatedEntry->editUrl();
        }
    }

    protected function getOriginCandidates()
    {
        $collection = $this->context['collection'];
        $currentLocale = $this->visibleToEntry?->locale() ?? null;

        $existingOrigins = Entries::query()
            ->where('collection', $collection)
            ->whereNotNull('origin')
            ->select('origin')
            ->get()
            ->map->origin()
            ->filter()
            ->map->id()
            ->unique();

        return Entries::query()
            ->where('collection', $collection)
            ->where('origin', null)
            ->whereNotIn('id', $existingOrigins)
            ->whereNot('site', $currentLocale)
            ->orderBy('title')
            ->get()
            ->sortBy(fn ($entry) => $entry->locale())
            ->mapWithKeys(fn ($entry) => [$entry->id() => $this->getOriginTitle($entry)]);
    }

    protected function getOriginTitle(Entry $entry): string
    {
        return sprintf('[%s] %s', strtoupper($entry->locale()), $entry->value('title'));
    }

    protected function isInEditForm(): bool
    {
        return ($this->context['view'] ?? null) === 'form';
    }

    protected function isEntryMissingOrigin(mixed $item): bool
    {
        return $item instanceof Entry
            && $item->collection()->sites()->count() > 1
            && ! $item->hasOrigin();
    }
}
