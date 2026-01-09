<?php

namespace Vormia\ATURankSEO;

use Vormia\ATURankSEO\ATURankSEO;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOInstallCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOUninstallCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOUpdateCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOHelpCommand;
use Vormia\ATURankSEO\Support\Installer;
use Vormia\ATURankSEO\Services\SeoResolverService;
use Vormia\ATURankSEO\Services\SeoSnapshotService;
use Vormia\ATURankSEO\Services\MediaIndexerService;
use Vormia\ATURankSEO\Services\SeoCacheService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class ATURankSEOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register version instance
        $this->app->instance('aturankseo.version', ATURankSEO::VERSION);

        // Register Installer as singleton
        $this->app->singleton(Installer::class, function (Application $app) {
            return new Installer(
                new Filesystem(),
                ATURankSEO::stubsPath(),
                $app->basePath()
            );
        });

        // Register services as singletons
        $this->app->singleton(SeoResolverService::class);
        $this->app->singleton(SeoSnapshotService::class);
        $this->app->singleton(MediaIndexerService::class);
        $this->app->singleton(SeoCacheService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ATURankSEOInstallCommand::class,
                ATURankSEOUpdateCommand::class,
                ATURankSEOUninstallCommand::class,
                ATURankSEOHelpCommand::class,
            ]);
        }
    }
}
