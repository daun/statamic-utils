<?php

use Daun\StatamicUtils\Forms\SendRateLimitedEmail;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;

function makeRateLimitedJob(): SendRateLimitedEmail
{
    // The parent constructor requires a Submission + Site; we only need to
    // exercise middleware(), so build the instance without the constructor.
    $job = (new ReflectionClass(SendRateLimitedEmail::class))->newInstanceWithoutConstructor();
    $job->config = ['mailer' => 'mailer'];

    return $job;
}

test('rate limited email applies rate limiting and overlap middleware', function () {
    $middleware = makeRateLimitedJob()->middleware();

    expect($middleware)->toHaveCount(2);
    expect($middleware[0])->toBeInstanceOf(RateLimited::class);
    expect($middleware[1])->toBeInstanceOf(WithoutOverlapping::class);
});
