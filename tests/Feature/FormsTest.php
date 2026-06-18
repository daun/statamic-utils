<?php

use Daun\StatamicUtils\Forms\SendRateLimitedEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Statamic\Forms\SendEmail;

function makeRateLimitedJob(): SendRateLimitedEmail
{
    // The parent constructor requires a Submission + Site; we only need to
    // exercise middleware(), so build the instance without the constructor.
    $job = (new ReflectionClass(SendRateLimitedEmail::class))->newInstanceWithoutConstructor();
    $job->config = ['mailer' => 'mailer'];

    return $job;
}

test('rate limited email is a queued drop-in replacement for SendEmail', function () {
    $job = makeRateLimitedJob();

    expect($job)->toBeInstanceOf(SendEmail::class);
    expect($job)->toBeInstanceOf(ShouldQueue::class);
});

test('rate limited email applies rate limiting and overlap middleware', function () {
    $middleware = makeRateLimitedJob()->middleware();

    expect($middleware)->toHaveCount(2);
    expect($middleware[0])->toBeInstanceOf(RateLimited::class);
    expect($middleware[1])->toBeInstanceOf(WithoutOverlapping::class);
});
