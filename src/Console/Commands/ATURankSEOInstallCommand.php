<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ATURankSEOInstallCommand extends Command
{
    protected $signature = 'aturankseo:install
                            {--skip-env : Do not modify .env files}
                            {--no-overwrite : Skip existing files instead of replacing}';

    protected $description = 'Install ATU Rank SEO package with all necessary files and configurations';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $overwrite = !$this->option('no-overwrite');
        $touchEnv = !$this->option('skip-env');

        // Copy files
        $this->step('Copying package files and stubs...');
        $results = $installer->install($overwrite, false);
        $this->displayCopyResults($results['copied']);

        // Environment variables
        $this->step('Updating environment files...');
        if ($touchEnv) {
            $this->updateEnvFiles();
        } else {
            $this->line('   ⏭️  Environment keys skipped (--skip-env flag used).');
        }

        // Routes
        $this->step('Ensuring routes...');
        $this->handleRoutes($results['routes'] ?? []);

        // Migrations
        $migrationsRun = $this->handleMigrations();

        // Seeders
        if ($migrationsRun) {
            $this->handleSeeders();
        }

        $this->displayCompletionMessage($touchEnv, $migrationsRun);

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
            $this->line('   ℹ️  No files to copy');
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
            $this->info("   ✅ Copied " . count($files) . " file(s) to {$relativeDir}/");
        }

        if (!empty($skipped)) {
            $this->warn("   ⚠️  " . count($skipped) . " existing file(s) skipped (use --no-overwrite to keep existing files)");
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
     * Update .env and .env.example files with ATU Rank SEO configuration
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
            // Check if any of the ATU Rank SEO keys are missing
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
            }
        }

        // Update .env.example
        if (file_exists($envExamplePath)) {
            $content = file_get_contents($envExamplePath);
            // Check if any of the ATU Rank SEO keys are missing
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
            }
        }

        $this->info('   ✅ Environment files updated successfully (ATU Rank SEO configuration).');
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
            $this->warn('   ⚠️  routes/web.php not found. SEO routes were not added.');
            $this->line('   Create routes/web.php first, then re-run the installer to add the routes.');
            return;
        }

        if ($routes['added'] ?? false) {
            $this->info('   ✅ SEO routes added to routes/web.php');
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
        $this->info('🚀 Installing ATU Rank SEO Package...');
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

    /**
     * Handle migrations prompt and execution
     */
    private function handleMigrations(): bool
    {
        $this->step('Running database migrations...');

        if (!$this->confirm('Would you like to run migrations now?', true)) {
            $this->line('   ⏭️  Migrations skipped. You can run them later with: php artisan migrate');
            return false;
        }

        return $this->runMigrations();
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): bool
    {
        try {
            $this->line('   Running migrations...');
            $exitCode = Artisan::call('migrate', [], $this->getOutput());

            // Display any output from the migrate command
            $output = Artisan::output();
            if (!empty(trim($output))) {
                $this->line($output);
            }

            if ($exitCode === 0) {
                $this->info('   ✅ Migrations completed successfully!');
                return true;
            } else {
                $this->error('   ❌ Migrations completed with errors (exit code: ' . $exitCode . ')');
                $this->warn('   ⚠️  You can run migrations manually later with: php artisan migrate');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Migration failed: ' . $e->getMessage());
            $this->warn('   ⚠️  You can run migrations manually later with: php artisan migrate');
            return false;
        }
    }

    /**
     * Handle seeders execution
     */
    private function handleSeeders(): void
    {
        $this->step('Running database seeders...');

        if (!$this->confirm('Would you like to seed the default SEO settings now?', true)) {
            $this->line('   ⏭️  Seeders skipped. You can run them later with: php artisan db:seed --class=ATURankSEOSeeder');
            return;
        }

        $this->runSeeders();
    }

    /**
     * Run database seeders
     */
    private function runSeeders(): void
    {
        try {
            $this->line('   Running seeders...');
            $exitCode = Artisan::call('db:seed', [
                '--class' => 'ATURankSEOSeeder'
            ], $this->getOutput());

            // Display any output from the seeder command
            $output = Artisan::output();
            if (!empty(trim($output))) {
                $this->line($output);
            }

            if ($exitCode === 0) {
                $this->info('   ✅ Seeders completed successfully!');
            } else {
                $this->error('   ❌ Seeders completed with errors (exit code: ' . $exitCode . ')');
                $this->warn('   ⚠️  You can run seeders manually later with: php artisan db:seed --class=ATURankSEOSeeder');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Seeder failed: ' . $e->getMessage());
            $this->warn('   ⚠️  You can run seeders manually later with: php artisan db:seed --class=ATURankSEOSeeder');
        }
    }

    /**
     * Display completion message with next steps
     */
    private function displayCompletionMessage(bool $envTouched, bool $migrationsRun): void
    {
        $this->newLine();
        $this->info('🎉 ATU Rank SEO package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Review and configure your .env file with SEO settings (if needed)');

        if (!$migrationsRun) {
            $this->line('   2. Run migrations: php artisan migrate');
            $this->line('   3. Run seeders: php artisan db:seed --class=ATURankSEOSeeder');
            $this->line('   4. Review the implementation guide in the package documentation');
        } else {
            $this->line('   2. Review the implementation guide in the package documentation');
        }

        $this->newLine();

        if (!$envTouched) {
            $this->warn('⚠️  Note: Environment keys were not modified (--skip-env flag used).');
            $this->line('   Run: php artisan aturankseo:help to see required env keys.');
            $this->newLine();
        }

        $this->comment('📖 For help and available commands, run: php artisan aturankseo:help');
        $this->newLine();

        $this->info('✨ Happy coding with ATU Rank SEO!');
    }
}
