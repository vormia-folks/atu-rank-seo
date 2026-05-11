<?php

namespace Vormia\ATURankSEO\Console\Commands;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Edit;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Index;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\MediaEdit;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\MediaIndex;
use Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo\Settings;
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

    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    ATU RANK SEO HELP                        ║');
        $this->info('║                    Version '.str_pad(ATURankSEO::VERSION, 25).'║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->comment('🔍 ATU Rank SEO provides snapshot-based SEO management');
        $this->comment('   integrated with Vormia\'s SlugRegistry.');
        $this->newLine();
    }

    private function displayCommands(): void
    {
        $this->info('📋 AVAILABLE COMMANDS:');
        $this->newLine();

        $commands = [
            [
                'command' => 'aturankseo:install',
                'description' => 'Apply .env keys and optionally run migrate / seed (package code stays in vendor)',
                'options' => '--skip-env (leave .env untouched)',
            ],
            [
                'command' => 'aturankseo:update',
                'description' => 'Re-apply .env keys from the installer',
                'options' => '--skip-env (leave .env untouched)',
            ],
            [
                'command' => 'aturankseo:uninstall',
                'description' => 'Remove .env keys and optionally roll back package migrations',
                'options' => '--keep-env (preserve env keys), --force (skip confirmation prompts)',
            ],
            [
                'command' => 'aturankseo:help',
                'description' => 'Display this help information',
                'options' => null,
            ],
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

    private function displayUsageExamples(): void
    {
        $this->info('💡 USAGE EXAMPLES:');
        $this->newLine();

        $examples = [
            [
                'title' => 'Installation',
                'command' => 'php artisan aturankseo:install',
                'description' => 'Add env keys, then prompt for migrate and optional seed',
            ],
            [
                'title' => 'Install (Skip Environment)',
                'command' => 'php artisan aturankseo:install --skip-env',
                'description' => 'Install without modifying .env files',
            ],
            [
                'title' => 'Update env keys',
                'command' => 'php artisan aturankseo:update',
                'description' => 'Append any missing ATU Rank SEO keys to .env / .env.example',
            ],
            [
                'title' => 'Uninstall Package',
                'command' => 'php artisan aturankseo:uninstall',
                'description' => 'Prompt to remove env keys and optionally roll back migrations',
            ],
            [
                'title' => 'Uninstall (Keep Environment)',
                'command' => 'php artisan aturankseo:uninstall --keep-env',
                'description' => 'Uninstall steps without removing environment variables',
            ],
            [
                'title' => 'Force Uninstall',
                'command' => 'php artisan aturankseo:uninstall --force',
                'description' => 'Skip confirmation prompts (defaults: keep migrations, remove env)',
            ],
        ];

        foreach ($examples as $example) {
            $this->line("  <fg=cyan>{$example['title']}:</>");
            $this->line("    <fg=white>{$example['command']}</>");
            $this->line("    <fg=gray>{$example['description']}</>");
            $this->newLine();
        }
    }

    private function displayEnvironmentKeys(): void
    {
        $this->info('⚙️  ENVIRONMENT VARIABLES:');
        $this->newLine();

        $this->line('  <fg=white>These keys may be added to .env and .env.example during installation:</>');
        $this->newLine();

        $envKeys = [
            ['key' => 'ATU_RANKSEO_ENABLED', 'value' => 'true', 'description' => 'Master enable/disable for SEO resolution'],
            ['key' => 'ATU_RANKSEO_CACHE_TTL', 'value' => '3600', 'description' => 'Cache TTL in seconds (mirrors config cache.ttl)'],
            ['key' => 'ATU_RANKSEO_ADMIN_ENABLED', 'value' => 'true', 'description' => 'When false, the package does not register admin Livewire routes'],
        ];

        $this->line('  <fg=cyan># ATU Rank SEO Configuration</>');
        foreach ($envKeys as $env) {
            $value = $env['value'] !== '' ? "={$env['value']}" : '=';
            $this->line("  <fg=white>{$env['key']}{$value}</>");
            $this->line("    <fg=gray>{$env['description']}</>");
        }

        $this->newLine();
    }

    private function displayRoutes(): void
    {
        $this->info('🛣️  ADMIN ROUTES (Livewire 4):');
        $this->newLine();

        $this->line('  <fg=white>Registered by the package when config atu-rank-seo.enabled and atu-rank-seo.admin.enabled are true:</>');
        $this->newLine();

        $index = Index::class;
        $settings = Settings::class;
        $edit = Edit::class;
        $mediaIndex = MediaIndex::class;
        $mediaEdit = MediaEdit::class;

        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo</> → <fg=white>{$index}</>  (name: admin.atu.rank-seo.index)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/settings</> → <fg=white>{$settings}</>  (admin.atu.rank-seo.settings)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/edit/{id}</> → <fg=white>{$edit}</>  (admin.atu.rank-seo.edit)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/media</> → <fg=white>{$mediaIndex}</>  (admin.atu.rank-seo.media.index)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/media/edit/{id}</> → <fg=white>{$mediaEdit}</>  (admin.atu.rank-seo.media.edit)</>");

        $this->newLine();
        $this->line('  <fg=gray>Override middleware or prefix in config/atu-rank-seo.php (atu-rank-seo.admin.*). To disable routes, set ATU_RANKSEO_ADMIN_ENABLED=false.</>');
        $this->line('  <fg=gray>Optional manual wiring: see src/stubs/reference/routes-to-add.php in the package.</>');
        $this->newLine();
    }

    private function displayFooter(): void
    {
        $this->info('📚 ADDITIONAL RESOURCES:');
        $this->newLine();

        $this->line('  <fg=white>Package Repository:</> vormia-folks/atu-rank-seo');
        $this->line('  <fg=white>Livewire:</> https://livewire.laravel.com/docs');

        $this->newLine();
        $this->comment('💡 For more detailed documentation, review the package README and RELEASE_NOTES.');
        $this->newLine();

        $this->info('🎉 Thank you for using ATU Rank SEO!');
        $this->newLine();
    }
}
