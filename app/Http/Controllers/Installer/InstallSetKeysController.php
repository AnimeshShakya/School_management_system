<?php

declare(strict_types=1);

namespace App\Http\Controllers\Installer;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InstallSetKeysController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'app_url' => ['required', 'url'],
        ]);

        return redirect()->route('install.finish');
    }
}
