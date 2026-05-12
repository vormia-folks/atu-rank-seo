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
                'description' => 'Apply .env keys, copy admin views + append routes/web.php by default, then optional migrate / seed',
                'options' => '--skip-env (skip initial ATU env block merge), --skip-host-copy (package registers routes; no view copy), --force (overwrite copied Blade views)',
            ],
            [
                'command' => 'aturankseo:update',
                'description' => 'Re-apply .env keys from the installer',
                'options' => '--skip-env (leave .env untouched)',
            ],
            [
                'command' => 'aturankseo:uninstall',
                'description' => 'Remove .env keys, optional host routes/web.php block + copied views, and optionally roll back package migrations',
                'options' => '--keep-env (preserve env keys), --keep-host-files (preserve injected web.php + copied rank-seo views), --force (skip confirmation prompts; removes host files unless --keep-host-files)',
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
                'description' => 'Skip merging the default ATU env block; host copy still sets ATU_RANKSEO_ADMIN_ENABLED=false when routes/views are copied',
            ],
            [
                'title' => 'Install (Package-only routes)',
                'command' => 'php artisan aturankseo:install --skip-host-copy',
                'description' => 'Do not copy views or append routes/web.php; package registers admin routes (ATU_RANKSEO_ADMIN_ENABLED stays true if added)',
            ],
            [
                'title' => 'Update env keys',
                'command' => 'php artisan aturankseo:update',
                'description' => 'Append any missing ATU Rank SEO keys to .env / .env.example',
            ],
            [
                'title' => 'Uninstall Package',
                'command' => 'php artisan aturankseo:uninstall',
                'description' => 'Prompt to remove env keys, host web.php / copied views, and optionally roll back migrations',
            ],
            [
                'title' => 'Uninstall (Keep Environment)',
                'command' => 'php artisan aturankseo:uninstall --keep-env',
                'description' => 'Uninstall steps without removing environment variables',
            ],
            [
                'title' => 'Force Uninstall',
                'command' => 'php artisan aturankseo:uninstall --force',
                'description' => 'Skip confirmation prompts (defaults: keep migrations, remove env, remove host routes/views unless --keep-host-files)',
            ],
            [
                'title' => 'Uninstall (Keep host routes and views)',
                'command' => 'php artisan aturankseo:uninstall --keep-host-files',
                'description' => 'Do not strip the ATU Rank SEO web.php block or delete copied rank-seo Blade files',
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
            ['key' => 'ATU_RANKSEO_ADMIN_ENABLED', 'value' => 'true', 'description' => 'When false, the package does not register admin Livewire routes (default install sets false after copying routes to web.php)'],
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

        $this->line('  <fg=white>Registered by the package when config atu-rank-seo.enabled and atu-rank-seo.admin.enabled are true, or by your routes/web.php block after install (env admin false):</>');
        $this->newLine();

        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo</> → <fg=white>rank-seo.index</>  (name: admin.atu.rank-seo.index)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/settings</> → <fg=white>rank-seo.settings</>  (admin.atu.rank-seo.settings)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/edit/{id}</> → <fg=white>rank-seo.edit</>  (admin.atu.rank-seo.edit)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/media</> → <fg=white>rank-seo.media-index</>  (admin.atu.rank-seo.media.index)</>");
        $this->line("  <fg=cyan>GET .../admin/atu/rank-seo/media/edit/{id}</> → <fg=white>rank-seo.media-edit</>  (admin.atu.rank-seo.media.edit)</>");

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
