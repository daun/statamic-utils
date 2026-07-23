# Search Transformers

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
