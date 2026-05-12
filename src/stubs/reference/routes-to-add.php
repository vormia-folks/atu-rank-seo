<?php

// ATU Rank SEO admin Livewire routes (optional manual merge into routes/web.php).
// Wrap with Route::middleware(['web', 'auth']) or your stack if needed.
// `aturankseo:install` appends an equivalent block using the markers
// `// >>> ATU Rank SEO start` … `// <<< ATU Rank SEO end` (see Installer).

use Illuminate\Support\Facades\Route;

// >>> ATU Rank SEO Web Routes START
Route::prefix('admin/atu')->name('admin.atu.rank-seo.')->group(function () {
    Route::livewire('rank-seo', 'rank-seo.index')->name('index');
    Route::livewire('rank-seo/settings', 'rank-seo.settings')->name('settings');
    Route::livewire('rank-seo/edit/{id}', 'rank-seo.edit')->name('edit');
    Route::livewire('rank-seo/media', 'rank-seo.media-index')->name('media.index');
    Route::livewire('rank-seo/media/edit/{id}', 'rank-seo.media-edit')->name('media.edit');
});
// <<< ATU Rank SEO Web Routes END
