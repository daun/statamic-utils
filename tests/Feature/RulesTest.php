<?php

use Daun\StatamicUtils\Rules\RequiredIfPublic;

function runRequiredIfPublic(array $data, mixed $value): ?string
{
    $message = null;

    (new RequiredIfPublic)
        ->setData($data)
        ->validate('field', $value, function (string $msg) use (&$message) {
            $message = $msg;
        });

    return $message;
}

/*
|--------------------------------------------------------------------------
| Required If Public
|--------------------------------------------------------------------------
*/

test('required if public passes when a value is present', function () {
    $message = runRequiredIfPublic([
        'published' => true,
        'visibility' => 'listed',
    ], 'a value');

    expect($message)->toBeNull();
});

test('required if public passes for unpublished entries', function () {
    $message = runRequiredIfPublic([
        'published' => false,
        'visibility' => 'listed',
    ], '');

    expect($message)->toBeNull();
});

test('required if public passes when visibility is index', function () {
    $message = runRequiredIfPublic([
        'published' => true,
        'visibility' => 'index',
    ], '');

    expect($message)->toBeNull();
});

test('required if public fails for public entries missing a value', function () {
    $message = runRequiredIfPublic([
        'published' => true,
        'visibility' => 'listed',
    ], '');

    expect($message)->toBe('This field is required when the entry is public.');
});

test('set data returns the rule for chaining', function () {
    $rule = new RequiredIfPublic;

    expect($rule->setData(['published' => true]))->toBe($rule);
});
