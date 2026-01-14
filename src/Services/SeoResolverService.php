<?php

namespace Vormia\ATURankSEO\Services;

use Vormia\ATURankSEO\Models\RankSeoMeta;
use Vormia\ATURankSEO\Models\RankSeoSettings;
use Illuminate\Support\Facades\Log;

class SeoResolverService
{
    public function __construct(
        private readonly SeoCacheService $cacheService
    ) {}

    /**
     * Get SEO for a given slug.
     * Priority: page > global > media
     * Returns array: ['title' => '', 'description' => '', 'keywords' => '', ...]
     */
    public function forSlug(string $slug): array
    {
        // First, try to find slug_registry_id from the slug
        // This assumes you have access to SlugRegistry model
        $slugRegistry = $this->getSlugRegistryBySlug($slug);

        if (!$slugRegistry) {
            return $this->getGlobalSeo();
        }

        return $this->forSlugRegistry($slugRegistry->id, 'page');
    }

    /**
     * Get SEO by slug registry ID and type (page, image, file).
     */
    public function forSlugRegistry(int $slugRegistryId, string $type = 'page'): array
    {
        $cacheKey = $this->cacheService->keyForSlug($slugRegistryId, $type);

        // Try cache first
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Get page-specific SEO
        $pageSeo = RankSeoMeta::where('slug_registry_id', $slugRegistryId)
            ->where('type', $type)
            ->active()
            ->first();

        // Get global SEO
        $globalSeo = $this->getGlobalSeo();

        // Merge based on use_global flag
        $resolved = [];
        if ($pageSeo && $pageSeo->use_global) {
            // Merge page SEO with global SEO (page takes precedence)
            $resolved = array_merge($globalSeo, [
                'title' => $pageSeo->title ?? $globalSeo['title'] ?? null,
                'description' => $pageSeo->description ?? $globalSeo['description'] ?? null,
                'keywords' => $pageSeo->keywords ?? $globalSeo['keywords'] ?? null,
                'canonical_url' => $pageSeo->canonical_url,
                'robots' => $pageSeo->robots,
            ]);
        } elseif ($pageSeo) {
            // Use only page SEO
            $resolved = [
                'title' => $pageSeo->title,
                'description' => $pageSeo->description,
                'keywords' => $pageSeo->keywords,
                'canonical_url' => $pageSeo->canonical_url,
                'robots' => $pageSeo->robots,
            ];
        } else {
            // Use only global SEO
            $resolved = $globalSeo;
        }

        // Cache the resolved SEO
        $this->cacheService->put($cacheKey, $resolved);

        return $resolved;
    }

    /**
     * Get SEO for a media URL.
     */
    public function forMedia(string $mediaUrl): array
    {
        $cacheKey = $this->cacheService->keyForMedia($mediaUrl);

        // Try cache first
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Get media SEO from database
        $mediaSeo = \Vormia\ATURankSEO\Models\RankSeoMedia::where('media_url', $mediaUrl)
            ->active()
            ->first();

        $resolved = [
            'title' => $mediaSeo->title ?? null,
            'alt_text' => $mediaSeo->alt_text ?? null,
            'caption' => $mediaSeo->caption ?? null,
            'metadata' => $mediaSeo->metadata ?? [],
        ];

        // Cache the resolved SEO
        $this->cacheService->put($cacheKey, $resolved);

        return $resolved;
    }

    /**
     * Get global SEO settings.
     */
    private function getGlobalSeo(): array
    {
        $settings = RankSeoSettings::getInstance();

        if (!$settings->is_enabled) {
            return [];
        }

        return [
            'title' => $settings->global_title,
            'description' => $settings->global_description,
            'keywords' => $settings->global_keywords,
            'canonical_url' => null,
            'robots' => null,
        ];
    }

    /**
     * Get SlugRegistry by slug.
     * This method assumes you have access to Vormia's SlugRegistry.
     */
    private function getSlugRegistryBySlug(string $slug)
    {
        try {
            $slugRegistryModel = config('vormia.models.slug_registry', 'App\Models\Vrm\SlugRegistry');

            if (!class_exists($slugRegistryModel)) {
                return null;
            }

            return $slugRegistryModel::where('slug', $slug)->first();
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve SlugRegistry by slug', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
