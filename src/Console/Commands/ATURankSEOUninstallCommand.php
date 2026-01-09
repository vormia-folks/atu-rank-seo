<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ATURankSEOUninstallCommand extends Command
{
    protected $signature = 'aturankseo:uninstall {--keep-env : Leave env keys untouched} {--force : Skip confirmation prompts}';

    protected $description = 'Remove all ATU Rank SEO package files and configurations';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $force = $this->option('force');
        $keepEnv = $this->option('keep-env');

        // Warning message
        $this->error('⚠️  DANGER: This will remove ATU Rank SEO from your application!');
        $this->warn('   This action will:');
        $this->warn('   • Remove all ATU Rank SEO copied files and stubs');
        $this->warn('   • Remove SEO routes from routes/web.php');
        $this->warn('   • Note: Composer packages are NOT uninstalled');
        $this->newLine();

        if (!$force && !$this->confirm('Are you absolutely sure you want to uninstall ATU Rank SEO?', false)) {
            $this->info('❌ Uninstall cancelled.');
            return self::SUCCESS;
        }

        // Ask about migrations
        $undoMigrations = false;
        if (!$force) {
            $this->newLine();
            $this->error('⚠️  WARNING: Rolling back migrations will DELETE ALL DATA in ATU Rank SEO database tables!');
            $this->warn('   This includes: SEO metadata, media SEO, and settings.');
            $undoMigrations = $this->confirm('Do you wish to undo migrations? (This will rollback and delete migration files)', false);
        } else {
            // In force mode, default to not rolling back migrations for safety
            $undoMigrations = false;
        }

        // Ask about .env variables
        $removeEnvVars = false;
        if (!$keepEnv && !$force) {
            $this->newLine();
            $removeEnvVars = $this->confirm('Do you wish to remove ATU Rank SEO environment variables from .env and .env.example?', false);
        } elseif ($keepEnv) {
            $removeEnvVars = false;
        } else {
            // In force mode without --keep-env, default to removing env vars
            $removeEnvVars = true;
        }

        // Step 1: Remove files
        $this->step('Removing ATU Rank SEO files and stubs...');
        $touchEnv = $removeEnvVars;
        $results = $installer->uninstall($touchEnv);

        $removedFiles = $results['removed'] ?? [];
        $removedCount = count($removedFiles);

        if ($removedCount > 0) {
            foreach ($removedFiles as $file) {
                $this->line("   ✅ Removed: " . $this->getRelativePath($file));
            }
            $this->info("   ✅ {$removedCount} installed file(s) removed successfully.");
        } else {
            $this->warn('   ⚠️  No installed files found to remove.');
        }

        // Step 2: Environment variables
        $this->step('Cleaning up environment files...');
        if ($removeEnvVars) {
            $this->handleEnvResults($results['env'] ?? []);
        } else {
            $this->line('   ⏭️  Environment keys preserved (skipped by user choice).');
        }

        // Step 3: Routes
        $this->step('Removing routes...');
        $this->handleRoutes($results['routes'] ?? []);

        // Step 4: Remove migrations for ATU Rank SEO
        if ($undoMigrations) {
            $this->step('Rolling back and removing ATU Rank SEO migrations...');
            $this->removeMigrations();
        } else {
            $this->step('Skipping migration rollback...');
            $this->line('   ⏭️  Migrations preserved (skipped by user choice).');
            $this->line('   ⚠️  Note: Migration files and database tables remain. You may need to drop tables manually.');
        }

        // Step 5: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage($removeEnvVars, $undoMigrations);

        return self::SUCCESS;
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
     * Handle environment file results
     */
    private function handleEnvResults(array $envResults): void
    {
        $envCleaned = false;
        $filesChecked = [];

        foreach ($envResults as $file => $keys) {
            $filesChecked[] = basename($file);

            if ($keys !== []) {
                $this->info("   ✅ Removed from " . basename($file) . ": " . implode(', ', $keys));
                $envCleaned = true;
            } else {
                $this->line("   ℹ️  " . basename($file) . " does not contain ATU Rank SEO keys");
            }
        }

        if (empty($filesChecked)) {
            $this->warn('   ⚠️  No .env or .env.example files found.');
        } elseif (!$envCleaned) {
            $this->info('   ✅ No ATU Rank SEO environment keys found to remove.');
        }
    }

    /**
     * Handle routes results
     */
    private function handleRoutes(array $routes): void
    {
        if ($routes !== []) {
            if ($routes['removed'] ?? false) {
                $this->info('   ✅ SEO routes removed from routes/web.php');
            } else {
                $this->info('   ✅ No route block found to remove.');
            }
        }
    }

    /**
     * Clear application caches
     */
    private function clearCaches(): void
    {
        $cacheCommands = [
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($cacheCommands as $command => $description) {
            try {
                \Illuminate\Support\Facades\Artisan::call($command);
                $this->line("   ✅ Cleared: {$description}");
            } catch (\Exception $e) {
                $this->line("   ⚠️  Skipped: {$description} (not available)");
            }
        }
    }

    /**
     * Display the header
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('🗑️  Uninstalling ATU Rank SEO Package...');
        $this->line('   Version: ' . ATURankSEO::VERSION);
        $this->newLine();
    }

    /**
     * Display a step message
     */
    private function step(string $message): void
    {
        $this->info("🗂️  {$message}");
    }

    /**
     * Remove database tables
     */
    private function removeDatabaseTables(): void
    {
        try {
            $prefix = 'atu_rankseo_';

            // Get all tables with ATU Rank SEO prefix
            $tables = DB::select("SHOW TABLES LIKE '{$prefix}%'");

            if (empty($tables)) {
                $this->line('   ℹ️  No ATU Rank SEO tables found.');
                return;
            }

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                $this->line("   ✅ Dropped table: {$tableName}");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('   ✅ Database tables removed successfully.');
        } catch (\Exception $e) {
            $this->error("   ❌ Error removing database tables: " . $e->getMessage());
            $this->warn('   ⚠️  You may need to manually remove the tables.');
        }
    }

    /**
     * Remove migration files
     */
    private function removeMigrations(): void
    {
        // Step 1: Drop database tables directly using SQL (most reliable method)
        $this->removeDatabaseTables();

        // Step 2: Attempt to rollback migrations (for cleanup/verification)
        $migrationPath = database_path('migrations');
        if (!File::isDirectory($migrationPath)) {
            $this->line("   ℹ️  Migrations directory does not exist");
            return;
        }

        $removed = 0;
        $rolledBack = false;

        foreach (File::files($migrationPath) as $file) {
            if (str_contains($file->getFilename(), 'atu_rankseo_')) {
                try {
                    Artisan::call('migrate:rollback', ['--path' => 'database/migrations/' . $file->getFilename(), '--force' => true]);
                    $this->line('   Rolled back migration: ' . $file->getFilename());
                    $rolledBack = true;
                } catch (\Exception $e) {
                    $this->warn('   Could not rollback migration: ' . $file->getFilename() . ' (' . $e->getMessage() . ')');
                }

                // Step 3: Delete migration files
                File::delete($file->getPathname());
                $removed++;
            }
        }

        if ($removed === 0) {
            $this->line("   ℹ️  No ATU Rank SEO migrations found to remove");
            return;
        }

        if (! $rolledBack && $removed > 0) {
            $this->line('   ℹ️  Note: Some migrations could not be rolled back, but tables were dropped directly.');
        }
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage(bool $envRemoved, bool $migrationsUndone): void
    {
        $this->newLine();
        $this->info('🎉 ATU Rank SEO package uninstalled successfully!');
        $this->newLine();

        $this->comment('📋 What was removed:');
        $this->line('   ✅ All ATU Rank SEO copied files and stubs');
        $this->line('   ✅ SEO routes from routes/web.php');
        if ($envRemoved) {
            $this->line('   ✅ ATU Rank SEO environment variables');
        } else {
            $this->line('   ⏭️  Environment variables preserved (skipped by user choice)');
        }
        if ($migrationsUndone) {
            $this->line('   ✅ ATU Rank SEO migrations rolled back and migration files deleted');
        } else {
            $this->line('   ⏭️  Migrations preserved (skipped by user choice)');
        }
        $this->line('   ✅ Application caches cleared');
        $this->newLine();

        $this->comment('📖 Final steps:');
        $this->line('   1. Remove "vormia-folks/atu-rank-seo" from your composer.json');
        $this->line('   2. Run: composer remove vormia-folks/atu-rank-seo');
        if (!$migrationsUndone) {
            $this->line('   3. Manually remove database tables if needed (migrations were not rolled back)');
            $this->line('   4. Review your application for any remaining ATU Rank SEO references');
        } else {
            $this->line('   3. Review your application for any remaining ATU Rank SEO references');
        }
        $this->newLine();

        if (!$envRemoved) {
            $this->warn('⚠️  Note: Environment variables were preserved. Remove them manually if needed.');
            $this->newLine();
        }

        if (!$migrationsUndone) {
            $this->warn('⚠️  Note: Migration files and database tables remain. Remove them manually if needed.');
            $this->newLine();
        }

        $this->info('✨ Thank you for using ATU Rank SEO!');
    }
}
