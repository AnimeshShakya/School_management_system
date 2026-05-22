<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'Laravel'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'Asia/Kathmandu',

    'superadmin_email' => env('SUPERADMIN_EMAIL', 'superadmin@gmail.com'),

    'locale' => 'en',

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'providers' => ServiceProvider::defaultProviders()->merge([

        /*
         * Application Service Providers
         */
        Barryvdh\DomPDF\ServiceProvider::class,
        Laravel\Mcp\Server\McpServiceProvider::class,
        Laravel\Boost\BoostServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Form' => App\Facades\Form::class,
        'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
    ])->toArray(),

];
