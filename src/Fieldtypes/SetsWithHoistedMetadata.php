<?php

namespace Daun\StatamicUtils\Fieldtypes;

use Statamic\Facades\Fieldset;
use Statamic\Fieldtypes\Sets;
use Statamic\Support\Arr;

/**
 * Sets with auto-hoisted set metadata.
 *
 * For any Replicator or Bard set whose fields consist of a single fieldset
 * import, missing set metadata (display, instructions, icon) is hoisted
 * from the imported fieldset's top-level keys. Explicit values on the set
 * always win.
 *
 * Drop-in replacement for the core `sets` fieldtype. No need to manually
 * register this fieldtype as it overrides the core `sets` fieldtype.
 */
class SetsWithHoistedMetadata extends Sets
{
    protected static $handle = 'sets';

    public function preProcess($sets)
    {
        return parent::preProcess($this->hoistImportedMetadata($sets));
    }

    public function preProcessConfig($sets)
    {
        return parent::preProcessConfig($this->hoistImportedMetadata($sets));
    }

    private function hoistImportedMetadata($sets)
    {
        $sets = collect($sets);

        if ($sets->isEmpty()) {
            return [];
        }

        // Legacy format: sets at the top level, no groups.
        if (! Arr::has($sets->first(), 'sets')) {
            return $sets->map(fn ($set) => $this->hoistIntoSet($set))->all();
        }

        return $sets->map(function ($group) {
            $group['sets'] = collect($group['sets'] ?? [])
                ->map(fn ($set) => $this->hoistIntoSet($set))
                ->all();

            return $group;
        })->all();
    }

    private function hoistIntoSet(array $set): array
    {
        if (! $handle = $this->importedFieldsetHandle($set)) {
            return $set;
        }

        if (! $fieldset = Fieldset::find($handle)) {
            return $set;
        }

        $contents = $fieldset->contents();

        $set['display'] ??= $contents['display'] ?? $contents['title'] ?? null;
        $set['instructions'] ??= $contents['instructions'] ?? null;
        $set['icon'] ??= $contents['icon'] ?? null;

        return $set;
    }

    private function importedFieldsetHandle(array $set): ?string
    {
        $imports = collect($set['fields'] ?? [])->pluck('import')->filter();

        return $imports->count() === 1 ? $imports->first() : null;
    }
}
