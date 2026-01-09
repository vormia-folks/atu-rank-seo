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
    // SEO entries list
    Route::get('/', \App\Livewire\Admin\ATU\RankSeo\IndexComponent::class)
        ->name('admin.atu.rank-seo.index');

    // Global SEO settings
    Route::get('/settings', \App\Livewire\Admin\ATU\RankSeo\SettingsComponent::class)
        ->name('admin.atu.rank-seo.settings');

    // Edit page SEO
    Route::get('/edit/{id}', \App\Livewire\Admin\ATU\RankSeo\EditComponent::class)
        ->name('admin.atu.rank-seo.edit');

    // Media SEO manager
    Route::get('/media', \App\Livewire\Admin\ATU\RankSeo\MediaIndexComponent::class)
        ->name('admin.atu.rank-seo.media.index');

    // Edit media SEO
    Route::get('/media/edit/{id}', \App\Livewire\Admin\ATU\RankSeo\MediaEditComponent::class)
        ->name('admin.atu.rank-seo.media.edit');
});
// >>> ATU Rank SEO Routes END
