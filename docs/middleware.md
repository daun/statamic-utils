# Middleware

### DynamicDebugMode

HTTP middleware that conditionally enables Laravel's debug mode at runtime for allow-listed IP
addresses or requests carrying a secret cookie. Configure it under `config/app.php`:

```php
// config/app.php
'dynamic_debug' => [
    'enabled' => env('DYNAMIC_DEBUG_ENABLED', false),
    'allowed_ips' => ['203.0.113.5'],
    'cookie_name' => 'x-debug',
    'cookie_secret' => env('DYNAMIC_DEBUG_SECRET'),
],
```

```php
// Register in bootstrap/app.php or your HTTP kernel
$middleware->append(\Daun\StatamicUtils\Middleware\DynamicDebugMode::class);
```
