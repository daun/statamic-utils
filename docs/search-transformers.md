# Search Transformers

[Search transformers](https://statamic.dev/search#transforming-fields) reshape field data before it is stored in search indexes.

- `BardText`: Extract [plain text](https://statamic.dev/modifiers/bard_text) from a Bard field.
- `RelationshipTitle`: Return an array of titles of linked relationships (entries, terms, etc)
- `RelationshipTitleLocalizations`: Return an array of relationship titles across all languages

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
