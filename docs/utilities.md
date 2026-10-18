# Utilities

Various other utilities for Statamic.

## Data

### Resolver

The `Resolver` class provides a way of resolving wrapped data to their actual underlying values. This is useful for value objects, query builders, fluent tags, etc.

```php
use Daun\StatamicUtils\Data\Resolver;

$value = /* query builder, wrapped in value object */;
$actual = Resolver::actual($value);
```

## Glide

### Readable Cache Paths

Name the cache folder of each Glide render after its params instead of an md5 hash, e.g.
`fit-contain_fm-avif_q-75_w-400`. Repeated parameters compress better with gzip, which makes
pages with lots of responsive images smaller. Values are escaped to stay path-safe and lowercase,
and params that can't be spelled out in 100 characters or less fall back to a short xxh3 hash.

```php
\Daun\StatamicUtils\Glide\GlideHash::register();
```

## Control Panel

### Ensure Button Labels

Ensure the existence of customized `Create Entry` buttons for all data types. Throws an exception if
a collection or taxonomy is missing the required translation key.

```php
\Daun\StatamicUtils\ControlPanel\Translations::ensureCreateButtonLabels();
```
