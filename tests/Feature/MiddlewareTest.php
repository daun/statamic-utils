<?php

use Daun\StatamicUtils\Middleware\DynamicDebugMode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

function runDynamicDebugMode(Request $request): bool
{
    config(['app.debug' => false]);

    (new DynamicDebugMode)->handle($request, fn ($request) => new Response('ok'));

    return (bool) config('app.debug');
}

/*
|--------------------------------------------------------------------------
| Dynamic Debug Mode
|--------------------------------------------------------------------------
*/

test('dynamic debug mode leaves debug off when the feature is disabled', function () {
    config([
        'app.dynamic_debug.enabled' => false,
        'app.dynamic_debug.allowed_ips' => ['10.0.0.1'],
    ]);

    $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);

    expect(runDynamicDebugMode($request))->toBeFalse();
});

test('dynamic debug mode enables debug for an allowed ip', function () {
    config([
        'app.dynamic_debug.enabled' => true,
        'app.dynamic_debug.allowed_ips' => ['10.0.0.1'],
    ]);

    $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);

    expect(runDynamicDebugMode($request))->toBeTrue();
});

test('dynamic debug mode ignores an unlisted ip without a valid cookie', function () {
    config([
        'app.dynamic_debug.enabled' => true,
        'app.dynamic_debug.allowed_ips' => ['10.0.0.1'],
    ]);

    $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.5']);

    expect(runDynamicDebugMode($request))->toBeFalse();
});

test('dynamic debug mode enables debug for a matching cookie secret', function () {
    config([
        'app.dynamic_debug.enabled' => true,
        'app.dynamic_debug.allowed_ips' => [],
        'app.dynamic_debug.cookie_name' => 'debug',
        'app.dynamic_debug.cookie_secret' => 's3cret',
    ]);

    $request = Request::create('/', 'GET', cookies: ['debug' => 's3cret']);

    expect(runDynamicDebugMode($request))->toBeTrue();
});

test('dynamic debug mode ignores a mismatched cookie secret', function () {
    config([
        'app.dynamic_debug.enabled' => true,
        'app.dynamic_debug.allowed_ips' => [],
        'app.dynamic_debug.cookie_name' => 'debug',
        'app.dynamic_debug.cookie_secret' => 's3cret',
    ]);

    $request = Request::create('/', 'GET', cookies: ['debug' => 'wrong']);

    expect(runDynamicDebugMode($request))->toBeFalse();
});
