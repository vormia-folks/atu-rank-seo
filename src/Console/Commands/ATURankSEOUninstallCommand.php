<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ATURankSEOUninstallCommand extends Command
{
    protected $signature = 'aturankseo:uninstall
                            {--keep-env : Leave env keys untouched}
                            {--keep-host-files : Do not remove injected routes/web.php block or copied rank-seo views}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Remove ATU Rank SEO env keys, optional host routes/views, and optionally roll back package migrations';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $force = $this->option('force');
        $keepEnv = $this->option('keep-env');
        $keepHostFiles = $this->option('keep-host-files');

        $this->error('⚠️  This prepares removal of ATU Rank SEO from your application.');
        $this->warn('   • Optional: remove ATU Rank SEO environment variables');
        $this->warn('   • Optional: roll back package migrations (deletes data in ATU Rank SEO tables)');
        $this->warn('   • Optional: remove the ATU Rank SEO block from routes/web.php and copied Livewire views (unless --keep-host-files)');
        $this->warn('   • Composer packages are NOT removed by this command');
        $this->newLine();

        if (! $force && ! $this->confirm('Are you absolutely sure you want to continue?', false)) {
            $this->info('❌ Uninstall cancelled.');

            return self::SUCCESS;
        }

        $undoMigrations = false;
        if (! $force) {
            $this->newLine();
            $this->error('⚠️  Rolling back migrations will DELETE ALL DATA in ATU Rank SEO database tables.');
            $undoMigrations = $this->confirm('Do you wish to roll back package migrations now?', false);
        }

        $removeEnvVars = false;
        if (! $keepEnv && ! $force) {
            $this->newLine();
            $removeEnvVars = $this->confirm('Do you wish to remove ATU Rank SEO environment variables from .env and .env.example?', false);
        } elseif ($keepEnv) {
            $removeEnvVars = false;
        } else {
            $removeEnvVars = true;
        }

        $removeHostAssets = false;
        if ($keepHostFiles) {
            $removeHostAssets = false;
        } elseif ($force) {
            $removeHostAssets = true;
        } else {
            $this->newLine();
            $removeHostAssets = $this->confirm('Remove the injected routes/web.php block (ATU Rank SEO markers) and copied rank-seo Blade files from your app?', false);
        }

        $hostUninstall = null;
        if ($removeHostAssets) {
            $this->step('Removing host admin routes and copied views...');
            $hostUninstall = $installer->uninstallHostAdminAssets(ATURankSEO::basePath());
            $this->displayHostUninstallResults($hostUninstall);
        } else {
            $this->step('Skipping host routes/views removal...');
            if ($keepHostFiles) {
                $this->line('   ⏭️  Preserved (--keep-host-files).');
            } else {
                $this->line('   ⏭️  Skipped by user choice.');
            }
        }

        $touchEnv = $removeEnvVars;
        $results = $installer->uninstall($touchEnv);

        $this->step('Cleaning up environment files...');
        if ($removeEnvVars) {
            $this->handleEnvResults($results['env'] ?? []);
        } else {
            $this->line('   ⏭️  Environment keys preserved (skipped by user choice).');
        }

        if ($undoMigrations) {
            $this->step('Rolling back package migrations...');
            $this->rollbackPackageMigrations();
        } else {
            $this->step('Skipping migration rollback...');
            $this->line('   ⏭️  Migrations preserved (skipped by user choice).');
        }

        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage($removeEnvVars, $undoMigrations, $removeHostAssets, $hostUninstall);

        return self::SUCCESS;
    }

    /**
     * @param  array{
     *     routes: array{removed: bool, reason?: string},
     *     views: array{deleted: array<int, string>, missing: array<int, string>, reason?: string},
     *     env_admin_restored: array<string, bool>
     * }  $hostUninstall
     */
    private function displayHostUninstallResults(array $hostUninstall): void
    {
        $routes = $hostUninstall['routes'] ?? [];
        if (($routes['removed'] ?? false) === true) {
            $this->info('   ✅ Removed ATU Rank SEO route block from routes/web.php');
        } else {
            $this->line('   ℹ️  routes/web.php: '.($routes['reason'] ?? 'route block not removed'));
        }

        $views = $hostUninstall['views'] ?? [];
        foreach ($views['deleted'] ?? [] as $file) {
            $this->info('   ✅ Deleted copied view: '.$file);
        }
        if (($views['deleted'] ?? []) === []) {
            if (! empty($views['reason'] ?? null)) {
                $this->line('   ℹ️  Views: '.$views['reason']);
            } else {
                $this->line('   ℹ️  No copied rank-seo Blade files removed (already absent or not installed).');
            }
        }

        foreach ($hostUninstall['env_admin_restored'] ?? [] as $path => $changed) {
            if ($changed) {
                $this->info('   ✅ Set ATU_RANKSEO_ADMIN_ENABLED=true in '.basename((string) $path).' (package may register admin routes again).');
            }
        }
    }

    /**
     * @param  array<string, array<int, string>>  $envResults
     */
    private function handleEnvResults(array $envResults): void
    {
        $envCleaned = false;
        $filesChecked = [];

        foreach ($envResults as $file => $keys) {
            $filesChecked[] = basename((string) $file);

            if ($keys !== []) {
                $this->info('   ✅ Removed from '.basename((string) $file).': '.implode(', ', $keys));
                $envCleaned = true;
            } else {
                $this->line('   ℹ️  '.basename((string) $file).' does not contain ATU Rank SEO keys');
            }
        }

        if ($filesChecked === []) {
            $this->warn('   ⚠️  No .env or .env.example files found.');
        } elseif (! $envCleaned) {
            $this->info('   ✅ No ATU Rank SEO environment keys found to remove.');
        }
    }

    private function rollbackPackageMigrations(): void
    {
        $abs = realpath(ATURankSEO::basePath('database/migrations'));
        if ($abs === false) {
            $this->warn('   Could not resolve package migrations directory.');

            return;
        }

        $base = realpath(base_path());
        if ($base === false || ! str_starts_with($abs, $base)) {
            $this->warn('   Package migrations are outside the application base path. Run manually, e.g.:');
            $this->line('   php artisan migrate:rollback --path='.ATURankSEO::basePath('database/migrations'));

            return;
        }

        $relative = ltrim(str_replace('\\', '/', substr($abs, strlen($base))), '/');

        try {
            $exitCode = Artisan::call('migrate:rollback', [
                '--path' => $relative,
                '--force' => true,
            ], $this->getOutput());

            $output = Artisan::output();
            if (! empty(trim($output))) {
                $this->line($output);
            }

            if ($exitCode === 0) {
                $this->info('   ✅ migrate:rollback completed for the package migration path.');
            } else {
                $this->warn('   migrate:rollback exited with code '.$exitCode.'. You may need to adjust batches or run manually.');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ '.$e->getMessage());
        }
    }

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
                Artisan::call($command);
                $this->line("   ✅ Cleared: {$description}");
            } catch (\Exception $e) {
                $this->line("   ⚠️  Skipped: {$description} (not available)");
            }
        }
    }

    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('🗑️  Uninstalling ATU Rank SEO Package...');
        $this->line('   Version: '.ATURankSEO::VERSION);
        $this->newLine();
    }

    private function step(string $message): void
    {
        $this->info("🗂️  {$message}");
    }

    /**
     * @param  array{
     *     routes: array{removed: bool, reason?: string},
     *     views: array{deleted: array<int, string>, missing: array<int, string>, reason?: string},
     *     env_admin_restored: array<string, bool>
     * }|null  $hostUninstall
     */
    private function displayCompletionMessage(bool $envRemoved, bool $migrationsUndone, bool $hostRemovalChosen, ?array $hostUninstall): void
    {
        $this->newLine();
        $this->info('🎉 ATU Rank SEO uninstall steps completed.');
        $this->newLine();

        $this->comment('📋 Summary:');
        if ($envRemoved) {
            $this->line('   ✅ ATU Rank SEO environment variables removed (where present)');
        } else {
            $this->line('   ⏭️  Environment variables preserved');
        }
        if ($migrationsUndone) {
            $this->line('   ✅ migrate:rollback was run for the package migration directory');
        } else {
            $this->line('   ⏭️  Database migrations not rolled back');
        }
        if ($hostRemovalChosen && $hostUninstall !== null) {
            if (($hostUninstall['routes']['removed'] ?? false) === true) {
                $this->line('   ✅ Host routes/web.php ATU Rank SEO block removed');
            } else {
                $this->line('   ℹ️  Host routes/web.php unchanged: '.($hostUninstall['routes']['reason'] ?? 'unknown'));
            }
            $deleted = $hostUninstall['views']['deleted'] ?? [];
            if ($deleted !== []) {
                $this->line('   ✅ Copied rank-seo views removed ('.count($deleted).' file(s))');
            }
        } elseif (! $hostRemovalChosen) {
            $this->line('   ⏭️  Host routes/views cleanup skipped or not selected');
        }
        $this->line('   ✅ Application caches cleared');
        $this->newLine();

        $this->comment('📖 Final steps:');
        $this->line('   1. Remove "vormia-folks/atu-rank-seo" from composer.json if you are done');
        $this->line('   2. Run: composer remove vormia-folks/atu-rank-seo');
        $this->line('   3. Remove any sidebar links you added from reference snippets');
        if (! $migrationsUndone) {
            $this->line('   4. If needed: php artisan migrate:rollback --path=…/vendor/…/database/migrations');
        }
        $this->newLine();

        if (! $envRemoved) {
            $this->warn('⚠️  Environment variables were preserved. Remove them manually if needed.');
            $this->newLine();
        }

        $this->info('✨ Thank you for using ATU Rank SEO!');
    }
}
