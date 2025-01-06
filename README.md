# 🛠️  Statamic Utils

A collection of utilities I use in [Statamic](https://statamic.com/) projects.

## Installation

Install the package via composer:

```bash
composer require daun/statamic-utils
```

## Modifiers

### Is String

Check if a value is a string.

```antlers
{{ if some_var | is_string }}
```

### Asset

Return or find an asset by id or url.

```antlers
{{# Fetch asset if url was passed #}}
{{ image = image | asset }}
```

### Max

Return the highest value in an array or collection.

```antlers
{{ large = sizes | max }}
```

### Min

Return the lowest value in an array or collection.

```antlers
{{ small = sizes | min }}
```

### P 2 Br

Convert paragraph tags to line breaks.

```antlers
<p>{{ rich_text | p2br }}</p>
```

### Push

Push an item onto an array or collection.

```antlers
{{ items = (items | push:{newitem}) }}
```

### To Iterable

Wrap a value in an array if it is not already iterable.

```antlers
Locations: {{ (locations ?? location) | make_iterable | pluck('title') | list }}
```

## Utilities

### Control Panel

#### Translations

Ensure the existence of customized `Create Entry` buttons for all data types. Trows an exception if
a collection or taxonomy is missing the required translation key.

```php
\Daun\StatamicUtils\ControlPanel\Translations::ensureCreateButtonLabels();
```

## License

[MIT](https://opensource.org/licenses/MIT)
