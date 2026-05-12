# Release Notes

## [v1.3.1] - 2026-05-12

### Documentation and stubs

- **README**: Aligned with Livewire 4 package-first routing (no Volt), correct install/uninstall behavior, env keys (`ATU_RANKSEO_ADMIN_ENABLED`), vendor paths (`vormia-folks/atu-rank-seo`), and requirements from `composer.json`. Added stub-folder overview and admin component/view table.
- **Stubs**: Single Flux sidebar reference at `src/stubs/reference/sidebar-menu-to-add.blade.php` (Multicurrency-style). Added `src/stubs/resources/views/livewire/admin/atu/rank-seo/` as Multicurrency-style **mirror copies** of shipped Blade templates (with stub headers).
- **Examples**: Replaced legacy Volt-style `example-package/rank-seo/*.blade.php` files with the same templates as `resources/views/livewire/admin/atu/rank-seo/` (admin UI uses Livewire single-file components in those blades).
- **Docs**: Merged `docs/atu-rank-seo-ui.md` into a single [docs/atu-rank-seo.md](docs/atu-rank-seo.md) (TOC, service APIs, admin UI, Livewire 4, stubs, Vormia `WithNotifications` vs `WithRankSeoToasts`, events). Removed `docs/atu-rank-seo-ui.md`.
- **Installer message**: Completion step mentions the sidebar reference stub.

---

## [v1.3.0] - 2026-05-11

### Breaking changes

- **Package-first install**: The installer no longer copies migrations, views, or config into the host app. Migrations load via `loadMigrationsFrom`; admin UI views load via `loadViewsFrom`; optional `php artisan vendor:publish --tag=aturankseo-config` publishes config only.
- **Routes**: Admin routes are registered by `ATURankSEOServiceProvider` (Livewire 4 full-page component classes). The old commented block injected into `routes/web.php` is removed. Delete any leftover `// >>> ATU Rank SEO Routes` block from a previous install if it is still present.
- **Volt removed**: Admin screens use `Livewire\Component` PHP classes under `Vormia\ATURankSEO\Livewire\...` plus Blade views; `livewire/livewire` `^4.0` is now a required dependency.
- **Notifications**: `App\Traits\Vrm\Livewire\WithNotifications` is replaced by an in-package toast helper (`WithRankSeoToasts`).
- **Seeder**: Run `php artisan db:seed --class=Vormia\ATURankSEO\Database\Seeders\ATURankSEOSeeder` (class lives in the package).
- **Config keys**: Cache settings are nested: `atu-rank-seo.cache.ttl` and `atu-rank-seo.cache.prefix`. New env: `ATU_RANKSEO_ADMIN_ENABLED` (disables route registration when false).
- **Uninstall**: No longer deletes copied stub files or strips `web.php`; it optionally removes env keys and runs `migrate:rollback` with `--path` pointed at the package migration directory when the package lives under the application base path.

### Improvements

- Aligns install/uninstall flow with the package-first pattern used by A2Commerce-style packages.

---

## [v1.2.1] - 2025-01-XX

### Bug Fixes
- **Fixed SlugRegistry Auto-Creation**: Fixed issue where SEO data was not being saved when the SlugRegistry entry didn't exist. The `SeoSnapshotService::generateForSlug()` method now automatically creates a SlugRegistry entry if it doesn't exist, ensuring SEO data is always saved successfully
- **Corrected Vormia Namespace**: Updated all SlugRegistry model references to use the correct Vormia namespace `App\Models\Vrm\SlugRegistry` instead of `App\Models\SlugRegistry`. This affects:
  - `SeoSnapshotService` (getSlugRegistryBySlug and getOrCreateSlugRegistryBySlug methods)
  - `SeoResolverService` (getSlugRegistryBySlug method)
  - `RankSeoMeta` model (slugRegistry relationship)
  - `RankSeoMedia` model (slugRegistry relationship)
  - Configuration file default values

### Improvements
- **Enhanced Error Handling**: Improved error logging when SlugRegistry operations fail, with more descriptive error messages
- **Better Code Consistency**: All SlugRegistry references now consistently use the Vormia/Vrm namespace pattern

---

## [v1.2.0] - 2024-12-XX

### Improvements
- **Refactored SEO Entries Blade Template**: Streamlined search and filter section, updated table structure, and ensured proper indentation and formatting throughout the file for improved readability and consistency
- **Volt Components Integration**: Refactored SEO management route definitions to utilize Volt components for improved organization and clarity, aligning with recent updates in the project structure
- **Enhanced Dark Mode Support**: Improved UI consistency across SEO management views by updating styles and classes in Blade templates. Adjusted button styles, form containers, and text colors to ensure better visibility and accessibility in dark mode
- **Improved Route Organization**: Refactored route definitions in README.md and Installer.php for SEO management to use a more organized structure with prefix and name grouping, enhancing clarity for developers
- **Better Navigation Structure**: Updated README.md to rename SEO Management to SEO Entries, and added separate menu items for Media SEO and Global Settings for improved clarity and organization in the admin panel navigation
- **Enhanced User Experience**: Refactored SEO management views to utilize Volt components, implement validation attributes, and enhance user notifications for actions such as saving, activating, and deleting entries. Updated form structures for better usability and consistency across the admin panel
- **Documentation Updates**: Refactored README.md to clarify installation steps, update route definitions to use Volt components, and enhance uninstallation command documentation

### Technical Changes
- Updated route definitions to use Volt components instead of traditional Livewire components
- Improved form validation attributes across all SEO management views
- Enhanced error handling and user feedback mechanisms
- Better code organization and consistency in Blade templates

---

## [v1.1.0] - Previous Release

### Features
- Initial stable release with core SEO management functionality
- Page-level and media-level SEO support
- Snapshot-based SEO resolution
- Admin panel integration with Livewire
- Global SEO settings management
- Placeholder support for dynamic SEO content
- Cache-first performance optimization

---

## [v1.0.0] - Initial Release

### Features
- Initial release of ATU Rank SEO package
- Basic SEO management functionality
- Integration with Vormia SlugRegistry
- Admin panel UI components
- Database migrations and seeders
- Installation and uninstallation commands
