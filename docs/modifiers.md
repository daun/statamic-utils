# Modifiers

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
