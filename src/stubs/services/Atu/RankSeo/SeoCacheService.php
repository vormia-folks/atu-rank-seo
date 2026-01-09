<?php

namespace Atu\RankSeo\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SeoCacheService
{
    private string $prefix;

    public function __construct()
    {
        $this->prefix = config('atu-rank-seo.cache_prefix', 'atu_rankseo');
    }

    /**
     * Cache SEO payload for a slug or media.
     */
    public function put(string $cacheKey, array $seoPayload, int $ttl = null): void
    {
        $ttl = $ttl ?? config('atu-rank-seo.cache_ttl', 3600);

        try {
            Cache::put($cacheKey, $seoPayload, $ttl);
        } catch (\Exception $e) {
            Log::warning('Failed to cache SEO payload', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retrieve SEO payload from cache.
     */
    public function get(string $cacheKey): ?array
    {
        try {
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve SEO payload from cache', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Invalidate cache for a slug or media.
     */
    public function invalidate(string $cacheKey): void
    {
        try {
            Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate SEO cache', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate standard cache key for slug_registry_id and type.
     */
    public function keyForSlug(int $slugRegistryId, string $type = 'page'): string
    {
        return "{$this->prefix}:slug:{$slugRegistryId}:{$type}";
    }

    /**
     * Generate cache key for media URL.
     */
    public function keyForMedia(string $mediaUrl): string
    {
        $hash = md5($mediaUrl);

        return "{$this->prefix}:media:{$hash}";
    }

    /**
     * Clear all SEO cache entries.
     */
    public function clearAll(): void
    {
        try {
            // Note: This is a simple implementation. For production, you might want
            // to use cache tags if your cache driver supports it.
            Cache::flush();
        } catch (\Exception $e) {
            Log::warning('Failed to clear all SEO cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
