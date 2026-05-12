<?php

namespace Vormia\ATURankSEO;

use Vormia\ATURankSEO\Console\Commands\ATURankSEOHelpCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOInstallCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOUninstallCommand;
use Vormia\ATURankSEO\Console\Commands\ATURankSEOUpdateCommand;
use Vormia\ATURankSEO\Services\MediaIndexerService;
use Vormia\ATURankSEO\Services\SeoCacheService;
use Vormia\ATURankSEO\Services\SeoResolverService;
use Vormia\ATURankSEO\Services\SeoSnapshotService;
use Vormia\ATURankSEO\Support\Installer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ATURankSEOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('aturankseo.version', ATURankSEO::VERSION);

        $this->mergeConfigFrom(__DIR__.'/../config/atu-rank-seo.php', 'atu-rank-seo');

        $this->app->singleton(Installer::class, function (Application $app) {
            return new Installer(
                new Filesystem,
                $app->basePath()
            );
        });

        $this->app->singleton(SeoResolverService::class);
        $this->app->singleton(SeoSnapshotService::class);
        $this->app->singleton(MediaIndexerService::class);
        $this->app->singleton(SeoCacheService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(ATURankSEO::basePath('database/migrations'));

        $hostAtuLivewireViews = $this->app->resourcePath('views/livewire/admin/atu');
        if (is_dir($hostAtuLivewireViews)) {
            Livewire::addLocation(viewPath: $hostAtuLivewireViews);
        }

        Livewire::addLocation(
            viewPath: ATURankSEO::basePath('resources/views/livewire/admin/atu')
        );

        $this->registerAdminRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ATURankSEOInstallCommand::class,
                ATURankSEOUpdateCommand::class,
                ATURankSEOUninstallCommand::class,
                ATURankSEOHelpCommand::class,
            ]);

            $this->publishes([
                ATURankSEO::basePath('config/atu-rank-seo.php') => config_path('atu-rank-seo.php'),
            ], 'aturankseo-config');
        }
    }

    protected function registerAdminRoutes(): void
    {
        if (! config('atu-rank-seo.enabled', true) || ! config('atu-rank-seo.admin.enabled', true)) {
            return;
        }

        $middleware = config('atu-rank-seo.admin.middleware', ['web', 'auth']);
        $prefix = trim((string) config('atu-rank-seo.admin.prefix', 'admin/atu'), '/');

        Route::middleware($middleware)
            ->prefix($prefix)
            ->name('admin.atu.rank-seo.')
            ->group(function () {
                Route::livewire('/rank-seo', 'rank-seo.index')->name('index');
                Route::livewire('/rank-seo/settings', 'rank-seo.settings')->name('settings');
                Route::livewire('/rank-seo/edit/{id}', 'rank-seo.edit')->name('edit');
                Route::livewire('/rank-seo/media', 'rank-seo.media-index')->name('media.index');
                Route::livewire('/rank-seo/media/edit/{id}', 'rank-seo.media-edit')->name('media.edit');
            });
    }
}
