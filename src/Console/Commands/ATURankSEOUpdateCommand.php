<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;

class ATURankSEOUpdateCommand extends Command
{
    protected $signature = 'aturankseo:update {--skip-env : Do not modify .env files}';

    protected $description = 'Update ATU Rank SEO package files and configurations';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $touchEnv = !$this->option('skip-env');

        $this->step('Updating package files...');
        $results = $installer->update($touchEnv);

        $this->displayCopyResults($results['copied'] ?? []);

        $this->step('Updating routes...');
        $this->handleRoutes($results['routes'] ?? []);

        if ($touchEnv) {
            $this->step('Updating environment files...');
            $this->updateEnvFiles();
        } else {
            $this->line('   ⏭️  Environment keys skipped (--skip-env flag used).');
        }

        $this->newLine();
        $this->info('✅ ATU Rank SEO package updated successfully!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Display copy results grouped by directory
     */
    private function displayCopyResults(array $copyResults): void
    {
        $copied = $copyResults['copied'] ?? [];
        $skipped = $copyResults['skipped'] ?? [];

        if (empty($copied) && empty($skipped)) {
            $this->line('   ℹ️  No files to update');
            return;
        }

        // Group files by directory for better output
        $byDirectory = [];
        foreach ($copied as $file) {
            $dir = dirname($file);
            if (!isset($byDirectory[$dir])) {
                $byDirectory[$dir] = [];
            }
            $byDirectory[$dir][] = basename($file);
        }

        foreach ($byDirectory as $dir => $files) {
            $relativeDir = $this->getRelativePath($dir);
            $this->info("   ✅ Updated " . count($files) . " file(s) in {$relativeDir}/");
        }

        if (!empty($skipped)) {
            $this->warn("   ⚠️  " . count($skipped) . " file(s) skipped");
        }
    }

    /**
     * Get relative path from base path for display
     */
    private function getRelativePath(string $absolutePath): string
    {
        $basePath = base_path();
        if (str_starts_with($absolutePath, $basePath)) {
            return ltrim(str_replace($basePath, '', $absolutePath), '/\\');
        }
        return $absolutePath;
    }

    /**
     * Update .env and .env.example files
     */
    private function updateEnvFiles(): void
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        $envBlock = "\n# ATU Rank SEO Configuration\n"
            . "ATU_RANKSEO_ENABLED=true\n"
            . "ATU_RANKSEO_CACHE_TTL=3600\n";

        // Update .env
        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            $keysToCheck = ['ATU_RANKSEO_ENABLED', 'ATU_RANKSEO_CACHE_TTL'];
            $hasAllKeys = true;
            foreach ($keysToCheck as $key) {
                if (strpos($content, $key) === false) {
                    $hasAllKeys = false;
                    break;
                }
            }
            if (!$hasAllKeys) {
                file_put_contents($envPath, $content . $envBlock, FILE_APPEND);
                $this->info('   ✅ Updated .env file');
            }
        }

        // Update .env.example
        if (file_exists($envExamplePath)) {
            $content = file_get_contents($envExamplePath);
            $keysToCheck = ['ATU_RANKSEO_ENABLED', 'ATU_RANKSEO_CACHE_TTL'];
            $hasAllKeys = true;
            foreach ($keysToCheck as $key) {
                if (strpos($content, $key) === false) {
                    $hasAllKeys = false;
                    break;
                }
            }
            if (!$hasAllKeys) {
                file_put_contents($envExamplePath, $content . $envBlock, FILE_APPEND);
                $this->info('   ✅ Updated .env.example file');
            }
        }
    }

    /**
     * Handle routes results
     */
    private function handleRoutes(array $routes): void
    {
        if ($routes === []) {
            return;
        }

        if ($routes['skipped'] ?? false) {
            $this->warn('   ⚠️  routes/web.php not found.');
            return;
        }

        if ($routes['added'] ?? false) {
            $this->info('   ✅ SEO routes updated in routes/web.php');
        } else {
            $this->info('   ✅ SEO routes already exist in routes/web.php');
        }
    }

    /**
     * Display the header
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('🔄 Updating ATU Rank SEO Package...');
        $this->line('   Version: ' . ATURankSEO::VERSION);
        $this->newLine();
    }

    /**
     * Display a step message
     */
    private function step(string $message): void
    {
        $this->info("📦 {$message}");
    }
}
