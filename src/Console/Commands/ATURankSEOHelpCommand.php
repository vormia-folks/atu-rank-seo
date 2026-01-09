<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Illuminate\Console\Command;

class ATURankSEOHelpCommand extends Command
{
    protected $signature = 'aturankseo:help';

    protected $description = 'Display help information for ATU Rank SEO package commands';

    public function handle(): int
    {
        $this->displayHeader();
        $this->displayCommands();
        $this->displayUsageExamples();
        $this->displayEnvironmentKeys();
        $this->displayRoutes();
        $this->displayFooter();

        return self::SUCCESS;
    }

    /**
     * Display the header
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    ATU RANK SEO HELP                        ║');
        $this->info('║                    Version ' . str_pad(ATURankSEO::VERSION, 25) . '║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->comment('🔍 ATU Rank SEO provides snapshot-based SEO management');
        $this->comment('   integrated with Vormia\'s SlugRegistry.');
        $this->newLine();
    }

    /**
     * Display available commands
     */
    private function displayCommands(): void
    {
        $this->info('📋 AVAILABLE COMMANDS:');
        $this->newLine();

        $commands = [
            [
                'command' => 'aturankseo:install',
                'description' => 'Install ATU Rank SEO package with all files and configurations',
                'options' => '--no-overwrite (keep existing files), --skip-env (leave .env untouched)'
            ],
            [
                'command' => 'aturankseo:update',
                'description' => 'Update ATU Rank SEO package files and configurations',
                'options' => '--skip-env (leave .env untouched)'
            ],
            [
                'command' => 'aturankseo:uninstall',
                'description' => 'Remove all ATU Rank SEO package files and configurations',
                'options' => '--keep-env (preserve env keys), --force (skip confirmation prompts)'
            ],
            [
                'command' => 'aturankseo:help',
                'description' => 'Display this help information',
                'options' => null
            ]
        ];

        foreach ($commands as $cmd) {
            $this->line("  <fg=green>{$cmd['command']}</>");
            $this->line("    {$cmd['description']}");
            if ($cmd['options']) {
                $this->line("    <fg=yellow>Options:</> {$cmd['options']}");
            }
            $this->newLine();
        }
    }

    /**
     * Display usage examples
     */
    private function displayUsageExamples(): void
    {
        $this->info('💡 USAGE EXAMPLES:');
        $this->newLine();

        $examples = [
            [
                'title' => 'Installation',
                'command' => 'php artisan aturankseo:install',
                'description' => 'Install ATU Rank SEO with all files and configurations'
            ],
            [
                'title' => 'Install (Preserve Existing Files)',
                'command' => 'php artisan aturankseo:install --no-overwrite',
                'description' => 'Install without overwriting existing files'
            ],
            [
                'title' => 'Install (Skip Environment)',
                'command' => 'php artisan aturankseo:install --skip-env',
                'description' => 'Install without modifying .env files'
            ],
            [
                'title' => 'Update Package',
                'command' => 'php artisan aturankseo:update',
                'description' => 'Update package files and configurations'
            ],
            [
                'title' => 'Uninstall Package',
                'command' => 'php artisan aturankseo:uninstall',
                'description' => 'Remove all ATU Rank SEO files and configurations'
            ],
            [
                'title' => 'Uninstall (Keep Environment)',
                'command' => 'php artisan aturankseo:uninstall --keep-env',
                'description' => 'Uninstall but preserve environment variables'
            ],
            [
                'title' => 'Force Uninstall',
                'command' => 'php artisan aturankseo:uninstall --force',
                'description' => 'Uninstall without confirmation prompts'
            ]
        ];

        foreach ($examples as $example) {
            $this->line("  <fg=cyan>{$example['title']}:</>");
            $this->line("    <fg=white>{$example['command']}</>");
            $this->line("    <fg=gray>{$example['description']}</>");
            $this->newLine();
        }
    }

    /**
     * Display environment keys
     */
    private function displayEnvironmentKeys(): void
    {
        $this->info('⚙️  ENVIRONMENT VARIABLES:');
        $this->newLine();

        $this->line('  <fg=white>These keys are added to .env and .env.example during installation:</>');
        $this->newLine();

        $envKeys = [
            ['key' => 'ATU_RANKSEO_ENABLED', 'value' => 'true', 'description' => 'Master enable/disable switch for SEO functionality'],
            ['key' => 'ATU_RANKSEO_CACHE_TTL', 'value' => '3600', 'description' => 'Cache TTL in seconds (default: 1 hour)'],
        ];

        $this->line('  <fg=cyan># ATU Rank SEO Configuration</>');
        foreach ($envKeys as $env) {
            $value = $env['value'] !== '' ? "={$env['value']}" : '=';
            $this->line("  <fg=white>{$env['key']}{$value}</>");
            $this->line("    <fg=gray>{$env['description']}</>");
        }

        $this->newLine();
    }

    /**
     * Display routes information
     */
    private function displayRoutes(): void
    {
        $this->info('🛣️  ADMIN ROUTES:');
        $this->newLine();

        $this->line('  <fg=white>The following route block is added to routes/web.php (commented out by default):</>');
        $this->newLine();

        $this->line('  <fg=cyan>// >>> ATU Rank SEO Routes START</>');
        $this->line('  <fg=cyan>// Route::prefix(\'admin/atu/rank-seo\')->middleware([\'web\', \'auth\'])->group(function () {</>');
        $this->line('  <fg=cyan>//     Route::get(\'/\', \\App\\Livewire\\Admin\\ATU\\RankSeo\\IndexComponent::class)->name(\'admin.atu.rank-seo.index\');</>');
        $this->line('  <fg=cyan>//     Route::get(\'/settings\', \\App\\Livewire\\Admin\\ATU\\RankSeo\\SettingsComponent::class)->name(\'admin.atu.rank-seo.settings\');</>');
        $this->line('  <fg=cyan>//     Route::get(\'/edit/{id}\', \\App\\Livewire\\Admin\\ATU\\RankSeo\\EditComponent::class)->name(\'admin.atu.rank-seo.edit\');</>');
        $this->line('  <fg=cyan>//     Route::get(\'/media\', \\App\\Livewire\\Admin\\ATU\\RankSeo\\MediaIndexComponent::class)->name(\'admin.atu.rank-seo.media.index\');</>');
        $this->line('  <fg=cyan>//     Route::get(\'/media/edit/{id}\', \\App\\Livewire\\Admin\\ATU\\RankSeo\\MediaEditComponent::class)->name(\'admin.atu.rank-seo.media.edit\');</>');
        $this->line('  <fg=cyan>// });</>');
        $this->line('  <fg=cyan>// >>> ATU Rank SEO Routes END</>');

        $this->newLine();
        $this->line('  <fg=gray>Note: Routes are commented out by default. Uncomment and implement as needed.</>');
        $this->newLine();
    }

    /**
     * Display footer
     */
    private function displayFooter(): void
    {
        $this->info('📚 ADDITIONAL RESOURCES:');
        $this->newLine();

        $this->line('  <fg=white>Package Repository:</> vormia-folks/atu-rank-seo');
        $this->line('  <fg=white>Documentation:</> Check docs/ directory in package');

        $this->newLine();
        $this->comment('💡 For more detailed documentation, review the package documentation.');
        $this->newLine();

        $this->info('🎉 Thank you for using ATU Rank SEO!');
        $this->newLine();
    }
}
