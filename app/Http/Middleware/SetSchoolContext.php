<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetSchoolContext
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->school_id) {
            $schoolId = (int) Auth::user()->school_id;
            app()->instance('current_school_id', $schoolId);

            if (! app()->bound('current_school')) {
                $school = School::find($schoolId);
                app()->instance('current_school', $school);
            }
        }

        return $next($request);
    }
}
