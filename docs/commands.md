# Commands

[Laravel Artisan](https://laravel.com/docs/13.x/artisan) console commands for managing Statamic sites.

### UpdateEntryUris

An artisan command that rebuilds the cached URIs for every entry in all collections. Useful after bulk
changes to routes or collection structure.

```bash
php artisan app:update-entry-uris
```

### PruneStaleVendorAssets

An artisan command that finds stale Vite-hashed assets left behind in `public/vendor` after addon
rebuilds. It reads each published Vite manifest and flags hashed files (`[name]-[hash].ext`) that the
manifest no longer references. By default it does a dry run; pass `--force` to delete the stale files.

```bash
php artisan app:assets:prune
php artisan app:assets:prune --force
```
