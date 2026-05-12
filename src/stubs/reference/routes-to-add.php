<?php

/**
 * REFERENCE ONLY — not executed by the package. `php artisan aturankseo:install`
 * generates an equivalent block in your app's routes/web.php (see markers
 * `// >>> ATU Rank SEO start` / `// <<< ATU Rank SEO end` in Installer).
 *
 * By default, ATU Rank SEO registers admin Livewire routes from the service
 * provider when `config('atu-rank-seo.enabled')` and `config('atu-rank-seo.admin.enabled')`
 * are true (see `ATU_RANKSEO_ADMIN_ENABLED` in .env).
 *
 * Use this file only if you disabled package routes and want to register the
 * same endpoints yourself (custom middleware stack, duplicate names, etc.).
 *
 * Admin UI uses Livewire 4 **single-file components** (PHP + Blade in one file)
 * under `resources/views/livewire/admin/atu/rank-seo/` in the package. The
 * service provider calls `Livewire::addLocation` for that tree; replicate that
 * registration if you copy views into your app.
 *
 * @see https://livewire.laravel.com/docs
 */

use Illuminate\Support\Facades\Route;

// Example: register manually (set ATU_RANKSEO_ADMIN_ENABLED=false first to avoid duplicates).
// Route::middleware(['web', 'auth'])->prefix('admin/atu')->name('admin.atu.rank-seo.')->group(function () {
//     Route::livewire('/rank-seo', 'rank-seo.index')->name('index');
//     Route::livewire('/rank-seo/settings', 'rank-seo.settings')->name('settings');
//     Route::livewire('/rank-seo/edit/{id}', 'rank-seo.edit')->name('edit');
//     Route::livewire('/rank-seo/media', 'rank-seo.media-index')->name('media.index');
//     Route::livewire('/rank-seo/media/edit/{id}', 'rank-seo.media-edit')->name('media.edit');
// });
