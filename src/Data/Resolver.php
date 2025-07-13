<?php

namespace Daun\StatamicUtils\Data;

use Statamic\Facades\Compare;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Modifiers\Modify;
use Statamic\Tags\FluentTag;

class Resolver {
    public static function actual(...$values): mixed
    {
        foreach ($values as $value) {
            if ($value instanceof Values) {
                $value = $value->all();
            }
            if ($value instanceof Value) {
                $value = $value->value();
            }
            if ($value instanceof LabeledValue) {
                $value = $value->value();
            }
            if ($value instanceof ArrayableString) {
                $value = $value->__toString();
            }
            if (Compare::isQueryBuilder($value)) {
                $value = $value->get();
            }
            if ($value instanceof FluentTag) {
                $value = static::actual($value->fetch());
            }
            if ($value instanceof Modify) {
                $value = static::actual($value->fetch());
            }
            if (isset($value)) {
                return $value;
            }
        }

        return $values[0] ?? null;
    }
}
