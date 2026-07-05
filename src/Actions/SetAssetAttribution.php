<?php

namespace Daun\StatamicUtils\Actions;

use Statamic\Actions\Action;
use Statamic\Contracts\Assets\Asset;

class SetAssetAttribution extends Action
{
    protected string $attributionField = 'attribution';

    public function buttonText()
    {
        return __('Set Attribution|Set Attribution');
    }

    public function confirmationText()
    {
        return __('Set a new copyright or credit for this asset.|Set a new copyright or credit for the :count selected assets.');
    }

    protected function fieldItems()
    {
        return [
            'attribution' => [
                'display' => __('Attribution'),
                'instructions' => __('The new attribution to apply to all selected assets'),
                'type' => 'text',
                'required' => true,
            ],
            'overwrite' => [
                'display' => __('Overwrite existing data'),
                'instructions' => __('If the selected assets already have an attribution, should it be overwritten?'),
                'type' => 'toggle',
                'inline_label' => __('No'),
                'inline_label_when_true' => __('Yes'),
                'default' => true,
            ],
        ];
    }

    public static function title()
    {
        return __('Set Attribution');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset && $item->isMedia();
    }

    public function run($assets, $values)
    {
        $attribution = $values['attribution'];
        $overwrite = $values['overwrite'];

        $updated = 0;

        $assets->each(function (Asset $asset) use ($attribution, $overwrite, &$updated) {
            if (! $overwrite && $asset->get($this->attributionField)) {
                return;
            }

            $asset->set($this->attributionField, $attribution);
            $asset->saveQuietly();
            $updated++;
        });

        if ($updated === 1) {
            return __('Attribution updated.');
        } else if ($updated) {
            return __('Attribution updated for :count assets.', ['count' => $updated]);
        } else {
            return __('No assets were updated.');
        }
    }
}
