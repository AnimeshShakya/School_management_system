<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || ! Auth::user()->hasRole('Super Admin')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        $schools = School::orderBy('id', 'DESC')->paginate(10);

        return view('schools.index', compact('schools'));
    }

    public function create()
    {
        return view('schools.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:512',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
            'address' => 'nullable|string|max:1000',
        ]);

        School::create($request->only(['name', 'email', 'phone', 'address']));

        return redirect()->route('schools.index')
            ->with('success', trans('data_store_successfully'));
    }

    public function show($id)
    {
        $school = School::withCount('users')->findOrFail($id);

        return view('schools.show', compact('school'));
    }

    public function edit($id)
    {
        $school = School::findOrFail($id);

        return view('schools.edit', compact('school'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:512',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $school = School::findOrFail($id);
        $school->update($request->only(['name', 'email', 'phone', 'address', 'status']));

        return redirect()->route('schools.index')
            ->with('success', trans('data_update_successfully'));
    }

    public function destroy($id)
    {
        $school = School::findOrFail($id);

        if ($school->users()->exists()) {
            return redirect()->route('schools.index')
                ->with('error', 'Cannot delete school with existing users.');
        }

        $school->delete();

        return redirect()->route('schools.index')
            ->with('success', trans('data_delete_successfully'));
    }
}
