<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar;

use Livewire\Livewire;
use Livewire\Volt\Volt;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IntranetAppInventarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('intranet-app-inventar')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations();
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->booted(function (): void {
            Volt::mount(__DIR__.'/../resources/views/livewire');
        });

        Livewire::addNamespace(
            namespace: 'intranet-app-inventar',
            viewPath: __DIR__.'/../resources/views/livewire',
            classNamespace: 'Hwkdo\IntranetAppInventar\Livewire',
            classPath: __DIR__.'/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
