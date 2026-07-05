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

### Asset Meta

Get an asset's meta value by key, iterating over possible locales. Falls back to the unsuffixed key
if no localized value is found. Array values are rendered as Bard HTML.

```antlers
{{ caption = asset | asset_meta('caption') }}
```

Pass an explicit locale as the optional second parameter to prioritize it over the current locale.

```antlers
{{ caption = asset | asset_meta('caption', 'de') }}
```

### Br 2 Nl

Strip tags, but keep line breaks as visually intended by the html.

```antlers
<p>{{ rich_text | br2nl }}</p>
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

### Hostname

Extract the hostname from a URL, stripping a leading `www.`. Non-URL values return `null`.

```antlers
{{ 'https://www.example.com/path' | hostname }} {{# example.com #}}
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

### Nl 2 Str

Replace newlines with a specified string.

```antlers
<p>{{ rich_text | nl2str(', ') }}</p>
```

### Orientation

Determine the orientation of an aspect ratio value. Returns `portrait`, `landscape`, or `square`.

```antlers
{{ orientation = asset:ratio | orientation }}
```

A ratio is considered square if it is within 5% of 1:1. Use the optional first parameter to specify
a custom threshold, e.g. `1.25` for a 25% tolerance or `1.0` for a strict 1:1 ratio.

```antlers
{{ orientation = image | orientation(1.25) }}
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

### Qr Code

Return a QR code image URL (SVG) for a given URL or phone number, generated via [quickchart.io](https://quickchart.io/).

```antlers
<img src="{{ url | qr_code }}" alt="QR code">
```

### Query Append

Append a query string to a URL, using `?` or `&` as appropriate depending on whether the URL already
contains a query string.

```antlers
{{ url | query_append('utm_source=newsletter') }}
```

### Resolve

Resolves unfetched query builder queries to their results. Useful when passing around entries
field values in combination with `nocache` tags to avoid serialization issues.

```antlers
{{ partial:partials/data-table :rows="news | resolve" }}
```

### Standard Ratio

Map an aspect ratio value to its closest equivalent in a predefined set of standard ratios.

```antlers
{{ ratio = asset:ratio | standard_ratio }}
```

Default ratios are 1:1, 4:3, 3:2, 16:9 and their inverses. You can globally define custom ratios:

```php
\Daun\StatamicUtils\Modifiers\StandardRatio::define(['5/4', '4/5']);
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

### Wrap Words

Wrap each word in a `<span>` tag. Use the optional first parameter to specify a different wrapper tag.

```antlers
<p>{{ title | wrap_words }}</p>
<ul>
  {{ title | wrap_words('li') }}
</ul>
```

## Tags

### Capture

Capture the output of a template section and assign it to a variable. Similar to assigning the output
of a partial view to a variable, but without the need for an actual partial file.

```antlers
{{ capture:contents }}
    Any output inside of this will land in the `contents` variable.
{{ /capture:contents }}
```

An optional `trim` parameter will trim the output of whitespace.

```antlers
{{ capture:contents trim="true" }}
    {{ title }}
{{ /capture:contents }}
```

An optional `when` parameter will only render and capture the output if the condition is met.

```antlers
{{ capture:contents :when="count >= 1" }}
    Found {{ count }} results
{{ /capture:contents }}
```

### Icon

Render an SVG icon from an existing sprite map.

```antlers
{{ icon:search }}
```

```html
<svg class="icon icon-search" preserveAspectRatio="xMinYMid" aria-hidden="true">
    <use xlink:href="#icon-search">
</svg>
```

### IfContent

Render a block of content only if it is not empty, i.e. if it contains actual text content. A block
of content containing only whitespace or empty tags will not be rendered.

```antlers
{{ if_content }}
    <ul>
        {{ categories }} <li>{{ title }}</li> {{ /categories }}
        {{ tags }} <li>{{ title }}</li> {{ /tags }}
    </ul>
{{ /if_content }}
```

### Repeat

Render the contained content a specified number of times.

```antlers
{{ repeat times="3" }}
    <p>This paragraph will be repeated 3 times.</p>
{{ /repeat }}
```

### Get Mount Root

Find the root entry mounted at the current URL. Useful for locating the top-level page of a mounted
collection. Pass an explicit URL via the `of` parameter to look it up somewhere other than the current page.

```antlers
{{ get_mount_root }}
    Root page: {{ title }}
{{ /get_mount_root }}

{{ get_mount_root of="/blog/my-post" }}
```

### Key

Inject a stable `key` attribute into the first element of a block so a morphing frontend (e.g. Alpine
/ Livewire) skips re-rendering it when its content is unchanged. Must be used as a pair.

```antlers
{{ key:tag }}
    <div class="card">{{ content }}</div>
{{ /key:tag }}
```

```html
<div key="<md5-of-rendered-content>" data-skip-morph-if-keys-equal class="card">...</div>
```

### Random

Generate a random value. Use `{{ random }}` for a 32-character hexadecimal hash, or `{{ random:int }}`
for a random integer. The integer variant accepts optional `min` (default `1`) and `max` (default
`PHP_INT_MAX`) parameters.

```antlers
{{ random }}                        {{# e.g. 9e107d9d372bb6826bd81d3542a419d6 #}}
{{ random:int }}                    {{# random integer #}}
{{ random:int min="1" max="10" }}   {{# random integer between 1 and 10 #}}
```

## Actions

### Edit Collection Mount

Redirect to the edit form of a collection's mount page from its entry listing view.

### Show Mount Entries

Redirect to the entry listing view of a collection's mount page from its entry edit view.

### Connect to Origin

Connect an entry to an origin (localization source) directly from its edit form. Only visible for
entries in multi-site collections that don't yet have an origin, letting editors pick a candidate
entry in another language to link to.

### Set Asset Attribution

Bulk-set a copyright or credit attribution on selected media assets. Provides an option to skip assets
that already have an attribution, so existing data isn't overwritten unless requested.

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
- `RelationshipTitle`: Return an array of titles of all relationships (entries, terms, etc)
- `RelationshipTitleLocalizations`: Return an array of relationship titles in all languages

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

## Forms

### SendRateLimitedEmail

A job that sends form submission emails with a rate limit of 1 per second. It extends/wraps the
default `SendEmail` job and can be used as a drop-in replacement with otherwise identical behavior.

```php
// config/statamic/forms.php
return [
    'send_email_job' => \Daun\StatamicUtils\Forms\SendRateLimitedEmail::class,
];
```

## Cache

### Query Params

Get an up-to-date list of marketing query params to ignore when caching a page.

```php
// config/statamic/static_caching.php

return [
    'disallowed_query_strings' => \Daun\StatamicUtils\Cache\QueryParams::toIgnore()
];
```

## Utilities

### Data Resolution

The `Resolver` class provides a way of resolving wrapped data to their actual underlying values. This is useful for value objects, query builders, fluent tags, etc.

```php
use Daun\StatamicUtils\Data\Resolver;

$value = /* query builder, wrapped in value object */;
$actual = Resolver::actual($value);

```

### Control Panel

#### Translations

Ensure the existence of customized `Create Entry` buttons for all data types. Throws an exception if
a collection or taxonomy is missing the required translation key.

```php
\Daun\StatamicUtils\ControlPanel\Translations::ensureCreateButtonLabels();
```

## License

[MIT](https://opensource.org/licenses/MIT)
