<?php

namespace Vormia\ATURankSEO\Support;

use Illuminate\Filesystem\Filesystem;

/**
 * Host .env / .env.example keys and optional copy of admin views + routes into the application.
 */
class Installer
{
    public const WEB_ROUTES_MARKER_START = '// >>> ATU Rank SEO start';

    public const WEB_ROUTES_MARKER_END = '// <<< ATU Rank SEO end';

    private const ENV_KEYS = [
        'ATU_RANKSEO_ENABLED' => 'true',
        'ATU_RANKSEO_CACHE_TTL' => '3600',
        'ATU_RANKSEO_ADMIN_ENABLED' => 'true',
    ];

    public function __construct(
        private readonly Filesystem $files,
        private readonly string $appBasePath
    ) {}

    /**
     * @return array{env: array<string, array<int, string>>}
     */
    public function install(bool $touchEnv = true): array
    {
        $envChanges = $touchEnv ? $this->ensureEnvKeys() : [];

        return ['env' => $envChanges];
    }

    /**
     * @return array{env: array<string, array<int, string>>}
     */
    public function update(bool $touchEnv = true): array
    {
        return $this->install($touchEnv);
    }

    /**
     * @return array{env: array<string, array<int, string>>}
     */
    public function uninstall(bool $touchEnv = true): array
    {
        $env = $touchEnv ? $this->removeEnvKeys() : [];

        return ['env' => $env];
    }

    public function ensureEnvKeys(): array
    {
        $paths = [
            $this->pathJoin($this->appBasePath, '.env'),
            $this->pathJoin($this->appBasePath, '.env.example'),
        ];

        $added = [];

        foreach ($paths as $envPath) {
            if (! $this->files->exists($envPath)) {
                $added[$envPath] = [];

                continue;
            }

            $existing = $this->files->get($envPath);
            $addedKeys = [];
            $updated = $this->appendEnvBlock($existing, $addedKeys);

            if ($updated !== $existing) {
                $this->files->put($envPath, $updated);
                $added[$envPath] = $addedKeys;
            } else {
                $added[$envPath] = [];
            }
        }

        return $added;
    }

    private function appendEnvBlock(string $current, ?array &$addedKeys = []): string
    {
        $addedKeys = [];
        $lines = rtrim($current) === '' ? [] : preg_split('/\r\n|\r|\n/', $current);
        $presentKeys = $this->extractExistingKeys($lines);

        foreach (self::ENV_KEYS as $key => $value) {
            if (! in_array($key, $presentKeys, true)) {
                $addedKeys[] = $key;
            }
        }

        if ($addedKeys === []) {
            return $current;
        }

        $block = [];
        $block[] = '# ATU Rank SEO Configuration';
        foreach ($addedKeys as $key) {
            $block[] = $key.'='.self::ENV_KEYS[$key];
        }

        $merged = array_merge($lines, $lines ? [''] : [], $block);

        return implode(PHP_EOL, $merged).PHP_EOL;
    }

