<?php

/**
 * ATU Rank SEO Routes
 *
 * These routes are for the admin panel SEO management interface.
 * Uncomment and customize as needed for your application.
 *
 * Make sure you have Livewire installed and configured:
 * composer require livewire/livewire
 */

use Illuminate\Support\Facades\Route;

// >>> ATU Rank SEO Routes START
Route::prefix('admin/atu/rank-seo')->middleware(['web', 'auth'])->group(function () {
    // SEO entries list (Volt component)
    Route::get('/', 'livewire.admin.atu.rank-seo.index')
        ->name('admin.atu.rank-seo.index');

    // Global SEO settings (Volt component)
    Route::get('/settings', 'livewire.admin.atu.rank-seo.settings')
        ->name('admin.atu.rank-seo.settings');

    // Edit page SEO (Volt component)
    Route::get('/edit/{id}', 'livewire.admin.atu.rank-seo.edit')
        ->name('admin.atu.rank-seo.edit');

    // Media SEO manager (Volt component)
    Route::get('/media', 'livewire.admin.atu.rank-seo.media-index')
        ->name('admin.atu.rank-seo.media.index');

    // Edit media SEO (Volt component)
    Route::get('/media/edit/{id}', 'livewire.admin.atu.rank-seo.media-edit')
        ->name('admin.atu.rank-seo.media.edit');
});
// >>> ATU Rank SEO Routes END
