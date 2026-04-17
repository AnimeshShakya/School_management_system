<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\FormService;
use App\Http\Controllers\Installer\InstallSetDatabaseController as AppInstallSetDatabaseController;
use App\Http\Controllers\Installer\InstallSetKeysController as AppInstallSetKeysController;
use App\Http\Controllers\Installer\InstallSetMigrationsController as AppInstallSetMigrationsController;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetDatabaseController as VendorInstallSetDatabaseController;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetKeysController as VendorInstallSetKeysController;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetMigrationsController as VendorInstallSetMigrationsController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('form', function ($app) {
            return new FormService();
        });

        $this->app->bind(VendorInstallSetDatabaseController::class, AppInstallSetDatabaseController::class);
        $this->app->bind(VendorInstallSetMigrationsController::class, AppInstallSetMigrationsController::class);
        $this->app->bind(VendorInstallSetKeysController::class, AppInstallSetKeysController::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
    }
}
