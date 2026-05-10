<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Implicitly grant "Super Admin" role (or a fallback superadmin email) all permissions.
        // This ensures UI checks like @can, @hasrole and auth()->user()->hasRole('Super Admin')
        // behave as expected even if the 'Super Admin' role record is missing from the DB.
        Gate::before(function ($user, $ability) {
            try {
                // If user actually has the Super Admin role, grant everything.
                if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                    return true;
                }

                // Fallback: allow a seeded superadmin email to act as super admin.
                // Configure SUPERADMIN_EMAIL in .env if you need a different email.
                $superAdminEmail = env('SUPERADMIN_EMAIL', 'superadmin@gmail.com');
                if (!empty($user->email) && strcasecmp($user->email, $superAdminEmail) === 0) {
                    return true;
                }
            } catch (\Throwable $e) {
                // In case of any unexpected error, do not block authorization checks — return null to continue normal checks.
            }

            return null;
        });
    }
}
