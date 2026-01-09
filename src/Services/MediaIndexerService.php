<?php

namespace Vormia\ATURankSEO\Services;

use Vormia\ATURankSEO\Models\RankSeoMedia;
use Vormia\ATURankSEO\Events\MediaIndexed;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MediaIndexerService
{
    public function __construct(
        private readonly SeoCacheService $cacheService
    ) {}

    /**
     * Scan public/media directory recursively and register all media.
     */
    public function scanAndRegister(): void
    {
        $mediaPath = public_path('media');

        if (!File::exists($mediaPath)) {
            Log::info('Media directory does not exist', ['path' => $mediaPath]);

            return;
        }

        $files = File::allFiles($mediaPath);
        $registered = 0;

        foreach ($files as $file) {
            $relativePath = str_replace(public_path(), '', $file->getPathname());
            $mediaUrl = ltrim($relativePath, '/');

            // Skip if already registered
            if (RankSeoMedia::where('media_url', $mediaUrl)->exists()) {
                continue;
            }

            // Determine media type
            $extension = strtolower($file->getExtension());
            $mediaType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']) ? 'image' : 'file';

            // Register media
            $this->registerMedia($mediaUrl, [
                'media_type' => $mediaType,
                'title' => $file->getFilename(),
                'alt_text' => null,
                'caption' => null,
                'metadata' => [
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'mime_type' => File::mimeType($file->getPathname()),
                ],
            ]);

            $registered++;
        }

        Log::info('Media scan completed', ['registered' => $registered]);
    }

    /**
     * Register or update a single media file.
     */
    public function registerMedia(string $mediaPath, array $metadata = []): void
    {
        // Normalize media path (remove leading slash if present)
        $mediaUrl = ltrim($mediaPath, '/');

        // Get or create media SEO entry
        $mediaSeo = RankSeoMedia::firstOrNew([
            'media_url' => $mediaUrl,
        ]);

        // Update with metadata
        $mediaSeo->fill([
            'media_type' => $metadata['media_type'] ?? $this->detectMediaType($mediaUrl),
            'title' => $metadata['title'] ?? basename($mediaUrl),
            'alt_text' => $metadata['alt_text'] ?? null,
            'caption' => $metadata['caption'] ?? null,
            'metadata' => $metadata['metadata'] ?? [],
            'slug_registry_id' => $metadata['slug_registry_id'] ?? null,
            'is_active' => $metadata['is_active'] ?? true,
        ]);

        $mediaSeo->save();

        // Invalidate cache
        $cacheKey = $this->cacheService->keyForMedia($mediaUrl);
        $this->cacheService->invalidate($cacheKey);

        // Fire event
        event(new MediaIndexed($mediaSeo));
    }

    /**
     * Delete or deactivate a media SEO entry.
     */
    public function deleteMedia(string $mediaUrl, bool $hardDelete = false): void
    {
        $mediaSeo = RankSeoMedia::where('media_url', $mediaUrl)->first();

        if (!$mediaSeo) {
            return;
        }

        if ($hardDelete) {
            $mediaSeo->delete();
        } else {
            $mediaSeo->update(['is_active' => false]);
        }

        // Invalidate cache
        $cacheKey = $this->cacheService->keyForMedia($mediaUrl);
        $this->cacheService->invalidate($cacheKey);
    }

    /**
     * Detect media type from file extension.
     */
    private function detectMediaType(string $mediaUrl): string
    {
        $extension = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];

        return in_array($extension, $imageExtensions) ? 'image' : 'file';
    }
}
