# ATU Rank SEO — Developer Guide

A companion SEO package for the Vormia ecosystem. ATU Rank SEO provides centralized, snapshot-based SEO management tightly integrated with Vormia's `SlugRegistry`, enabling page-level and media-level SEO similar in spirit to Yoast SEO (WordPress), but designed for Laravel applications.

This guide combines product design, database shape, **service APIs**, **admin UI** expectations, and **Livewire 4** integration notes.

## Table of contents

1. [Design goals](#1-design-goals)
2. [Package scope](#2-package-scope)
3. [Dependency: SlugRegistry](#3-dependency-slugregistry)
4. [Snapshot SEO resolution](#4-snapshot-seo-resolution)
5. [Conflict resolution order](#5-conflict-resolution-order)
6. [Caching strategy](#6-caching-strategy)
7. [Database schema](#7-database-schema)
8. [Service layer (APIs)](#8-service-layer-apis)
9. [Admin UI and Livewire 4](#9-admin-ui-and-livewire-4)
10. [Events and hooks](#10-events-and-hooks)
11. [Extension points (future)](#11-extension-points-future)
12. [Non-goals](#12-non-goals)
13. [Summary](#13-summary)

---

## 1. Design goals

- Snapshot-based SEO (resolved on save, not runtime)
- Slug-driven (integrates with `vrm_slug_registry` via FK; package does not alter that table’s schema — see [Section 3](#3-dependency-slugregistry) for `firstOrCreate` on snapshot save)
- Page and media SEO support
- Cache-first for performance
- UI-driven management (enable/disable, global defaults, per-page overrides)
- Clean separation of concerns (services over ad hoc traits in app code)

---

## 2. Package scope

### Supported SEO controls

- Page title
- Meta description
- Meta keywords
- Canonical URL
- Robots meta
- Open Graph (future)
- Twitter cards (future)

### Media SEO

- Image alt text, image title, caption, media metadata

Media SEO affects rendering of media elements (for example `<img>` tags), not necessarily the HTML document `<head>`.

---

## 3. Dependency: SlugRegistry

The package **does not change** the SlugRegistry schema or own slug lifecycle policy; it stores `slug_registry_id` on SEO rows as a nullable reference.

- `slug_registry_id` (nullable)
- Used primarily for `type = page`
- Media entries may use `slug_registry_id = null`

**Implementation note:** `SeoSnapshotService::generateForSlug()` resolves the slug model class from `config('vormia.models.slug_registry')` and uses `firstOrCreate(['slug' => $slug], …)` so a registry row may be **created** if one does not exist yet. `SeoResolverService::forSlug()` only **reads** by slug. If your app must never auto-create registry rows, call snapshot APIs only after the slug exists, or extend the service in your application.

---

## 4. Snapshot SEO resolution

### Placeholder system

SEO string fields may contain `{placeholder}` tokens (regex: `\{([a-zA-Z0-9_]+)\}` in `SeoSnapshotService`). Example:

```
Buy {make} {model} {year} in Kenya
```

### Resolution rules

- Placeholders are resolved **on save** inside `SeoSnapshotService::resolvePlaceholders()`
- Variable map: `dynamic_variables` from `atu_rankseo_settings`, merged with the `$data` array passed into the snapshot method (**`$data` wins** on duplicate keys), then `current_year`, `current_month`, and `current_date` are set from the server clock (overriding those keys if present)
- `config('atu-rank-seo.default_variables')` is **not** merged automatically; use settings JSON or pass keys in `$data`
- Final resolved strings are stored in the database
- No placeholder parsing at runtime for stored snapshots

---

## 5. Conflict resolution order

When resolving SEO for a page:

1. Page-specific SEO (highest priority)
2. Global SEO (merged or ignored based on page setting)
3. Media SEO (applies only to media elements)

---

## 6. Caching strategy

SEO data is **cache-first**.

### Cache rules

- Cache the resolved SEO payload per slug (and separately for media where applicable)
- Invalidate when page SEO, media SEO, or activation state changes, or when slugs change

### Example cache keys

Implementation uses a configurable prefix (see `config('atu-rank-seo.cache.prefix')`). Illustrative shapes:

- Page SEO: `{prefix}:slug:{slug_registry_id}:{type}`
- Media SEO: `{prefix}:media:{hash(media_url)}`

TTL is controlled via `config('atu-rank-seo.cache.ttl')` / `ATU_RANKSEO_CACHE_TTL`.

---

## 7. Database schema

Authoritative columns are defined in package migrations under `database/migrations/`. The tables below are a conceptual summary.

### 7.1 `atu_rankseo_meta`

Page-level SEO metadata.

| Column            | Notes                                        |
| ----------------- | -------------------------------------------- |
| id                | Primary key                                  |
| slug_registry_id  | Nullable FK-style reference to slug registry |
| type              | Enum: currently **`page` only** (migration)  |
| title, description, keywords, canonical_url, robots | Snapshot fields           |
| use_global        | Whether to merge global SEO                  |
| is_active         | Enable/disable                               |
| timestamps        |                                              |

Service-level uniqueness: one active SEO record per `(slug_registry_id, type = page)` where applicable.

### 7.2 `atu_rankseo_media`

| Column            | Notes                    |
| ----------------- | ------------------------ |
| media_url         | Unique media path        |
| media_type        | image / file             |
| title, alt_text, caption, metadata |           |
| slug_registry_id  | Optional                 |
| is_active         |                          |

### 7.3 `atu_rankseo_settings`

Global defaults and master switch (`is_enabled`), plus `dynamic_variables` (JSON key/value map).

The migration defines **`updated_at` only** (no `created_at`); the `RankSeoSettings` model sets `$timestamps = false` and uses `getInstance()` to ensure a singleton row exists.

---

## 8. Service layer (APIs)

Service classes are registered on the container and intended for constructor injection.

### 8.1 `SeoResolverService`

**Purpose:** Retrieve resolved SEO for a slug, page, or media.

```php
class SeoResolverService
{
    /** Priority: page > global > media. Returns ['title' => '', ...]. */
    public function forSlug(string $slug): array;

    public function forSlugRegistry(int $slugRegistryId, string $type = 'page'): array;

    public function forMedia(string $mediaUrl): array;
}
```

**Behavior:** Cache first, then database; returns a payload suitable for `<head>` rendering.

### 8.2 `SeoSnapshotService`

**Purpose:** Generate and persist snapshot SEO on save.

```php
class SeoSnapshotService
{
    public function generateForSlug(string $slug, array $data): void;

    public function generateForMedia(string $mediaUrl, array $data): void;

    public function updateSeo(int $seoId, array $data): void;

    /** `$hardDelete` false (default) sets `is_active` to false; true deletes the row. */
    public function deleteSeo(int $seoId, bool $hardDelete = false): void;
}
```

**Behavior:** Resolves placeholders, writes `atu_rankseo_meta` / `atu_rankseo_media`, triggers cache invalidation.

### 8.3 `MediaIndexerService`

**Purpose:** Scan and index media for SEO rows.

```php
class MediaIndexerService
{
    /** Recursively scans `public/media` and registers files not yet in `atu_rankseo_media`. */
    public function scanAndRegister(): void;

    public function registerMedia(string $mediaPath, array $metadata = []): void;

    /** Soft-deletes by default (`is_active = false`) unless `$hardDelete` is true. */
    public function deleteMedia(string $mediaUrl, bool $hardDelete = false): void;
}
```

### 8.4 `SeoCacheService`

**Purpose:** Put, get, invalidate, and build standard keys.

```php
class SeoCacheService
{
    /** TTL defaults to config('atu-rank-seo.cache.ttl') when null. */
    public function put(string $cacheKey, array $seoPayload, ?int $ttl = null): void;

    public function get(string $cacheKey): ?array;

    public function invalidate(string $cacheKey): void;

    public function keyForSlug(int $slugRegistryId, string $type = 'page'): string;

    public function keyForMedia(string $mediaUrl): string;

    /** Calls Cache::flush() — clears the entire app cache, not only Rank SEO keys. */
    public function clearAll(): void;
}
```

---

## 9. Admin UI and Livewire 4

### 9.1 Product goals (Yoast-like)

- Master enable/disable (package / settings)
- Global SEO editor and dynamic variables (`{site_name}`, `{current_year}`, etc.)
- SEO entries table: list, filter, activate/deactivate, edit, delete
- Media SEO manager: list, edit metadata, activate/deactivate, delete; optional image preview
- Page SEO form: title, description, keywords, canonical, robots, use-global toggle, placeholder hints
- Optional: cache status and manual clear (future enhancement)

### 9.2 Implementation (shipped package)

Admin screens are **Livewire 4 single-file components** (PHP `new class extends Component` at the top of each Blade file under `src/stubs/resources/views/livewire/admin/atu/rank-seo/`), aligned with the Multicurrency ATU pattern—not Volt.

- **Routes:** Either appended to the host `routes/web.php` by `php artisan aturankseo:install` (default), with `ATU_RANKSEO_ADMIN_ENABLED=false`, **or** registered in `ATURankSEOServiceProvider` when `config('atu-rank-seo.enabled')` and `config('atu-rank-seo.admin.enabled')` are true and `ATU_RANKSEO_ADMIN_ENABLED` is true (for example after `aturankseo:install --skip-host-copy`). Uses `Route::livewire($uri, 'rank-seo.*')` with string component names (required for SFC full-page components in Livewire 4).
- **Discovery:** `Livewire::addLocation` registers the host `resources/views/livewire/admin/atu` directory first when it exists, then the package `src/stubs/resources/views/livewire/admin/atu` tree, so copied blades override vendor.
- **Toasts:** Admin components use `Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts`. They do **not** depend on `App\Traits\Vrm\Livewire\WithNotifications` (legacy). If another feature in the host app uses Vormia’s Livewire trait, that is `Vormia\Vormia\Traits\Livewire\WithNotifications` in current Vormia packages—not the same as Rank SEO’s toasts, which stay self-contained for this admin UI.

### 9.3 Stub folders (reference vs copy)

Under the package repository (and under `vendor/.../atu-rank-seo/` when installed):

| Path | Role |
| ---- | ---- |
| `src/stubs/reference/routes-to-add.php` | Commented example for **manual** route registration; default install generates an equivalent block in `routes/web.php` (see `Installer::WEB_ROUTES_MARKER_*` in the package). |
| `src/stubs/reference/sidebar-menu-to-add.blade.php` | Flux `flux:navlist.item` sidebar snippet (Multicurrency-style). |
| `src/stubs/resources/views/livewire/admin/atu/rank-seo/*.blade.php` | **Shipped** Livewire admin blades (Multicurrency-style; single location in-repo — no duplicate `resources/views/` tree at package root). |

`php artisan aturankseo:install` (by default) copies shipped admin Blade files from `src/stubs/resources/views/...` into the host app and appends a marked Livewire route block to `routes/web.php`; it does **not** copy the `src/stubs/reference/` files themselves. Use `--skip-host-copy` for package-only routes and views.

### 9.4 Example folder

`example-package/rank-seo/*.blade.php` holds **view-only** examples aligned with the same templates as `src/stubs/resources/views/livewire/admin/atu/rank-seo/` (without the SFC PHP block) for side-by-side comparison with other example packages in the repo.

---

## 10. Events and hooks

The package defines Laravel events under `Vormia\ATURankSEO\Events\`:

- `SeoSnapshotGenerated` — dispatched from `SeoSnapshotService` when page SEO snapshots are saved.
- `MediaIndexed` — dispatched from `MediaIndexerService` and `SeoSnapshotService` when media rows are indexed or updated.
- `SeoCacheInvalidated` — class exists under `src/Events/` but is **not** dispatched anywhere yet; search the codebase for `SeoCacheInvalidated` before relying on it.

Inspect `src/Events/` for payloads and properties.

---

## 11. Extension points (future)

- Multilingual SEO, multi-site / tenant support
- Open Graph and Twitter cards, SEO analysis hints
- Redirect / 301 management
- Scheduled media re-index and cache warmup

---

## 12. Non-goals

- No runtime placeholder parsing for stored snapshots
- No ownership of SlugRegistry table migrations or core slug rules (see [Section 3](#3-dependency-slugregistry) for `firstOrCreate` behavior on snapshot save)
- No responsibility for public frontend layout beyond SEO data contracts

---

## 13. Summary

ATU Rank SEO is a snapshot-based, slug-aware SEO engine that integrates with Vormia’s architecture, prioritizes performance and clarity, and ships a **Livewire 4** admin UI with host-friendly install defaults (copied views + `web.php` routes) or package-only routing when you pass `--skip-host-copy`.

For install commands and env keys, see the repository [README.md](../README.md). Laravel: [laravel.com/docs](https://laravel.com/docs). Livewire: [livewire.laravel.com/docs](https://livewire.laravel.com/docs).
