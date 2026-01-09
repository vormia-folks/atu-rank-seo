<?php

namespace Vormia\ATURankSEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankSeoMeta extends Model
{
    protected $table = 'atu_rankseo_meta';

    protected $fillable = [
        'slug_registry_id',
        'type',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'robots',
        'use_global',
        'is_active',
    ];

    protected $casts = [
        'use_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the slug registry that owns this SEO meta.
     */
    public function slugRegistry(): BelongsTo
    {
        // Note: This assumes vrm_slug_registry table exists via Vormia package
        // We use a dynamic relationship since we don't control that model
        return $this->belongsTo(
            config('vormia.models.slug_registry', 'App\Models\SlugRegistry'),
            'slug_registry_id'
        );
    }

    /**
     * Scope to get only active SEO entries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get entries for a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
