<?php

namespace Vormia\ATURankSEO\Services;

use Vormia\ATURankSEO\Models\RankSeoMeta;
use Vormia\ATURankSEO\Models\RankSeoSettings;
use Vormia\ATURankSEO\Events\SeoSnapshotGenerated;
use Illuminate\Support\Facades\Log;

class SeoSnapshotService
{
    public function __construct(
        private readonly SeoCacheService $cacheService
    ) {}

    /**
     * Generate and save snapshot SEO for a slug/entity.
     * Resolves all placeholders using provided data.
     */
    public function generateForSlug(string $slug, array $data): void
    {
        // Get or create slug registry
        $slugRegistry = $this->getOrCreateSlugRegistryBySlug($slug);
        if (!$slugRegistry) {
            Log::error('Failed to get or create SlugRegistry for slug', ['slug' => $slug]);

            return;
        }

        // Get or create SEO entry
        $seoMeta = RankSeoMeta::firstOrNew([
            'slug_registry_id' => $slugRegistry->id,
            'type' => 'page',
        ]);

        // Resolve placeholders
        $resolvedData = $this->resolvePlaceholders($data, $seoMeta->toArray());

        // Update SEO entry
        $seoMeta->fill([
            'title' => $resolvedData['title'] ?? null,
            'description' => $resolvedData['description'] ?? null,
            'keywords' => $resolvedData['keywords'] ?? null,
            'canonical_url' => $resolvedData['canonical_url'] ?? null,
            'robots' => $resolvedData['robots'] ?? null,
            'use_global' => $resolvedData['use_global'] ?? true,
            'is_active' => $resolvedData['is_active'] ?? true,
        ]);

        $seoMeta->save();

        // Invalidate cache
        $cacheKey = $this->cacheService->keyForSlug($slugRegistry->id, 'page');
        $this->cacheService->invalidate($cacheKey);

        // Fire event
        event(new SeoSnapshotGenerated($seoMeta));
    }

    /**
     * Generate and save snapshot SEO for media.
     */
    public function generateForMedia(string $mediaUrl, array $data): void
    {
        // Get or create media SEO entry
        $mediaSeo = \Vormia\ATURankSEO\Models\RankSeoMedia::firstOrNew([
            'media_url' => $mediaUrl,
        ]);

        // Resolve placeholders
        $resolvedData = $this->resolvePlaceholders($data, $mediaSeo->toArray());

        // Update media SEO entry
        $mediaSeo->fill([
            'slug_registry_id' => $resolvedData['slug_registry_id'] ?? null,
            'media_type' => $resolvedData['media_type'] ?? 'image',
            'title' => $resolvedData['title'] ?? null,
            'alt_text' => $resolvedData['alt_text'] ?? null,
            'caption' => $resolvedData['caption'] ?? null,
            'metadata' => $resolvedData['metadata'] ?? [],
            'is_active' => $resolvedData['is_active'] ?? true,
        ]);

        $mediaSeo->save();

        // Invalidate cache
        $cacheKey = $this->cacheService->keyForMedia($mediaUrl);
        $this->cacheService->invalidate($cacheKey);

        // Fire event
        event(new \Vormia\ATURankSEO\Events\MediaIndexed($mediaSeo));
    }

    /**
     * Update an existing SEO entry.
     */
    public function updateSeo(int $seoId, array $data): void
    {
        $seoMeta = RankSeoMeta::findOrFail($seoId);

        // Resolve placeholders if needed
        $resolvedData = $this->resolvePlaceholders($data, $seoMeta->toArray());

        $seoMeta->update($resolvedData);
        $seoMeta->refresh();

        // Invalidate cache
        if ($seoMeta->slug_registry_id) {
            $cacheKey = $this->cacheService->keyForSlug($seoMeta->slug_registry_id, $seoMeta->type);
            $this->cacheService->invalidate($cacheKey);
        }

        // Fire event
        event(new SeoSnapshotGenerated($seoMeta));
    }

    /**
     * Delete or deactivate an SEO entry.
     */
    public function deleteSeo(int $seoId, bool $hardDelete = false): void
    {
        $seoMeta = RankSeoMeta::findOrFail($seoId);
        $slugRegistryId = $seoMeta->slug_registry_id;
        $type = $seoMeta->type;

        if ($hardDelete) {
            $seoMeta->delete();
        } else {
            $seoMeta->update(['is_active' => false]);
        }

        // Invalidate cache
        if ($slugRegistryId) {
            $cacheKey = $this->cacheService->keyForSlug($slugRegistryId, $type);
            $this->cacheService->invalidate($cacheKey);
        }
    }

    /**
     * Resolve placeholders in SEO data.
     * Placeholders like {make}, {model}, {year}, {site_name}, {current_year}
     */
    private function resolvePlaceholders(array $data, array $existing = []): array
    {
        $resolved = [];
        $settings = RankSeoSettings::getInstance();
        $globalVars = $settings->dynamic_variables ?? [];

        // Merge data with global variables
        $allVars = array_merge($globalVars, $data);

        // Add common placeholders
        $allVars['current_year'] = date('Y');
        $allVars['current_month'] = date('F');
        $allVars['current_date'] = date('Y-m-d');

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                $resolved[$key] = $value;
                continue;
            }

            // Replace placeholders {placeholder_name}
            $resolved[$key] = preg_replace_callback(
                '/\{([a-zA-Z0-9_]+)\}/',
                function ($matches) use ($allVars) {
                    $placeholder = $matches[1];

                    return $allVars[$placeholder] ?? $matches[0]; // Return original if not found
                },
                $value
            );
        }

        return array_merge($existing, $resolved);
    }

    /**
     * Get SlugRegistry by slug.
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

    /**
     * Get or create SlugRegistry by slug.
     * Creates the registry entry if it doesn't exist.
     */
    private function getOrCreateSlugRegistryBySlug(string $slug)
    {
        try {
            $slugRegistryModel = config('vormia.models.slug_registry', 'App\Models\Vrm\SlugRegistry');

            if (!class_exists($slugRegistryModel)) {
                Log::warning('SlugRegistry model class does not exist', [
                    'model' => $slugRegistryModel,
                    'slug' => $slug,
                ]);

                return null;
            }

            // Use firstOrCreate to get existing or create new SlugRegistry
            return $slugRegistryModel::firstOrCreate(
                ['slug' => $slug],
                ['slug' => $slug] // Additional attributes if needed for creation
            );
        } catch (\Exception $e) {
            Log::error('Failed to get or create SlugRegistry by slug', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
