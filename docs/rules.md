# Rules

[Validation](https://statamic.dev/validation) rules to use in blueprint fields and forms.

### RequiredIfPublic

Require a field only when the entry is public — i.e. published and not hidden from indexes
(`visibility` other than `index`). Otherwise the field remains optional.

```php
use Daun\StatamicUtils\Rules\RequiredIfPublic;

$rules = [
    'seo_description' => [new RequiredIfPublic],
];
```
