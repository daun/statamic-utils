# Search Filters

[Search filters](https://statamic.dev/search#filtering-searchables) control which entries are included in search indexes.

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
