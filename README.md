# 🛠️  Statamic Utils

A collection of utilities I use in [Statamic](https://statamic.com/) projects.

## Installation

Install the package via composer:

```bash
composer require daun/statamic-utils
```

## Utilities

### Control Panel

#### Translations

Ensure the existence of customized `Create Entry` buttons for all data types. Trows an exception if
a collection or taxonomy is missing the required translation key.

```php
\Daun\StatamicUtils\ControlPanel\Translations\ensureCreateButtonLabels();
```

## License

[MIT](https://opensource.org/licenses/MIT)
