<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Console\Command;

class ATURankSEOUpdateCommand extends Command
{
    protected $signature = 'aturankseo:update {--skip-env : Do not modify .env files}';

    protected $description = 'Re-apply ATU Rank SEO .env keys (package code updates with Composer)';

    public function handle(Installer $installer): int
    {
        $this->displayHeader();

        $touchEnv = ! $this->option('skip-env');

        $this->step('Updating ATU Rank SEO setup...');
        $results = $installer->update($touchEnv);

        if ($touchEnv) {
            $this->step('Environment files...');
            $this->displayEnvInstallResults($results['env'] ?? []);
        } else {
            $this->line('   ⏭️  Environment keys skipped (--skip-env flag used).');
        }

        $this->newLine();
        $this->info('✅ ATU Rank SEO package configuration refreshed.');
        $this->line('   If you changed config, run: php artisan config:clear');
        $this->newLine();

        return self::SUCCESS;
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
            $this->line('   ℹ️  ATU Rank SEO env keys already present (nothing added).');
        }
    }

    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('🔄 Updating ATU Rank SEO Package...');
        $this->line('   Version: '.ATURankSEO::VERSION);
        $this->newLine();
    }

    private function step(string $message): void
    {
        $this->info("📦 {$message}");
    }
}
