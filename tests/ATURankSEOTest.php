<?php

namespace Vormia\ATURankSEO\Tests;

use PHPUnit\Framework\TestCase;
use Vormia\ATURankSEO\ATURankSEO;

class ATURankSEOTest extends TestCase
{
    public function test_version_matches_semver_patch(): void
    {
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', ATURankSEO::VERSION);
    }

    public function test_base_path_resolves_to_package_root(): void
    {
        $expected = realpath(__DIR__.'/..');
        $this->assertNotFalse($expected);
        $this->assertSame($expected, realpath(ATURankSEO::basePath()));
    }

    public function test_base_path_suffix_resolves_package_config_file(): void
    {
        $config = realpath(ATURankSEO::basePath('config/atu-rank-seo.php'));
        $this->assertNotFalse($config);
        $this->assertFileExists($config);
    }

    public function test_stubs_path_resolves_to_src_stubs_directory(): void
    {
        $stubs = realpath(ATURankSEO::stubsPath());
        $this->assertNotFalse($stubs);
        $this->assertDirectoryExists($stubs);
        $this->assertSame(realpath(__DIR__.'/../src/stubs'), $stubs);
    }
}
