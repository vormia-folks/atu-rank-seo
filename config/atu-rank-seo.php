<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ATU Rank SEO Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('ATU_RANKSEO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Admin UI (Livewire)
    |--------------------------------------------------------------------------
    |
    | Routes are registered by the package service provider. Override
    | middleware or prefix here, or set enabled to false to register nothing
    | and wire routes yourself (see src/stubs/reference/routes-to-add.php).
    |
    */
    'admin' => [
        'enabled' => env('ATU_RANKSEO_ADMIN_ENABLED', true),
        'middleware' => ['web', 'auth'],
        'prefix' => 'admin/atu',
    ],

    'cache' => [
        'ttl' => env('ATU_RANKSEO_CACHE_TTL', 3600),
        'prefix' => 'atu_rankseo',
    ],

    'default_variables' => [
        'site_name' => env('APP_NAME', 'My Site'),
        'current_year' => date('Y'),
        'current_month' => date('F'),
        'current_date' => date('Y-m-d'),
    ],

    'slug_registry_model' => config('vormia.models.slug_registry', 'App\Models\Vrm\SlugRegistry'),

    'media_directory' => 'media',

    'media_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
        'file' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'],
    ],
];
