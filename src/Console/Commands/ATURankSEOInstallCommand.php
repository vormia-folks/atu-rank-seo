<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Database\Seeders\ATURankSEOSeeder;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ATURankSEOInstallCommand extends Command
{
    protected $signature = 'aturankseo:install {--skip-env : Do not modify .env files}';

    protected $description = 'Apply ATU Rank SEO setup: optional .env keys, then optional migrate and seed';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $touchEnv = ! $this->option('skip-env');

        $this->step('Applying ATU Rank SEO setup...');
        $results = $installer->install($touchEnv);

        $this->displaySetupResults();

        if ($touchEnv) {
            $this->step('Environment files...');
            $this->displayEnvInstallResults($results['env'] ?? []);
        } else {
            $this->line('   ⏭️  Environment keys skipped (--skip-env flag used).');
        }

        $migrationsRun = $this->handleMigrations();

        if ($migrationsRun) {
            $this->handleSeeders();
        }

        $this->displayCompletionMessage($touchEnv, $migrationsRun);

        return self::SUCCESS;
    }

    private function displaySetupResults(): void
    {
        $this->line('   ℹ️  Models, services, and admin Livewire UI load from the package (Vormia\\ATURankSEO\\*).');
        $this->line('   ℹ️  Optional: php artisan vendor:publish --tag=aturankseo-config');
    }

    /**
     * @param  array<string, array<int, string>>  $envResults
     */
    private function displayEnvInstallResults(array $envResults): void
    {
        $any = false;
        foreach ($envResults as $file => $keys) {
            if ($keys !== []) {
                $this->info('   ✅ Added to '.basename((string) $file).': '.implode(', ', $keys));
                $any = true;
            }
        }
        if (! $any) {
            $this->line('   ℹ️  ATU Rank SEO env keys already present in .env / .env.example (nothing added).');
        }
    }

    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('🚀 Installing ATU Rank SEO Package...');
        $this->line('   Version: '.ATURankSEO::VERSION);
        $this->newLine();
    }

    private function step(string $message): void
    {
        $this->info("📦 {$message}");
    }

    private function handleMigrations(): bool
    {
        $this->step('Running database migrations...');
        $this->line('   ℹ️  ATU Rank SEO migrations load from the package (vendor); `php artisan migrate` records them like any other migration.');

        if (! $this->confirm('Would you like to run migrations now?', true)) {
            $this->line('   ⏭️  Migrations skipped. Run later with: php artisan migrate');

            return false;
        }

        return $this->runMigrations();
    }

    private function runMigrations(): bool
    {
        try {
            $this->line('   Running migrations...');
            $exitCode = Artisan::call('migrate', [], $this->getOutput());

            $output = Artisan::output();
            if (! empty(trim($output))) {
                $this->line($output);
            }

            if ($exitCode === 0) {
                $this->info('   ✅ Migrations completed successfully!');

                return true;
            }

            $this->error('   ❌ Migrations completed with errors (exit code: '.$exitCode.')');
            $this->warn('   ⚠️  You can run migrations manually later with: php artisan migrate');

            return false;
        } catch (\Exception $e) {
            $this->error('   ❌ Migration failed: '.$e->getMessage());
            $this->warn('   ⚠️  You can run migrations manually later with: php artisan migrate');

            return false;
        }
    }

    private function handleSeeders(): void
    {
        $this->step('Running database seeders...');

        if (! $this->confirm('Would you like to seed the default SEO settings now?', true)) {
            $this->line('   ⏭️  Seeders skipped. Run later with: php artisan db:seed --class='.ATURankSEOSeeder::class);

            return;
        }

        $this->runSeeders();
    }

    private function runSeeders(): void
    {
        try {
            $this->line('   Running seeders...');
            $exitCode = Artisan::call('db:seed', [
                '--class' => ATURankSEOSeeder::class,
            ], $this->getOutput());

            $output = Artisan::output();
            if (! empty(trim($output))) {
                $this->line($output);
            }

            if ($exitCode === 0) {
                $this->info('   ✅ Seeders completed successfully!');
            } else {
                $this->error('   ❌ Seeders completed with errors (exit code: '.$exitCode.')');
                $this->warn('   ⚠️  You can run seeders manually later with: php artisan db:seed --class='.ATURankSEOSeeder::class);
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Seeder failed: '.$e->getMessage());
            $this->warn('   ⚠️  You can run seeders manually later with: php artisan db:seed --class='.ATURankSEOSeeder::class);
        }
    }

    private function displayCompletionMessage(bool $envTouched, bool $migrationsRun): void
    {
        $this->newLine();
        $this->info('🎉 ATU Rank SEO package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Optional: php artisan vendor:publish --tag=aturankseo-config');
        $this->line('   2. Ensure Livewire and your admin layout/components (e.g. x-admin-panel) are available.');

        if (! $migrationsRun) {
            $this->line('   3. Run migrations: php artisan migrate');
            $this->line('   4. Run seeders: php artisan db:seed --class='.ATURankSEOSeeder::class);
        } else {
            $this->line('   3. Add sidebar links if needed (see package src/stubs/reference/sidebar-menu-to-add.blade.php).');
        }

        $this->newLine();

        if (! $envTouched) {
            $this->warn('⚠️  Note: Environment keys were not modified (--skip-env flag used).');
            $this->line('   Run: php artisan aturankseo:help to see suggested env keys.');
            $this->newLine();
        }

        $this->comment('📖 For help and available commands, run: php artisan aturankseo:help');
        $this->newLine();

        $this->info('✨ Happy coding with ATU Rank SEO!');
    }
}
