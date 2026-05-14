<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\Installer\InstallSetDatabaseController as AppInstallSetDatabaseController;
use App\Http\Controllers\Installer\InstallSetKeysController as AppInstallSetKeysController;
use App\Http\Controllers\Installer\InstallSetMigrationsController as AppInstallSetMigrationsController;
use App\Services\FormService;
use App\Services\NepaliDateService;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetDatabaseController as VendorInstallSetDatabaseController;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetKeysController as VendorInstallSetKeysController;
use dacoto\LaravelWizardInstaller\Controllers\InstallSetMigrationsController as VendorInstallSetMigrationsController;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // -----------------------------------------------
        // Form Service Singleton
        // -----------------------------------------------
        $this->app->singleton('form', function ($app) {
            return new FormService;
        });

        // -----------------------------------------------
        // Nepali Date Service Singleton
        // -----------------------------------------------
        $this->app->singleton(NepaliDateService::class, fn () => new NepaliDateService);

        // -----------------------------------------------
        // Bind Vendor Installer Controllers to App Controllers
        // -----------------------------------------------
        $this->app->bind(VendorInstallSetDatabaseController::class, AppInstallSetDatabaseController::class);
        $this->app->bind(VendorInstallSetMigrationsController::class, AppInstallSetMigrationsController::class);
        $this->app->bind(VendorInstallSetKeysController::class, AppInstallSetKeysController::class);

        // -----------------------------------------------
        // Debugbar — Register only in local/dev (Copilot fix)
        // Safe: checks environment + class existence
        // -----------------------------------------------
        if (
            $this->app->environment('local', 'development')
            && class_exists(\Barryvdh\Debugbar\ServiceProvider::class)
        ) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // -----------------------------------------------
        // Set default string length for MySQL compatibility
        // -----------------------------------------------
        Schema::defaultStringLength(191);

        // -----------------------------------------------
        // Use Bootstrap 4 pagination views
        // -----------------------------------------------
        Paginator::useBootstrapFour();

        // -----------------------------------------------
        // Create storage symlink without exec() (Hostinger fix)
        // Hostinger shared hosting disables exec(), so
        // `php artisan storage:link` fails at runtime.
        // PHP's native symlink() is a direct syscall and works fine.
        // -----------------------------------------------
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (! file_exists($linkPath) && ! is_link($linkPath)) {
            symlink($targetPath, $linkPath);
        }
    }
}
