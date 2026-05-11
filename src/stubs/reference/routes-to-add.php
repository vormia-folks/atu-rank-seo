<?php

/**
 * REFERENCE ONLY — not copied or loaded by the installer.
 *
 * By default, ATU Rank SEO registers admin Livewire routes from the service
 * provider when `config('atu-rank-seo.enabled')` and `config('atu-rank-seo.admin.enabled')`
 * are true (see `ATU_RANKSEO_ADMIN_ENABLED` in .env).
 *
 * Use this file only if you disabled package routes and want to register the
 * same endpoints yourself (custom middleware stack, duplicate names, etc.).
 *
 * Livewire 4: full-page components are route targets (see Laravel routing docs
 * and https://livewire.laravel.com/docs ).
 */

use Illuminate\Support\Facades\Route;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Edit;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Index;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\MediaEdit;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\MediaIndex;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Settings;

// Example: register manually (set ATU_RANKSEO_ADMIN_ENABLED=false first to avoid duplicates).
// Route::middleware(['web', 'auth'])->prefix('admin/atu')->name('admin.atu.rank-seo.')->group(function () {
//     Route::get('/rank-seo', Index::class)->name('index');
//     Route::get('/rank-seo/settings', Settings::class)->name('settings');
//     Route::get('/rank-seo/edit/{id}', Edit::class)->name('edit');
//     Route::get('/rank-seo/media', MediaIndex::class)->name('media.index');
//     Route::get('/rank-seo/media/edit/{id}', MediaEdit::class)->name('media.edit');
// });
