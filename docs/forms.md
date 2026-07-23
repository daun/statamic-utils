# Forms

### SendRateLimitedEmail

A job that sends form submission emails with a rate limit of 1 per second. It extends/wraps the
default `SendEmail` job and can be used as a drop-in replacement with otherwise identical behavior.

```php
// config/statamic/forms.php
return [
    'send_email_job' => \Daun\StatamicUtils\Forms\SendRateLimitedEmail::class,
];
```
