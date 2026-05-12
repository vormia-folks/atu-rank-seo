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
- **By default**, copy admin Livewire Blade files from the package (`src/stubs/resources/views/livewire/admin/atu/rank-seo/`, Multicurrency-style) into your app’s `resources/views/livewire/admin/atu/rank-seo/`, append a marked Livewire route group to `routes/web.php`, then set `ATU_RANKSEO_ADMIN_ENABLED=false` so the host owns admin URLs (the package stops registering the same routes). Use `--skip-host-copy` to keep routes and views package-only (previous “package-first” behavior). Use `--force` to overwrite existing copied Blade files.
- Optionally run `php artisan migrate` (interactive confirmation; default **yes**)
- Optionally run the package seeder (only after migrations succeed; interactive confirmation; default **yes** — you can answer **no** to skip seeding)

Migrations always load from the package via `loadMigrationsFrom` (they are not copied). When host views exist under your app’s `resources/views/livewire/admin/atu/`, `ATURankSEOServiceProvider` registers that path with `Livewire::addLocation` **before** the package stubs path so your copies override vendor. Optional config publish:

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

When `ATU_RANKSEO_ADMIN_ENABLED` is `false`, the package does not register admin Livewire routes (the default after `aturankseo:install`, because routes are appended to your `routes/web.php`). Set it to `true` only if you removed the host route block and want the package to register admin URLs again.

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

The admin UI uses **Livewire 4 single-file components** (inline `new class extends Component` in each Blade file), aligned with the Multicurrency package. Shipped views live only under **`src/stubs/resources/views/livewire/admin/atu/rank-seo/`** in this repository (there is no duplicate `resources/views/` tree at the package root). After `aturankseo:install` (default), those files are copied into your app; `ATURankSEOServiceProvider` registers `Livewire::addLocation` for your app’s `resources/views/livewire/admin/atu` directory first when it exists, then the package stubs tree, and routes are typically registered from your `routes/web.php` with `ATU_RANKSEO_ADMIN_ENABLED=false`.

| Screen | Livewire component name | Blade path (in package) |
| --- | --- | --- |
| SEO entries list | `rank-seo.index` | `src/stubs/resources/views/livewire/admin/atu/rank-seo/index.blade.php` |
| Global settings | `rank-seo.settings` | `.../settings.blade.php` |
| Edit page SEO | `rank-seo.edit` | `.../edit.blade.php` |
| Media list | `rank-seo.media-index` | `.../media-index.blade.php` |
| Edit media SEO | `rank-seo.media-edit` | `.../media-edit.blade.php` |

Toasts use the in-package concern `Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts` (not application-level notification traits).

### Routes (default)

After **`php artisan aturankseo:install`** (without `--skip-host-copy`), a marked Livewire route group is appended to `routes/web.php` and `ATU_RANKSEO_ADMIN_ENABLED` is set to `false`, so the **host** registers the admin URLs. When `atu-rank-seo.enabled` is true and `atu-rank-seo.admin.enabled` is true **and** the package is allowed to register routes (`ATU_RANKSEO_ADMIN_ENABLED` true, for example after `aturankseo:install --skip-host-copy`), `ATURankSEOServiceProvider` registers the same endpoints (default prefix `admin/atu`, names `admin.atu.rank-seo.*`) using `Route::livewire($uri, $componentName)`.

### Manual routes (optional)

If you disable package route registration (`ATU_RANKSEO_ADMIN_ENABLED=false` or `admin.enabled` false) and you do not rely on the install-generated `web.php` block, register the same endpoints yourself. The installer writes a block delimited by `// >>> ATU Rank SEO start` … `// <<< ATU Rank SEO end` (see `Vormia\ATURankSEO\Support\Installer`). For a paste-ready variant without the generated middleware wrapper, use:

`vendor/vormia-folks/atu-rank-seo/src/stubs/reference/routes-to-add.php`

That reference uses `// >>> ATU Rank SEO Web Routes START` … `// <<< ATU Rank SEO Web Routes END`; adjust middleware and prefix to match your app if you merge it manually.

### Stub folders (reference vs shipped views)

These paths are under the package root (or `vendor/vormia-folks/atu-rank-seo/` when installed):

- **`src/stubs/reference/`** — Snippets only (routes, sidebar). `aturankseo:install` appends routes programmatically; use these files when you need to paste or compare custom wiring.
- **`src/stubs/resources/views/livewire/admin/atu/rank-seo/`** — **Canonical** shipped Livewire admin blades (same layout as Multicurrency under `src/stubs/resources/...`). `aturankseo:install` copies from here into your application when you do not pass `--skip-host-copy`.

### Sidebar (Flux)

The reference file contains **two** snippets: `flux:navlist.item` (admin panel / navlist) and `flux:sidebar.item` (inject inside `flux:sidebar.group`, e.g. Platform, before `</flux:sidebar.group>`). Paste the section that matches your layout:

`vendor/vormia-folks/atu-rank-seo/src/stubs/reference/sidebar-menu-to-add.blade.php`

## Commands

- `php artisan aturankseo:install` — Env keys, copy admin views + append `routes/web.php` by default (`--skip-host-copy`, `--skip-env`, `--force`)
- `php artisan aturankseo:update` — Re-apply env keys
- `php artisan aturankseo:uninstall` — Optional env removal, optional host `web.php` + copied views cleanup, optional migration rollback, cache clears (`--keep-env`, `--keep-host-files`, `--force`)
- `php artisan aturankseo:help` — Show env keys and route summary

## Uninstallation

```bash
php artisan aturankseo:uninstall
```

### What the uninstall command does

1. Optionally removes the ATU Rank SEO block from `routes/web.php` (markers `// >>> ATU Rank SEO start` … `// <<< ATU Rank SEO end`) and deletes copied Livewire blades under `resources/views/livewire/admin/atu/rank-seo/` (with confirmation, unless `--force`; skipped with `--keep-host-files` or if you decline the prompt). When the route block is removed, `ATU_RANKSEO_ADMIN_ENABLED` is set to `true` in `.env` / `.env.example` (if those files exist) so the package can register admin routes again—**before** optional full env key removal, so a combined “remove everything” run does not leave that key behind.
2. Optionally removes ATU Rank SEO keys from `.env` / `.env.example` (with confirmation, unless `--force` / `--keep-env`)
3. Optionally rolls back package migrations (with confirmation; destructive to package tables)
4. Clears config, route, view, and application caches

### Options

- `--keep-env`: Preserve environment variables
- `--keep-host-files`: Do not remove the injected `web.php` block or copied `rank-seo` Blade files
- `--force`: Skip confirmation prompts (host routes/views are removed unless `--keep-host-files`)

### After uninstall

1. Remove the Composer dependency if you no longer need the package:

   ```bash
   composer remove vormia-folks/atu-rank-seo
   ```

2. Remove any custom sidebar links you added from the reference snippets (if applicable).

3. If you used `--keep-host-files` or skipped host cleanup, remove the ATU Rank SEO route block and copied views manually when you no longer need them.

4. To reinstall: `composer require vormia-folks/atu-rank-seo` and `php artisan aturankseo:install`.

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
