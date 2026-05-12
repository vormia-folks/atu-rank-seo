# ATU Rank SEO

A companion SEO package for the Vormia ecosystem. ATU Rank SEO provides centralized, snapshot-based SEO management tightly integrated with Vormia's `SlugRegistry`, enabling page-level and media-level SEO similar in spirit to Yoast SEO (WordPress), but designed for Laravel applications.

Current package version: **1.3.1** (`Vormia\ATURankSEO\ATURankSEO::VERSION`).

## Features

- **Snapshot-based SEO**: Resolved on save, not runtime
- **Slug-driven**: SEO rows reference Vormia's SlugRegistry; the package does not ship migrations that alter `vrm_slug_registry` (snapshot generation may still `firstOrCreate` a slug row — see developer guide)
- **Page & Media SEO**: Support for both page-level and media-level SEO
- **Cache-first**: Optimized for performance with cache-first resolution
- **UI-driven Management**: Admin panel for managing SEO entries
- **Placeholder Support**: Dynamic placeholder resolution (e.g., `{make}`, `{model}`, `{year}`)
- **Global SEO Settings**: Centralized default SEO values

## Installation

### Via Composer

```bash
composer require vormia-folks/atu-rank-seo
```

### Run Installation Command

```bash
php artisan aturankseo:install
```

This will:

- Optionally add environment variables to `.env` and `.env.example` (unless `--skip-env`)
- Optionally run `php artisan migrate` (interactive confirmation; default **yes**)
- Optionally run the package seeder (only after migrations succeed; interactive confirmation; default **yes** — you can answer **no** to skip seeding)

The installer does **not** copy migrations, views, or routes into your app. Migrations load from the package via `loadMigrationsFrom`. Admin Livewire screens live in the package under `resources/views/livewire/admin/atu/`; `ATURankSEOServiceProvider` registers them with `Livewire::addLocation` and `Route::livewire(...)`. Optional config publish:

```bash
php artisan vendor:publish --tag=aturankseo-config
```

## Configuration

### Environment Variables

Typical keys (see `config/atu-rank-seo.php` after publishing):

```env
ATU_RANKSEO_ENABLED=true
ATU_RANKSEO_CACHE_TTL=3600
ATU_RANKSEO_ADMIN_ENABLED=true
```

When `ATU_RANKSEO_ADMIN_ENABLED` is `false`, the package does not register admin routes (useful if you register the same URLs yourself).

### Config File

Publish to `config/atu-rank-seo.php` with the tag above. You can customize:

- Global `enabled` and admin `enabled`, `middleware`, `prefix`
- Cache TTL and prefix
- `default_variables` — suggested defaults only (not wired into `SeoSnapshotService` placeholder resolution; use `dynamic_variables` in settings or pass values in `$data`)
- `media_directory` / `media_types` — documented intent for media handling; `MediaIndexerService::scanAndRegister()` currently scans **`public/media`** (see source if this changes)

## Usage

### Resolving SEO for a Page

```php
use Vormia\ATURankSEO\Services\SeoResolverService;

$seoResolver = app(SeoResolverService::class);
$seo = $seoResolver->forSlug('my-page-slug');

// Returns:
// [
//     'title' => 'Page Title',
//     'description' => 'Meta description',
//     'keywords' => 'keyword1, keyword2',
//     'canonical_url' => 'https://example.com/page',
//     'robots' => 'index, follow',
// ]
```

### Generating SEO Snapshot

```php
use Vormia\ATURankSEO\Services\SeoSnapshotService;

$snapshotService = app(SeoSnapshotService::class);
$snapshotService->generateForSlug('my-page-slug', [
    'title' => 'Buy {make} {model} {year}',
    'description' => 'Find the best {make} {model} deals',
    'make' => 'Toyota',
    'model' => 'Camry',
    'year' => '2024',
]);

// Placeholders are resolved and stored in database
```

### Media SEO

```php
use Vormia\ATURankSEO\Services\MediaIndexerService;

$mediaIndexer = app(MediaIndexerService::class);

// Scan public/media recursively and register new files (paths relative to public/, e.g. media/photo.jpg)
$mediaIndexer->scanAndRegister();

// Register a single media file
$mediaIndexer->registerMedia('media/images/product.jpg', [
    'title' => 'Product Image',
    'alt_text' => 'Product photo',
    'caption' => 'High-quality product image',
]);
```

## Admin Panel (Livewire 4)

The admin UI uses **Livewire 4 single-file components** (inline `new class extends Component` in each Blade file), similar to the Multicurrency package. Shipped views live under `resources/views/livewire/admin/atu/rank-seo/` in the package; `ATURankSEOServiceProvider` registers them via `Livewire::addLocation` and full-page `Route::livewire(...)`.

| Screen | Livewire component name | Blade path (package) |
| --- | --- | --- |
| SEO entries list | `rank-seo.index` | `resources/views/livewire/admin/atu/rank-seo/index.blade.php` |
| Global settings | `rank-seo.settings` | `.../settings.blade.php` |
| Edit page SEO | `rank-seo.edit` | `.../edit.blade.php` |
| Media list | `rank-seo.media-index` | `.../media-index.blade.php` |
| Edit media SEO | `rank-seo.media-edit` | `.../media-edit.blade.php` |

