# ATU Rank SEO

A companion SEO package for the Vormia ecosystem. ATU-Rank-SEO provides centralized, snapshot-based SEO management tightly integrated with Vormia's `SlugRegistry`, enabling page-level and media-level SEO similar in spirit to Yoast SEO (WordPress), but designed for Laravel applications.

## Features

- **Snapshot-based SEO**: Resolved on save, not runtime
- **Slug-driven**: Integrates with `vrm_slug_registry` without modifying it
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

- Copy configuration files
- Add environment variables
- Add routes (commented out by default)
- Run migrations (with confirmation)
- Run seeders (with confirmation)

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
ATU_RANKSEO_ENABLED=true
ATU_RANKSEO_CACHE_TTL=3600
```

### Config File

The configuration file is published to `config/atu-rank-seo.php`. You can customize:

- Cache TTL
- Default placeholder variables
- Media directory path
- Supported media types

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

// Scan and register all media files
$mediaIndexer->scanAndRegister();

// Register a single media file
$mediaIndexer->registerMedia('media/images/product.jpg', [
    'title' => 'Product Image',
    'alt_text' => 'Product photo',
    'caption' => 'High-quality product image',
]);
```

## Admin Panel

The package includes Livewire Volt components for managing SEO:

- **SEO Entries List**: View and manage all SEO entries
- **Global Settings**: Configure global SEO defaults and dynamic variables
- **Edit SEO Entry**: Edit page-specific SEO
- **Media SEO Manager**: Manage media SEO entries
- **Edit Media SEO**: Edit media-specific SEO

### Routes

Routes are added to `routes/web.php` (commented out by default). Uncomment and customize as needed:

```php
use Livewire\Volt\Volt;

Route::group(['prefix' => 'admin/atu'], function () {
    Volt::route('rank-seo', 'admin.atu.rank-seo.index')->name('admin.atu.rank-seo.index');
    // ... other routes
});
```

### Manual Route Setup

If automatic route injection fails, manually add the following routes to `routes/web.php` inside the `Route::middleware(['auth'])->group(function () { ... })` block:

```php
Route::prefix('admin/atu/rank-seo')->name('admin.atu.rank-seo.')->group(function () {
    Volt::route('index', 'admin.atu.rank-seo.index')->name('index');
    Volt::route('settings', 'admin.atu.rank-seo.settings')->name('settings');
    Volt::route('edit/{id}', 'admin.atu.rank-seo.edit')->name('edit');
    Volt::route('media', 'admin.atu.rank-seo.media-index')->name('media.index');
    Volt::route('media/edit/{id}', 'admin.atu.rank-seo.media-edit')->name('media.edit');
});
```

**Note:** If you have configured your own starterkit, you may need to add `use Livewire\Volt\Volt;` at the top of your routes file.

### Manual Sidebar Menu Setup

If automatic sidebar menu injection fails, manually add the following menu items to your admin sidebar (usually in `resources/views/components/layouts/app/sidebar.blade.php` or similar):

```blade
@if (auth()->user()?->isAdminOrSuperAdmin())
    <hr />

    {{-- SEO Entries Menu Item --}}
    <flux:navlist.item icon="magnifying-glass" :href="route('admin.atu.rank-seo.index')"
        :current="request()->routeIs('admin.atu.rank-seo.index') || request()->routeIs('admin.atu.rank-seo.edit')" wire:navigate>
        {{ __('SEO Entries') }}
    </flux:navlist.item>

    {{-- Media SEO Menu Item --}}
    <flux:navlist.item icon="photo" :href="route('admin.atu.rank-seo.media.index')"
        :current="request()->routeIs('admin.atu.rank-seo.media.*')" wire:navigate>
        {{ __('Media SEO') }}
    </flux:navlist.item>

    {{-- Global Settings Menu Item --}}
    <flux:navlist.item icon="cog-6-tooth" :href="route('admin.atu.rank-seo.settings')"
        :current="request()->routeIs('admin.atu.rank-seo.settings')" wire:navigate>
        {{ __('SEO Settings') }}
    </flux:navlist.item>
@endif
```

**Reference Files:**

- Routes: `vendor/vormiaphp/atu-rank-seo/src/stubs/reference/routes-to-add.php`
- Sidebar Menu: `vendor/vormiaphp/atu-rank-seo/src/stubs/reference/sidebar-menu-to-add.blade.php`

## Commands

- `php artisan aturankseo:install` - Install the package
- `php artisan aturankseo:update` - Update package files
- `php artisan aturankseo:uninstall` - Uninstall the package
- `php artisan aturankseo:help` - Display help information

## Uninstallation

To uninstall ATU Rank SEO from your application:

```bash
php artisan aturankseo:uninstall
```

### Uninstallation Process

The uninstall command will:

1. **Remove Package Files**: Delete all copied files and stubs from your application
2. **Remove Routes**: Remove SEO routes from `routes/web.php`
3. **Clean Environment Files**: Optionally remove environment variables from `.env` and `.env.example` (with confirmation)
4. **Clear Application Caches**: Automatically refresh/clear all Laravel caches:
   - Configuration cache (`config:clear`)
   - Route cache (`route:clear`)
   - View cache (`view:clear`)
   - Application cache (`cache:clear`)

### Uninstallation Options

- `--keep-env`: Preserve environment variables in `.env` files
- `--force`: Skip confirmation prompts

**Examples:**

```bash
# Standard uninstall with prompts
php artisan aturankseo:uninstall

# Uninstall but keep environment variables
php artisan aturankseo:uninstall --keep-env

# Force uninstall without prompts
php artisan aturankseo:uninstall --force
```

### After Uninstallation

After running the uninstall command, you'll need to:

1. Remove the package from `composer.json`:

   ```bash
   composer remove vormia-folks/atu-rank-seo
   ```

2. Review your application for any remaining ATU Rank SEO references in your code

3. If you want to reinstall the package later, simply run:
   ```bash
   composer require vormia-folks/atu-rank-seo
   php artisan aturankseo:install
   ```

## Database Schema

### Tables

- `atu_rankseo_meta` - Page-level SEO metadata
- `atu_rankseo_media` - Media SEO metadata
- `atu_rankseo_settings` - Global SEO settings

## Placeholder Resolution

SEO fields support placeholders that are resolved on save:

- `{make}` - Vehicle make
- `{model}` - Vehicle model
- `{year}` - Year
- `{site_name}` - Site name (from config or settings)
- `{current_year}` - Current year
- `{current_month}` - Current month name
- `{current_date}` - Current date

Placeholders are resolved using:

1. Data provided when generating the snapshot
2. Global SEO variables from settings
3. Built-in variables (current_year, etc.)

## Caching

SEO data is cached for performance. Cache keys follow the pattern:

- Page SEO: `atu_rankseo:slug:{slug_registry_id}:{type}`
- Media SEO: `atu_rankseo:media:{md5(media_url)}`

Cache is automatically invalidated when SEO entries are updated, deleted, or activated/deactivated.

## Requirements

- PHP ^8.2
- Laravel ^12.0
- vormiaphp/vormia ^4.4
- a2-atu/a2commerce ^0.1.6
- livewire/livewire (for admin panel)

## License

MIT

## Support

For issues and questions, please refer to the package documentation or create an issue in the repository.
