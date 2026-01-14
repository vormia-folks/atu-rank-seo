<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ATU Rank SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ATU Rank SEO package
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Master Enable/Disable
    |--------------------------------------------------------------------------
    |
    | Master switch to enable or disable SEO functionality globally.
    | When disabled, all SEO meta outputs are ignored.
    |
    */
    'enabled' => env('ATU_RANKSEO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for SEO data
    |
    */
    'cache' => [
        'ttl' => env('ATU_RANKSEO_CACHE_TTL', 3600), // Cache TTL in seconds (default: 1 hour)
        'prefix' => 'atu_rankseo', // Cache key prefix
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Placeholder Variables
    |--------------------------------------------------------------------------
    |
    | Default variables available for placeholder resolution.
    | These can be overridden via the settings table.
    |
    */
    'default_variables' => [
        'site_name' => env('APP_NAME', 'My Site'),
        'current_year' => date('Y'),
        'current_month' => date('F'),
        'current_date' => date('Y-m-d'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Registry Model
    |--------------------------------------------------------------------------
    |
    | The model class for SlugRegistry (from Vormia package).
    | This is used for resolving slugs to SEO data.
    |
    */
    'slug_registry_model' => config('vormia.models.slug_registry', 'App\Models\Vrm\SlugRegistry'),

    /*
    |--------------------------------------------------------------------------
    | Media Directory
    |--------------------------------------------------------------------------
    |
    | Directory path for media files (relative to public_path).
    | Media files in this directory will be indexed for SEO.
    |
    */
    'media_directory' => 'media',

    /*
    |--------------------------------------------------------------------------
    | Supported Media Types
    |--------------------------------------------------------------------------
    |
    | File extensions that are considered images vs files.
    |
    */
    'media_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
        'file' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'],
    ],
];
