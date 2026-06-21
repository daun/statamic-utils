<?php

namespace Daun\StatamicUtils\Modifiers;

use Statamic\Modifiers\Modifier;

class QrCode extends Modifier
{
    /**
     * Return a QR code image URL for a given url or phone number.
     */
    public function index($value, $params, $context)
    {
        if (! $value || ! is_string($value)) {
            return null;
        }

        $text = urlencode($value);

        return "https://quickchart.io/qr?text={$text}&ecLevel=L&margin=0&size=200&format=svg";
    }
}
