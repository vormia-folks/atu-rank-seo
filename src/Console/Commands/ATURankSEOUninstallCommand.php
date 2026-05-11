<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ATURankSEOUninstallCommand extends Command
{
    protected $signature = 'aturankseo:uninstall {--keep-env : Leave env keys untouched} {--force : Skip confirmation prompts}';

    protected $description = 'Remove ATU Rank SEO env keys and optionally roll back package migrations';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $force = $this->option('force');
        $keepEnv = $this->option('keep-env');

        $this->error('⚠️  This prepares removal of ATU Rank SEO from your application.');
        $this->warn('   • Optional: remove ATU Rank SEO environment variables');
        $this->warn('   • Optional: roll back package migrations (deletes data in ATU Rank SEO tables)');
        $this->warn('   • Admin routes are registered by the package; remove the Composer package to stop them');
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

        $this->displayCompletionMessage($removeEnvVars, $undoMigrations);

        return self::SUCCESS;
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

    private function displayCompletionMessage(bool $envRemoved, bool $migrationsUndone): void
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
