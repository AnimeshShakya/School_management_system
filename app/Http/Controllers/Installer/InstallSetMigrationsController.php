<?php

declare(strict_types=1);

namespace App\Http\Controllers\Installer;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class InstallSetMigrationsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--seed' => config('installer.database.seeders', false),
            ]);

            return redirect()->route('install.keys');
        } catch (Throwable $e) {
            if ($this->isTableAlreadyExistsError($e->getMessage())) {
                return redirect()->route('install.keys')->with('warning', 'Some tables already exist in this database, so migration step was skipped.');
            }

            return back()->withErrors('Migration failed: ' . $e->getMessage())->withInput();
        }
    }

    private function isTableAlreadyExistsError(string $message): bool
    {
        return str_contains($message, 'SQLSTATE[42S01]')
            || str_contains($message, ' 1050 ')
            || str_contains(strtolower($message), 'already exists');
    }
}
