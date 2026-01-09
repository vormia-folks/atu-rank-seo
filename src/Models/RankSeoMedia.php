<?php

namespace Vormia\ATURankSEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankSeoMedia extends Model
{
    protected $table = 'atu_rankseo_media';

    protected $fillable = [
        'slug_registry_id',
        'media_url',
        'media_type',
        'title',
        'alt_text',
        'caption',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the slug registry that owns this media SEO.
     */
    public function slugRegistry(): BelongsTo
    {
        // Note: This assumes vrm_slug_registry table exists via Vormia package
        return $this->belongsTo(
            config('vormia.models.slug_registry', 'App\Models\SlugRegistry'),
            'slug_registry_id'
        );
    }

    /**
     * Scope to get only active media SEO entries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get entries for a specific media type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('media_type', $type);
    }
}
