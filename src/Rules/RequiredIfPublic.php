<?php

namespace Daun\StatamicUtils\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredIfPublic implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value)) {
            return;
        }

        if (! ($this->data['published'] ?? false)) {
            return;
        }

        if (($this->data['visibility'] ?? null) === 'index') {
            return;
        }

        $fail('This field is required when the entry is public.');
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
