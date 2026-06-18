<?php

namespace Daun\StatamicUtils\Forms;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Statamic\Forms\SendEmail;

class SendRateLimitedEmail extends SendEmail implements ShouldQueue
{
    public function middleware(): array
    {
        return [
            new RateLimited('mailer')->releaseAfter(1),
            new WithoutOverlapping($this->config['mailer'] ?? 'mailer')->releaseAfter(1)->expireAfter(30),
        ];
    }
}
