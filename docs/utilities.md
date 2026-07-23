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

## Control Panel

### Ensure Button Labels

Ensure the existence of customized `Create Entry` buttons for all data types. Throws an exception if
a collection or taxonomy is missing the required translation key.

```php
\Daun\StatamicUtils\ControlPanel\Translations::ensureCreateButtonLabels();
```
