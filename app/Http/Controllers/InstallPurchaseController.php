<?php

namespace App\Http\Controllers;

use dacoto\EnvSet\Facades\EnvSet;
use dacoto\LaravelWizardInstaller\Controllers\InstallFolderController;
use dacoto\LaravelWizardInstaller\Controllers\InstallServerController;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstallPurchaseController extends Controller {
    /**
     * Set database settings
     *
     * @return Application|Factory|RedirectResponse|View
     */
    public function index() {
        if ((new InstallServerController())->check() === false || (new InstallFolderController())->check() === false) {
            return redirect()->route('install.folders');
        }
        return view('installer::steps.purchase');
    }

    /**
     * Test database and set keys in .env
     *
     * @param  Request  $request
     * @return Application|Factory|RedirectResponse|View
     */
    public function setPurchase(Request $request) {
        try {
            EnvSet::setKey('APPSECRET', Str::random(32));
            EnvSet::save();
            return redirect()->route('install.database');
        } catch (Exception $e) {
            Log::error('Install step failed while setting APPSECRET', [
                'message' => $e->getMessage(),
            ]);
            return view('installer::steps.purchase', ['error' => 'Unable to continue installer. Please try again.']);
        }
    }
}
