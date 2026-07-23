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

## Documentation

- [Modifiers](docs/modifiers.md)
- [Tags](docs/tags.md)
- [Actions](docs/actions.md)
- [Dictionaries](docs/dictionaries.md)
- [Middleware](docs/middleware.md)
- [Rules](docs/rules.md)
- [Commands](docs/commands.md)
- [Query Scopes](docs/query-scopes.md)
- [Search Filters](docs/search-filters.md)
- [Search Transformers](docs/search-transformers.md)
- [Forms](docs/forms.md)
- [Cache](docs/cache.md)
- [Utilities](docs/utilities.md)

## License

[MIT](https://opensource.org/licenses/MIT)
