<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResponseService
{
    /**
     * @return Application|RedirectResponse|Redirector|true
     */
    public static function noPermissionThenRedirect($permission)
    {
        if (! Auth::user()->can($permission)) {
            return redirect(route('home'))->withErrors([
                'message' => trans("You Don't have enough permissions"),
            ])->send();
        }

        return true;
    }

    /**
     * @return true
     */
    public static function noPermissionThenSendJson($permission)
    {
        if (! Auth::user()->can($permission)) {
            self::errorResponse("You Don't have enough permissions");
        }

        return true;
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector|true
     */
    // Check user role
    public static function noRoleThenRedirect($role)
    {
        if (! Auth::user()->hasRole($role)) {
            return redirect(route('home'))->withErrors([
                'message' => trans("You Don't have enough permissions"),
            ])->send();
        }

        return true;
    }

    /**
     * @return bool|Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public static function noAnyRoleThenRedirect(array $role)
    {
        if (! Auth::user()->hasAnyRole($role)) {
            return redirect(route('home'))->withErrors([
                'message' => trans("You Don't have enough permissions"),
            ])->send();
        }

        return true;
    }

    //    /**
    //     * @param $role
    //     * @return true
    //     */
    //    public static function noRoleThenSendJson($role)
    //    {
    //        if (!Auth::user()->hasRole($role)) {
    //            self::errorResponse("You Don't have enough permissions");
    //        }
    //        return true;
    //    }

    /**
     * @return RedirectResponse|true
     */
    // Check Feature
    public static function noFeatureThenRedirect($feature)
    {
        if (Auth::user()->school_id && ! app(FeaturesService::class)->hasFeature($feature)) {
            return redirect()->back()->withErrors([
                'message' => trans('Purchase').' '.trans($feature).' '.trans('to Continue using this functionality'),
            ])->send();
        }

        return true;
    }

    public static function noFeatureThenSendJson($feature)
    {
        if (Auth::user()->school_id && ! app(FeaturesService::class)->hasFeature($feature)) {
            self::errorResponse(trans('Purchase').' '.trans($feature).' '.trans('to Continue using this functionality'));
        }

        return true;
    }

    /**
     * If User don't have any of the permission that is specified in Array then Redirect will happen
     *
     * @return RedirectResponse|true
     */
    public static function noAnyPermissionThenRedirect(array $permissions)
    {
        if (! Auth::user()->canany($permissions)) {
            return redirect()->back()->withErrors([
                'message' => trans("You Don't have enough permissions"),
            ])->send();
        }

        return true;
    }

    /**
     * If User don't have any of the permission that is specified in Array then Json Response will be sent
     *
     * @return true
     */
    public static function noAnyPermissionThenSendJson(array $permissions)
    {
        if (! Auth::user()->canany($permissions)) {
            self::errorResponse("You Don't have enough permissions");
        }

        return true;
    }

    /**
     * @return JsonResponse
     */
    public static function successResponse(string $message = 'Success', $data = null, array $customData = [], $code = null, array $meta = [])
    {
        // Merge customData into data to enforce a consistent envelope
        if (! empty($customData)) {
            if (is_null($data)) {
                $data = $customData;
            } elseif (is_array($data)) {
                $data = array_merge($data, $customData);
            }
        }

        // Auto-extract pagination meta from paginator objects
        if ($data instanceof AbstractPaginator && empty($meta)) {
            $meta = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
            $data = $data->items();
        }

        $response = [
            'status' => true,
            'message' => trans($message),
            'data' => $data,
            'code' => $code ?? 200,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public static function successRedirectResponse(string $message = 'success', $url = null)
    {
        return isset($url) ? redirect($url)->with([
            'success' => trans($message),
        ])->send() : redirect()->back()->with([
            'success' => trans($message),
        ])->send();
    }

    /**
     * @param  string  $message  - Pass the Translatable Field
     * @param  null  $data
     * @param  null  $code
     * @param  null  $e
     * @return JsonResponse
     */
    public static function errorResponse(string $message = 'Error Occurred', $data = null, $code = null, $e = null)
    {
        if ($e) {
            self::logErrorResponse($e);
            self::logCurlRequest();
        }

        return response()->json([
            'status' => false,
            'message' => trans($message),
            'data' => $data,
            'code' => $code ?? config('constants.RESPONSE_CODE.EXCEPTION_ERROR'),
            'details' => (config('app.debug') && ! empty($e) && is_object($e)) ? $e->getMessage() : '',
        ]);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public static function errorRedirectResponse($url = null, string $message = 'Error Occurred')
    {
        return (($url != null) ? redirect($url) : redirect()->back())->withErrors([
            'message' => trans($message),
        ])->send();
    }

    /**
     * @param  null  $data
     * @param  null  $code
     * @return JsonResponse
     */
    public static function warningResponse(string $message = 'Error Occurred', $data = null, $code = null)
    {
        return response()->json([
            'status' => false,
            'warning' => true,
            'code' => $code,
            'message' => trans($message),
            'data' => $data,
        ]);
    }

    /**
     * @param  null  $data
     * @return JsonResponse
     */
    public static function validationError(string $message = 'Error Occurred', $data = null)
    {
        return self::errorResponse($message, $data, config('constants.RESPONSE_CODE.VALIDATION_ERROR'));
    }

    /**
     * @param  string  $logMessage
     * @param  string  $responseMessage
     * @param  bool  $jsonResponse
     * @return void
     */
    public static function logErrorResponse(Throwable|Exception $e)
    {
        report($e);
        $token = request()->bearerToken();
        $redactedToken = $token ? substr($token, 0, 8).'...' : 'none';

        Log::error($e->getMessage().'---> '.$e->getFile().' At Line : '.$e->getLine()."\n\n".request()->method().' : '.request()->fullUrl()."\nToken : ".$redactedToken."\nParams : ", request()->all());
    }

    public static function logCurlRequest()
    {
        $request = request();
        // Log::error("CURL Request:\n", $request->all());
        $method = strtoupper($request->method());
        $url = $request->fullUrl();
        $headers = [];

        foreach ($request->headers->all() as $key => $values) {
            foreach ($values as $value) {
                $headers[] = "-H '".$key.': '.$value."'";
            }
        }

        $data = '';

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Multipart (e.g., file upload)
            $parts = [];
            foreach ($request->input() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $kk => $vv) {
                                $parts[] = "-F '{$key}[{$k}][{$kk}]={$vv}'";
                            }
                        } else {
                            $parts[] = "-F '{$key}[{$k}]={$v}'";
                        }
                    }
                } else {
                    $parts[] = "-F '{$key}={$value}'";
                }
            }

            foreach ($request->files->all() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $kk => $vv) {
                                $parts[] = "-F '{$key}[{$k}][{$kk}]={$vv}'";
                            }
                        } else {
                            $parts[] = "-F '{$key}[{$k}]={$v}'";
                        }
                    }
                } else {
                    $parts[] = "-F '{$key}={$value}'";
                }
            }

            $data = ' '.implode(" \\\n  ", $parts);
        }

        $curl = "curl -X {$method} '".$url."' \\\n  ".implode(" \\\n  ", $headers).$data;

        Log::error("CURL Request:\n".$curl);
    }
}
