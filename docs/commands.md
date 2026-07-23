# Commands

[Laravel Artisan](https://laravel.com/docs/13.x/artisan) console commands for managing Statamic sites.

### UpdateEntryUris

An artisan command that rebuilds the cached URIs for every entry in all collections. Useful after bulk
changes to routes or collection structure.

```bash
php artisan app:update-entry-uris
```
