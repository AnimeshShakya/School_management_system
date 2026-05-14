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
        $data = User::with('roles')->orderBy('id', 'DESC')->paginate(10);

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::query()->pluck('name', 'name')->all();

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

        $input = $request->only(['first_name', 'last_name', 'email']);
        $input['password'] = Hash::make((string) $request->input('password'));

        $user = User::create($input);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')
            ->with('success', trans('data_store_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::query()->pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
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

        $input = $request->only(['first_name', 'last_name', 'email']);
        if (! empty($request->input('password'))) {
            if (! Hash::check((string) $request->input('current_password'), auth()->user()->password)) {
                return back()->withErrors(['current_password' => trans('current_password_incorrect')])->withInput();
            }
            $input['password'] = Hash::make((string) $request->input('password'));
        }

        $user = User::findOrFail($id);
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
