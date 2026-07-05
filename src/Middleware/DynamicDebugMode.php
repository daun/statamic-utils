<?php

namespace Daun\StatamicUtils\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicDebugMode
{
    protected bool $enabled;

    protected ?string $cookieName;

    protected ?string $cookieSecret;

    protected ?array $allowedIps;

    public function __construct()
    {
        $this->enabled = (bool) config('app.dynamic_debug.enabled');
        $this->cookieName = config('app.dynamic_debug.cookie_name');
        $this->cookieSecret = config('app.dynamic_debug.cookie_secret');
        $this->allowedIps = config('app.dynamic_debug.allowed_ips');
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldEnableDebugMode($request)) {
            $this->enableDebugMode();
        }

        return $next($request);
    }

    protected function enableDebugMode(): void
    {
        config(['app.debug' => true]);
    }

    protected function shouldEnableDebugMode(Request $request): bool
    {
        return $this->enabled && (
            $this->shouldAllowByIp($request) ||
            $this->shouldAllowByCookie($request)
        );
    }

    protected function shouldAllowByIp(Request $request): bool
    {
        return is_array($this->allowedIps)
            && in_array($request->ip(), $this->allowedIps);
    }

    protected function shouldAllowByCookie(Request $request): bool
    {
        return $this->cookieName
            && $request->hasCookie($this->cookieName)
            && $request->cookie($this->cookieName) === $this->cookieSecret;
    }
}
