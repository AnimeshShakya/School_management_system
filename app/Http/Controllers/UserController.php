<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:staff-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:staff-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:staff-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:staff-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles', 'school');

        if (! Auth::user()->hasRole('Super Admin')) {
            $query->where('school_id', Auth::user()->school_id);
        }

        $data = $query->orderBy('id', 'DESC')->paginate(10);

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rolesQuery = Role::query();

        if (! Auth::user()->hasRole('Super Admin')) {
            $rolesQuery->where('name', '!=', 'Super Admin');
        }

        $roles = $rolesQuery->pluck('name', 'name')->all();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|exists:roles,name',
        ]);

        if (! Auth::user()->hasRole('Super Admin') && in_array('Super Admin', $request->input('roles', []))) {
            abort(403);
        }

        $input = $request->only(['first_name', 'last_name', 'email']);
        $input['password'] = Hash::make((string) $request->input('password'));
        $input['created_by'] = Auth::id();

        if (! Auth::user()->hasRole('Super Admin')) {
            $input['school_id'] = Auth::user()->school_id;
        } else {
            $input['school_id'] = $request->input('school_id');
        }

        $user = User::create($input);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')
            ->with('success', trans('data_store_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $query = User::with('roles');

        if (! Auth::user()->hasRole('Super Admin')) {
            $query->where('school_id', Auth::user()->school_id);
        }

        $user = $query->with('roles', 'school')->findOrFail($id);

        $rolesQuery = Role::query();

        if (! Auth::user()->hasRole('Super Admin')) {
            $rolesQuery->where('name', '!=', 'Super Admin');
        }

        $roles = $rolesQuery->pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        return $this->edit($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'current_password' => 'nullable|string|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|exists:roles,name',
        ]);

        if (! Auth::user()->hasRole('Super Admin') && in_array('Super Admin', $request->input('roles', []))) {
            abort(403);
        }

        $input = $request->only(['first_name', 'last_name', 'email']);
        if (! empty($request->input('password'))) {
            if (! Hash::check((string) $request->input('current_password'), auth()->user()->password)) {
                return back()->withErrors(['current_password' => trans('current_password_incorrect')])->withInput();
            }
            $input['password'] = Hash::make((string) $request->input('password'));
        }

        $user = User::findOrFail($id);

        if (! Auth::user()->hasRole('Super Admin') && (int) $user->school_id !== (int) Auth::user()->school_id) {
            abort(403);
        }

        $user->update($input);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')
            ->with('success', trans('data_update_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (! Auth::user()->hasRole('Super Admin') && (int) $user->school_id !== (int) Auth::user()->school_id) {
            abort(403);
        }

        if ($user->email === 'superadmin@gmail.com' || $user->hasRole('Super Admin')) {
            return redirect()->route('users.index')
                ->with('error', 'Super Admin user cannot be deleted.');
        }

        if ((int) Auth::id() === (int) $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', trans('data_delete_successfully'));
    }
}