Toasts use the in-package concern `Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts` (not application-level notification traits).

### Routes (default)

When `atu-rank-seo.enabled` and `atu-rank-seo.admin.enabled` are true, `ATURankSEOServiceProvider` registers routes (default prefix `admin/atu`, names `admin.atu.rank-seo.*`) using `Route::livewire($uri, $componentName)`. You do **not** need to add a block to `routes/web.php` for the standard setup.

### Manual routes (optional)

If you disable package route registration (`ATU_RANKSEO_ADMIN_ENABLED=false` or `admin.enabled` false), register the same endpoints yourself. Copy from the package stub (adjust middleware/prefix to match your app):

`vendor/vormia-folks/atu-rank-seo/src/stubs/reference/routes-to-add.php`

The stub uses `Route::livewire(...)` with the five component name strings listed above. Ensure `Livewire::addLocation` points at the same view tree (package path or your published copy).

### Stub folders (reference vs copy)

These paths are under the package root (or `vendor/vormia-folks/atu-rank-seo/` when installed):

- **`src/stubs/reference/`** — Snippets only (routes, sidebar). The installer does **not** copy them; paste into your app when you need custom wiring.
- **`src/stubs/resources/views/livewire/admin/atu/rank-seo/`** — Mirror copies of the package Blade templates (same layout as other ATU packages such as Multicurrency). Use them as a starting point if you publish or merge views into your application for customization. Keep them in sync with `resources/views/livewire/admin/atu/rank-seo/` in this repository when contributing upstream.

### Sidebar (Flux)

For Flux-based sidebars, paste the snippet from:

`vendor/vormia-folks/atu-rank-seo/src/stubs/reference/sidebar-menu-to-add.blade.php`

## Commands

- `php artisan aturankseo:install` — Apply setup (env, optional migrate/seed)
- `php artisan aturankseo:update` — Re-apply env keys
- `php artisan aturankseo:uninstall` — Optional env removal, optional migration rollback, cache clears
- `php artisan aturankseo:help` — Show env keys and route summary

## Uninstallation

```bash
php artisan aturankseo:uninstall
```

### What the uninstall command does

1. Optionally removes ATU Rank SEO keys from `.env` / `.env.example` (with confirmation, unless `--force` / `--keep-env`)
2. Optionally rolls back package migrations (with confirmation; destructive to package tables)
3. Clears config, route, view, and application caches

It does **not** edit `routes/web.php` (routes are owned by the package while it remains installed) and does **not** delete optional files you copied from stubs.

### Options

- `--keep-env`: Preserve environment variables
- `--force`: Skip confirmation prompts

### After uninstall

1. Remove the Composer dependency if you no longer need the package:

   ```bash
   composer remove vormia-folks/atu-rank-seo
   ```

2. Remove any custom routes or sidebar links you added manually.

3. To reinstall: `composer require vormia-folks/atu-rank-seo` and `php artisan aturankseo:install`.

## Database Schema

### Tables

- `atu_rankseo_meta` — Page-level SEO metadata
- `atu_rankseo_media` — Media SEO metadata
- `atu_rankseo_settings` — Global SEO settings

## Placeholder Resolution

SEO string fields support `{placeholder}` tokens (letters, numbers, underscore). They are resolved **on save** inside `SeoSnapshotService`, not when reading cached SEO.

Typical examples:

- `{make}`, `{model}`, `{year}` — pass these keys in the `$data` array when calling `generateForSlug` / `generateForMedia`, or store them in **Global settings → dynamic variables** (`atu_rankseo_settings.dynamic_variables` JSON).
- `{current_year}`, `{current_month}`, `{current_date}` — always set from the server date at resolution time (they override the same keys if present in merged variables).

Merge behavior in code: `dynamic_variables` from settings are merged first, then the snapshot `$data` array (so **per-call data wins** on duplicate keys), then `current_year` / `current_month` / `current_date` are applied.

`config('atu-rank-seo.default_variables')` defines suggested defaults (for example `site_name`) but is **not** automatically merged into placeholder resolution today; put `site_name` (and similar) in `dynamic_variables` or pass them in `$data` when generating snapshots.

## Caching

SEO data is cached for performance. `SeoCacheService` builds keys from `config('atu-rank-seo.cache.prefix')` (default `atu_rankseo`) and TTL from `config('atu-rank-seo.cache.ttl')` / `ATU_RANKSEO_CACHE_TTL`:

- Page SEO: `{prefix}:slug:{slug_registry_id}:{type}`
- Media SEO: `{prefix}:media:{md5(media_url)}`

Cache entries are invalidated when matching SEO rows are written or removed via the snapshot, resolver, and media indexer services (for example after save, soft delete / deactivate, or media registration).

`SeoCacheService::clearAll()` calls `Cache::flush()` (entire application cache); use with care.

## Requirements

- PHP ^8.2
- Laravel ^12.0 or ^13.0
- `livewire/livewire` ^4.0
- `vormiaphp/vormia` ^5.4
- `a2-atu/a2commerce` ^0.2.0

## License

MIT

## Support

Developer guide: [docs/atu-rank-seo.md](docs/atu-rank-seo.md). Framework references: [Laravel documentation](https://laravel.com/docs), [Livewire 4](https://livewire.laravel.com/docs). For issues and questions, use the package repository.
