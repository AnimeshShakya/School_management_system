<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Core roles seeded during installation/demo setup.
     */
    private array $seededRoleNames = [
        'Super Admin',
        'Admin',
        'Teacher',
        'Parent',
        'Student',
    ];

    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = Role::where('custom_role', 1)
            ->orWhereIn('name', $this->seededRoleNames)
            ->orderBy('id', 'DESC')
            ->paginate(5);
        $excludedPermissions = ['class-teacher', 'manage-online-exam', 'attendance-delete', 'attendance-edit', 'attendance-create', 'attendance-list'];

        $permission = Permission::whereNotIn('name', $excludedPermissions)
            ->orderBy('id', 'DESC')
            ->get();

        return view('roles.index', compact('roles', 'permission'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $excludedPermissions = ['class-teacher', 'manage-online-exam', 'attendance-delete', 'attendance-edit', 'attendance-create', 'attendance-list'];

        $permission = Permission::whereNotIn('name', $excludedPermissions)
            ->orderBy('id', 'DESC')
            ->get();

        return view('roles.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array',
        ]);

        $role = Role::create(['name' => $request->input('name'), 'custom_role' => 1]);

        // Get permission IDs and sync them
        $permissionIds = $request->input('permission', []);
        if (! empty($permissionIds)) {
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')->with('success', trans('data_store_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {

        if (! Auth::user()->can('role-list')) {
            $response = [
                'error' => true,
                'message' => trans('no_permission_message'),
            ];

            return response()->json($response);
        }
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        $sql = Role::where('custom_role', '!=', 0)
            ->orWhereIn('name', $this->seededRoleNames);

        if (isset($_GET['search']) && ! empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $tempRow = [];
        $no = 1;
        foreach ($res as $row) {
            $operate = '<a href='.route('roles-list', $row->id).' class="btn btn-xs btn-gradient-info btn-rounded btn-icon"><i class="fa fa-eye"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href='.route('roles.edit', $row->id).' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon" data-id='.$row->id.' title="Edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href='.route('roles.destroy', $row->id).' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id='.$row->id.'><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['name'] = $row->name;
            $tempRow['operate'] = $operate;
            $tempRow['created_at'] = convertDateFormat($row->created_at, 'd-m-Y H:i:s');
            $tempRow['updated_at'] = convertDateFormat($row->updated_at, 'd-m-Y H:i:s');
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    public function showList($id)
    {
        $role = Role::findOrFail($id);
        $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $id)
            ->orderBy('permissions.name')
            ->get();

        return view('roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $excludedPermissions = ['class-teacher', 'manage-online-exam', 'attendance-delete', 'attendance-edit', 'attendance-create', 'attendance-list'];

        $permission = Permission::whereNotIn('name', $excludedPermissions)
            ->orderBy('id', 'DESC')
            ->get();
        $rolePermissions = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles.edit', compact('role', 'permission', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->can('role-edit')) {
            $response = [
                'error' => true,
                'message' => trans('no_permission_message'),
            ];

            return response()->json($response);
        }
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permission' => 'required|array',
        ]);
        try {
            $role = Role::findOrFail($id);
            $role->name = $request->input('name');
            $role->custom_role = 1;
            $role->save();

            // Get permission IDs and sync them
            $permissionIds = $request->input('permission', []);
            if (! empty($permissionIds)) {
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                $role->syncPermissions($permissions);
            }
            $response = [
                'error' => false,
                'message' => trans('data_update_successfully'),
            ];
        } catch (\Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e,
            ];
        }

        return redirect()->route('roles.index')->with('success', trans('data_update_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        if (! Auth::user()->can('role-delete')) {
            $response = [
                'error' => true,
                'message' => trans('no_permission_message'),
            ];

            return response()->json($response);
        }
        try {
            $role = Role::findOrFail($id);
            if ($role->name === 'Super Admin') {
                return response()->json([
                    'error' => true,
                    'message' => 'Super Admin role cannot be deleted',
                ]);
            }
            $role->delete();
            $response = [
                'error' => false,
                'message' => trans('data_delete_successfully'),
            ];
        } catch (\Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }
}
