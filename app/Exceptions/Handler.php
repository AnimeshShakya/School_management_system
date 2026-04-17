<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('install/*')) {
                return null;
            }

            $status = 500;
            $message = 'Installer error: ' . $e->getMessage();

            if (config('app.debug')) {
                $message .= "\n\n" . $e->getTraceAsString();
            }

            $acceptsJson = $request->expectsJson() || str_contains((string) $request->header('Accept'), 'application/json');

            if ($acceptsJson) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                ], $status);
            }

            return response('<pre style="white-space: pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; padding:16px;">' . e($message) . '</pre>', $status)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        });
    }
}