    private function extractExistingKeys(array $lines): array
    {
        $keys = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key] = explode('=', $line, 2);
            $keys[] = trim($key);
        }

        return $keys;
    }

    public function removeEnvKeys(): array
    {
        $paths = [
            $this->pathJoin($this->appBasePath, '.env'),
            $this->pathJoin($this->appBasePath, '.env.example'),
        ];

        $removed = [];

        foreach ($paths as $envPath) {
            if (! $this->files->exists($envPath)) {
                $removed[$envPath] = [];

                continue;
            }

            $removedKeys = [];
            $content = $this->files->get($envPath);
            $updated = $this->stripEnvKeys($content, $removedKeys);

            if ($updated !== $content) {
                $this->files->put($envPath, $updated);
            }

            $removed[$envPath] = $removedKeys;
        }

        return $removed;
    }

    private function stripEnvKeys(string $content, ?array &$removedKeys = []): string
    {
        $removedKeys = [];
        $lines = rtrim($content) === '' ? [] : preg_split('/\r\n|\r|\n/', $content);
        $remaining = [];

        foreach ($lines as $line) {
            if (str_contains($line, '# ATU Rank SEO Configuration')) {
                continue;
            }

            if (str_contains($line, '# ATU Rank SEO (host routes)')) {
                continue;
            }

            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '#')) {
                $remaining[] = $line;

                continue;
            }

            if (str_contains($line, '=')) {
                [$key] = explode('=', $line, 2);
                $key = trim($key);

                if (array_key_exists($key, self::ENV_KEYS)) {
                    $removedKeys[] = $key;

                    continue;
                }
            }

            $remaining[] = $line;
        }

        $normalized = preg_replace("/[\r\n]{3,}/", "\n\n", implode(PHP_EOL, $remaining));

        return rtrim($normalized).PHP_EOL;
    }

    /**
     * Copy package Livewire admin blades into the host app and append Livewire routes to routes/web.php.
     * Sets ATU_RANKSEO_ADMIN_ENABLED=false so the package does not register duplicate admin routes.
     *
     * @param  array<int, string>  $middleware
     * @return array{
     *     views: array{copied: array<int, string>, skipped: array<int, string>, overwritten: array<int, string>},
     *     routes: array{appended: bool, skipped: bool, reason?: string},
     *     env_admin_disabled: array<string, bool>
     * }
     */
    public function installHostAdminAssets(string $packageBasePath, array $middleware, string $prefix, bool $forceViews = false): array
    {
        $views = $this->copyRankSeoViewsFromPackage($packageBasePath, $forceViews);
        $routes = $this->appendRankSeoRoutesToWebPhp($middleware, $prefix);

        $envAdminDisabled = [];
        if (($routes['appended'] ?? false) === true || ($routes['skipped'] ?? false) === true) {
            $envAdminDisabled = $this->setEnvKeyInHostFiles('ATU_RANKSEO_ADMIN_ENABLED', 'false');
        }

        return [
            'views' => $views,
            'routes' => $routes,
            'env_admin_disabled' => $envAdminDisabled,
        ];
    }

    /**
     * @return array{copied: array<int, string>, skipped: array<int, string>, overwritten: array<int, string>}
     */
    public function copyRankSeoViewsFromPackage(string $packageBasePath, bool $force = false): array
    {
        $source = $this->pathJoin($packageBasePath, 'resources/views/livewire/admin/atu/rank-seo');
        $dest = $this->pathJoin($this->appBasePath, 'resources/views/livewire/admin/atu/rank-seo');

        $copied = [];
        $skipped = [];
        $overwritten = [];

        if (! $this->files->isDirectory($source)) {
            return ['copied' => [], 'skipped' => [], 'overwritten' => []];
        }

        $this->files->ensureDirectoryExists($dest);

        foreach ($this->files->files($source) as $fileInfo) {
            $name = $fileInfo->getFilename();
            if (! str_ends_with($name, '.blade.php')) {
                continue;
            }

            $target = $this->pathJoin($dest, $name);
            $exists = $this->files->exists($target);

            if ($exists && ! $force) {
                $skipped[] = $name;

                continue;
            }

            $this->files->copy($fileInfo->getPathname(), $target);

            if ($exists && $force) {
                $overwritten[] = $name;
            } else {
                $copied[] = $name;
            }
        }

        return [
            'copied' => $copied,
            'skipped' => $skipped,
            'overwritten' => $overwritten,
        ];
    }

    /**
     * @param  array<int, string>  $middleware
     * @return array{appended: bool, skipped: bool, reason?: string}
     */
    public function appendRankSeoRoutesToWebPhp(array $middleware, string $prefix): array
    {
        $webPhp = $this->pathJoin($this->appBasePath, 'routes/web.php');

        if (! $this->files->exists($webPhp)) {
            return ['appended' => false, 'skipped' => false, 'reason' => 'routes/web.php not found'];
        }

        $content = $this->files->get($webPhp);

        if (str_contains($content, self::WEB_ROUTES_MARKER_START)) {
            return ['appended' => false, 'skipped' => true, 'reason' => 'ATU Rank SEO route block already present'];
        }

        $block = $this->buildWebRoutesBlock($middleware, $prefix);
        $suffix = str_ends_with($content, "\n") ? '' : "\n";
        $this->files->put($webPhp, rtrim($content).$suffix."\n".$block."\n");

        return ['appended' => true, 'skipped' => false];
    }

    /**
     * @param  array<int, string>  $middleware
     */
    public function buildWebRoutesBlock(array $middleware, string $prefix): string
    {
        $middlewarePhp = var_export(array_values($middleware), true);
        $prefixPhp = var_export(trim((string) $prefix, '/'), true);

        return <<<PHP
// >>> ATU Rank SEO start (generated by aturankseo:install; set ATU_RANKSEO_ADMIN_ENABLED=false so the package does not register duplicate admin routes)
\\Illuminate\\Support\\Facades\\Route::middleware({$middlewarePhp})
    ->prefix({$prefixPhp})
    ->name('admin.atu.rank-seo.')
    ->group(function () {
        \\Illuminate\\Support\\Facades\\Route::livewire('/rank-seo', 'rank-seo.index')->name('index');
        \\Illuminate\\Support\\Facades\\Route::livewire('/rank-seo/settings', 'rank-seo.settings')->name('settings');
        \\Illuminate\\Support\\Facades\\Route::livewire('/rank-seo/edit/{id}', 'rank-seo.edit')->name('edit');
        \\Illuminate\\Support\\Facades\\Route::livewire('/rank-seo/media', 'rank-seo.media-index')->name('media.index');
        \\Illuminate\\Support\\Facades\\Route::livewire('/rank-seo/media/edit/{id}', 'rank-seo.media-edit')->name('media.edit');
    });

// <<< ATU Rank SEO end
PHP;
    }

    /**
     * Set a single env key in .env and .env.example (update existing line or append).
     *
     * @return array<string, bool> path => changed
     */
    public function setEnvKeyInHostFiles(string $key, string $value): array
    {
        $paths = [
            $this->pathJoin($this->appBasePath, '.env'),
            $this->pathJoin($this->appBasePath, '.env.example'),
        ];

        $changed = [];

        foreach ($paths as $envPath) {
            $changed[$envPath] = false;

            if (! $this->files->exists($envPath)) {
                continue;
            }

            $content = $this->files->get($envPath);
            $updated = $this->mergeEnvKeyLine($content, $key, $value);

            if ($updated !== $content) {
                $this->files->put($envPath, $updated);
                $changed[$envPath] = true;
            }
        }

        return $changed;
    }

    private function mergeEnvKeyLine(string $content, string $key, string $value): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $found = false;
        $out = [];

        foreach ($lines as $line) {
            $trim = trim($line);

            if ($trim === '' || str_starts_with($trim, '#') || ! str_contains($line, '=')) {
                $out[] = $line;

                continue;
            }

            [$k] = explode('=', $line, 2);
            if (trim($k) === $key) {
                $out[] = $key.'='.$value;
                $found = true;
            } else {
                $out[] = $line;
            }
        }

        if (! $found) {
            $lastKey = array_key_last($out);
            if ($out !== [] && $lastKey !== null && trim((string) $out[$lastKey]) !== '') {
                $out[] = '';
            }
            $out[] = '# ATU Rank SEO (host routes)';
            $out[] = $key.'='.$value;
        }

        return rtrim(implode(PHP_EOL, $out), "\r\n").PHP_EOL;
    }

    private function pathJoin(string ...$parts): string
    {
        $filtered = collect($parts)->filter(fn ($p) => $p !== '');

        if ($filtered->isEmpty()) {
            return '';
        }

        $first = $filtered->first();
        $isAbsolute = str_starts_with($first, '/') || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:/i', $first));

        if ($isAbsolute) {
            $first = rtrim($first, '/\\');
            $rest = $filtered->skip(1)
                ->map(fn ($p) => trim($p, '/\\'))
                ->filter(fn ($p) => $p !== '');

            return $rest->isEmpty()
                ? $first
                : $first.DIRECTORY_SEPARATOR.$rest->implode(DIRECTORY_SEPARATOR);
        }

        return $filtered
            ->map(fn ($p) => trim($p, '/\\'))
            ->implode(DIRECTORY_SEPARATOR);
    }
}
