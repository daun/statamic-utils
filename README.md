# 🛠️  Statamic Utils

A collection of utilities I use in [Statamic](https://statamic.com/) projects.

## Installation

Install the package via composer:

```bash
composer require daun/statamic-utils
```

## Registration

Modifiers, Tags, Scopes, etc. need to be registered in your app's service provider.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \Daun\StatamicUtils\Modifiers\ToIterable::register();
        \Daun\StatamicUtils\Scopes\Image::register();
    }
}
```

## Modifiers

### Asset

Return or find an asset by id or url.

```antlers
{{# Fetch asset if url was passed #}}
{{ image = image | asset }}
```

### Count Safe

Count the number of items in an array or iterable.
Returns `0` for null values and `1` for non-iterable values.

```antlers
{{ if locations | count_safe }} ... {{ /if }}
```

### Except

Remove keys from an array or collection.

```antlers
{{ params = get | except('page', 'q') }}
```

### Is Current

Check if the current page matches the given URL. Pass `true` to also include ancestors in the comparison.

```antlers
{{ if url | is_current }}
    aria-current="page"
{{ elseif url | is_current(true) }}
    aria-current="true"
{{ /if }}
```

### Is String

Check if a value is a string.

```antlers
{{ if some_var | is_string }}
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

### Resolve

Resolves unfetched query builder queries to their results. Useful when passing around entries
field values in combination with `nocache` tags to avoid serialization issues.

```antlers
{{ partial:partials/data-table :rows="news | resolve" }}
```

### To Int

Convert a value to an integer. Special case: converts a mixed array to an array of integers.

```antlers
{{ number_array = mixed_array | to_int }}
```

### To Float

Convert a value to a float. Special case: converts a mixed array to an array of floats.

```antlers
{{ number_array = mixed_array | to_float }}
```

### To Iterable

Wrap a value in an array if it is not already iterable.

```antlers
Locations: {{ (locations ?? location) | to_iterable | pluck('title') | list }}
```

## Query Scopes

Apply [query scopes](https://statamic.dev/extending/query-scopes-and-filters) to narrow down query results.

- `Published`: Filter out unpublished entries
- `Image`: Filter assets that are images (pixel + vector)
- `ImagePixel`: Filter assets that are pixel images (jpeg, png, gif, etc)
- `ImageVector`: Filter assets that are vector images (svg)
- `ImageOrVideo`: Filter assets that are images or videos
- `Video`: Filter assets that are video files
- `Audio`: Filter assets that are audio files

## Search Filters

Classes for [filtering entries](https://statamic.dev/search#filtering-searchables) for search indexing.

- `Published`: Filter out unpublished entries.
- `All`: Include all entries, regardless of their published status.

```php
return [
    'indexes' => [
        'articles' => [
            'searchables' => 'collection:articles',
            'filter' => \Daun\StatamicUtils\Search\Filters\Published::class,
        ]
    ]
];
```

## Search Transformers

Classes for [transforming fields](https://statamic.dev/search#transforming-fields) for search indexing.

- `BardText`: Extract [plain text](https://statamic.dev/modifiers/bard_text) from a Bard field.
- `RelationshipTitle`: Map relationship fields to an array of titles.

```php
return [
    'indexes' => [
        'articles' => [
            'searchables' => 'collection:articles',
            'transformers' => [
                'content' => \Daun\StatamicUtils\Search\Transformers\BardText::class,
                'categories' => \Daun\StatamicUtils\Search\Transformers\RelationshipTitle::class,
            ]
        ]
    ]
];
```

## Cache

### Query Params

Get an up-to-date list of marketing query params to ignore when caching a page.

```php
// config/statamic/static_caching.php

return [
    'disallowed_query_strings' => Daun\StatamicUtils\Cache\QueryParams::toIgnore()
];
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
