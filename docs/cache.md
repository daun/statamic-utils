# Cache

Utilities for [static caching](https://statamic.dev/static-caching) and its configuration.

### Query Params

Get an up-to-date list of marketing query params to ignore when caching a page.

```php
// config/statamic/static_caching.php

return [
    'disallowed_query_strings' => \Daun\StatamicUtils\Cache\QueryParams::toIgnore()
];
```
