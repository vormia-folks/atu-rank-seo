<?php

namespace Vormia\ATURankSEO\Tests;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Support\Installer;

class InstallerHostAssetsTest extends TestCase
{
    private function tempAppPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'atu_rankseo_installer_test_'.uniqid('', true);
    }

    public function test_build_web_routes_block_contains_markers_and_livewire_routes(): void
    {
        $fs = new Filesystem;
        $installer = new Installer($fs, $this->tempAppPath());

        $block = $installer->buildWebRoutesBlock(['web', 'auth'], 'admin/atu');

        $this->assertStringContainsString(Installer::WEB_ROUTES_MARKER_START, $block);
        $this->assertStringContainsString(Installer::WEB_ROUTES_MARKER_END, $block);
        $this->assertStringContainsString("->prefix('admin/atu')", $block);
        $this->assertStringContainsString("Route::livewire('/rank-seo', 'rank-seo.index')", $block);
    }

    public function test_append_rank_seo_routes_to_web_php_is_idempotent(): void
    {
        $base = $this->tempAppPath();
        $fs = new Filesystem;

        try {
            $fs->ensureDirectoryExists($base.'/routes');
            $fs->put($base.'/routes/web.php', "<?php\n\n");
            $installer = new Installer($fs, $base);

            $first = $installer->appendRankSeoRoutesToWebPhp(['web', 'auth'], 'admin/atu');
            $this->assertTrue($first['appended']);
            $this->assertFalse($first['skipped']);

            $second = $installer->appendRankSeoRoutesToWebPhp(['web', 'auth'], 'admin/atu');
            $this->assertFalse($second['appended']);
            $this->assertTrue($second['skipped']);

            $content = $fs->get($base.'/routes/web.php');
            $this->assertEquals(1, substr_count($content, Installer::WEB_ROUTES_MARKER_START));
        } finally {
            $fs->deleteDirectory($base);
        }
    }

    public function test_copy_rank_seo_views_from_package_copies_blades(): void
    {
        $base = $this->tempAppPath();
        $fs = new Filesystem;

        try {
            $installer = new Installer($fs, $base);
            $result = $installer->copyRankSeoViewsFromPackage(ATURankSEO::basePath(), false);

            $this->assertNotEmpty($result['copied']);
            $this->assertContains('index.blade.php', $result['copied']);
            $this->assertFileExists($base.'/resources/views/livewire/admin/atu/rank-seo/index.blade.php');

            $again = $installer->copyRankSeoViewsFromPackage(ATURankSEO::basePath(), false);
            $this->assertSame([], $again['copied']);
            $this->assertContains('index.blade.php', $again['skipped']);
        } finally {
            $fs->deleteDirectory($base);
        }
    }

    public function test_set_env_key_in_host_files_updates_line(): void
    {
        $base = $this->tempAppPath();
        $fs = new Filesystem;

        try {
            $fs->ensureDirectoryExists($base);
            $envPath = $base.'/.env';
            $fs->put($envPath, "FOO=bar\nATU_RANKSEO_ADMIN_ENABLED=true\n");

            $installer = new Installer($fs, $base);
            $changed = $installer->setEnvKeyInHostFiles('ATU_RANKSEO_ADMIN_ENABLED', 'false');

            $this->assertTrue($changed[$envPath]);
            $this->assertStringContainsString('ATU_RANKSEO_ADMIN_ENABLED=false', $fs->get($envPath));
            $this->assertStringNotContainsString('ATU_RANKSEO_ADMIN_ENABLED=true', $fs->get($envPath));
        } finally {
            $fs->deleteDirectory($base);
        }
    }

    public function test_install_host_admin_assets_does_not_disable_admin_env_when_web_php_missing(): void
    {
        $base = $this->tempAppPath();
        $fs = new Filesystem;

        try {
            $fs->ensureDirectoryExists($base);
            $fs->put($base.'/.env', "ATU_RANKSEO_ADMIN_ENABLED=true\n");

            $installer = new Installer($fs, $base);
            $result = $installer->installHostAdminAssets(ATURankSEO::basePath(), ['web', 'auth'], 'admin/atu', false);

            $this->assertFalse($result['routes']['appended']);
            $this->assertFalse($result['routes']['skipped']);
            $this->assertSame('routes/web.php not found', $result['routes']['reason'] ?? null);
            $this->assertSame([], $result['env_admin_disabled']);
            $this->assertStringContainsString('ATU_RANKSEO_ADMIN_ENABLED=true', $fs->get($base.'/.env'));
        } finally {
            $fs->deleteDirectory($base);
        }
    }
}
