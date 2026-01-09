<?php

// ATU Rank SEO Routes
// Add these routes to your routes/web.php file
// Place them inside: Route::middleware(['auth'])->group(function () { ... });
// Note: If you have configured your own starterkit, you may need to add: use Livewire\Volt\Volt;

Route::group(['prefix' => 'admin/atu'], function () {
    Volt::route('rank-seo', 'admin.atu.rank-seo.index')->name('admin.atu.rank-seo.index');
    Volt::route('rank-seo/settings', 'admin.atu.rank-seo.settings')->name('admin.atu.rank-seo.settings');
    Volt::route('rank-seo/edit/{id}', 'admin.atu.rank-seo.edit')->name('admin.atu.rank-seo.edit');
    Volt::route('rank-seo/media', 'admin.atu.rank-seo.media-index')->name('admin.atu.rank-seo.media.index');
    Volt::route('rank-seo/media/edit/{id}', 'admin.atu.rank-seo.media-edit')->name('admin.atu.rank-seo.media.edit');
});
