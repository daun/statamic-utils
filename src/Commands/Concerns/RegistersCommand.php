<?php

namespace Daun\StatamicUtils\Commands\Concerns;

use Illuminate\Console\Application as Artisan;

trait RegistersCommand
{
    /**
     * Register the command with Artisan. Resolution is deferred until
     * Artisan boots, so this adds no overhead to web requests.
     */
    public static function register(): void
    {
        Artisan::starting(fn (Artisan $artisan) => $artisan->resolve(static::class));
    }
}
