<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Services\MailService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    private array $userColumns = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Auth::user()->can('staff-list')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        $roles = $this->getAssignableRoles();

        return view('staff.index', compact('roles'));
    }

    private function getAssignableRoles(): Collection
    {
        $query = Role::query();

        if (Schema::hasColumn('roles', 'custom_role')) {
            $customRoles = (clone $query)
                ->where('custom_role', 1)
                ->orderBy('id', 'DESC')
                ->get();

            if ($customRoles->isNotEmpty()) {
                return $customRoles;
            }
        }

        // If the current user is Super Admin, return all roles (so they can assign Super Admin too).
        // Otherwise fall back to excluding core high-privilege roles from assignment.
        if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
            return $query->orderBy('id', 'DESC')->get();
        }

        return Role::whereNotIn('name', ['Super Admin', 'Teacher', 'Student', 'Parent'])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Auth::user()->can('staff-create')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required',
            'mobile' => 'required|numeric|regex:/^[0-9]{7,16}$/',
            'image' => 'mimes:jpeg,png,jpg|image|max:2048',
            'dob' => 'required|date',
            'address' => 'nullable',

        ], [
            'mobile.regex' => __('The mobile number must be a length of 7 to 15 digits.'),
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            $role = Role::findOrFail($request->role_id);

            $check_user = User::where('email', $request->email)->onlyTrashed();
            if ($check_user->count()) {
                $user_exists = $check_user->first();
                DB::table('users')->where('id', $user_exists->id)->update(['deleted_at' => null]);

                $user = User::findOrFail($user_exists->id);

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time().'-'.$image->getClientOriginalName();
                    // made file path to store in database
                    $file_path = 'staff/'.$file_name;
                    // resized image
                    resizeImage($image);
                    // stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/staff');
                    $image->move($destinationPath, $file_name);

                    $user->image = $file_path;
                } else {
                    $user->image = '';
                }

                $staff_plain_text_password = Str::random(12);
                $this->fillUserFromRequest($user, $request, $staff_plain_text_password);
                $user->created_by = Auth::id();
                if (! Auth::user()->hasRole('Super Admin')) {
                    $user->school_id = Auth::user()->school_id;
                }
                $user->save();

                if ($this->hasStaffTable()) {
                    $check_staff = DB::table('staffs')->where('user_id', $user->id)->whereNotNull('deleted_at');
                    if ($check_staff->count()) {
                        $staff_exists = $check_staff->first();
                        DB::table('staffs')->where('id', $staff_exists->id)->update(['deleted_at' => null]);
                        $staff = Staff::findOrFail($staff_exists->id);
                        $staff->user_id = $user->id;
                        $staff->update();
                    }
                }

                $user->assignRole($role);

                $school_name = getSettings('school_name');
                $data = [
                    'subject' => 'Welcome to '.$school_name['school_name'],
                    'name' => $request->first_name.' '.$request->last_name,
                    'email' => $request->email,
                    'password' => $staff_plain_text_password,
                    'school_name' => $school_name['school_name'],
                    'role' => $role->name,

                ];

                MailService::sendWithFallback('staff.email', $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            } else {
                $user = new User;

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time().'-'.$image->getClientOriginalName();
                    // made file path to store in database
                    $file_path = 'staff/'.$file_name;
                    // resized image
                    resizeImage($image);
                    // stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/staff');
                    $image->move($destinationPath, $file_name);

                    $user->image = $file_path;
                } else {
                    $user->image = '';
                }

                $staff_plain_text_password = Str::random(12);
                $this->fillUserFromRequest($user, $request, $staff_plain_text_password);
                $user->created_by = Auth::id();
                if (! Auth::user()->hasRole('Super Admin')) {
                    $user->school_id = Auth::user()->school_id;
                }
                $user->save();

                $user->assignRole($role);

                if ($this->hasStaffTable()) {
                    $staff = new Staff;
                    $staff->user_id = $user->id;
                    $staff->save();
                }

                $school_name = getSettings('school_name');
                $data = [
                    'subject' => 'Welcome to '.$school_name['school_name'],
                    'name' => $request->first_name.' '.$request->last_name,
                    'email' => $request->email,
                    'password' => $staff_plain_text_password,
                    'school_name' => $school_name['school_name'],
                    'role' => $role->name,

                ];

                MailService::sendWithFallback('staff.email', $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            }
            ResponseService::successResponse(trans('data_store_successfully'));
        } catch (\Throwable $e) {
            ResponseService::errorResponse(trans('error_occurred'), null, null, $e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        if (! Auth::user()->can('staff-list')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }

        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

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

        if (! $this->hasStaffTable()) {
            // If the staffs table doesn't exist, still return Super Admin users so the UI isn't empty.
            try {
                $superAdmins = User::whereHas('roles', function ($q) {
                    $q->where('name', 'Super Admin');
                })->whereNull('deleted_at')->get();

                $data = getSettings('date_formate');
                $rows = [];
                $no = 1;
                foreach ($superAdmins as $sa) {
                    $tempRow = [];
                    $tempRow['id'] = 0;
                    $tempRow['no'] = $no++;
                    $tempRow['user_id'] = $sa->id;
                    $tempRow['role_id'] = $sa->roles->pluck('id')->implode(', ');
                    $tempRow['roles'] = $sa->roles->pluck('name')->implode(', ');
                    $tempRow['first_name'] = $sa->first_name ?? explode(' ', $sa->name ?? '')[0] ?? '';
                    $tempRow['last_name'] = $sa->last_name ?? (isset($sa->name) ? implode(' ', array_slice(explode(' ', $sa->name), 1)) : '');
                    $tempRow['gender'] = $sa->gender ?? '';
                    $tempRow['address'] = $sa->current_address ?? '';
                    $tempRow['email'] = $sa->email ?? '';
                    $tempRow['dob'] = ! empty($sa->dob) && ! empty($data['date_formate']) ? date($data['date_formate'], strtotime($sa->dob)) : '';
                    $tempRow['mobile'] = $sa->mobile ?? '';
                    $tempRow['image'] = $sa->image ?? '';
                    // For appended rows there's no operate buttons by default; leave operate empty or add conditional actions.
                    $tempRow['operate'] = '';
                    $rows[] = $tempRow;
                }

                return response()->json([
                    'total' => count($rows),
                    'rows' => $rows,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'total' => 0,
                    'rows' => [],
                ]);
            }
        }

        $sql = Staff::with('user', 'user.roles');

        if (! Auth::user()->hasRole('Super Admin')) {
            $sql->whereHas('user', function ($q) {
                $q->where('school_id', Auth::user()->school_id);
            });
        }

        if (isset($_GET['search']) && ! empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")
                ->orwhere('user_id', 'LIKE', "%$search%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $searchColumns = ['first_name', 'last_name', 'name', 'gender', 'email', 'current_address'];
                    $first = true;

                    foreach ($searchColumns as $column) {
                        if (! $this->hasUserColumn($column)) {
                            continue;
                        }

                        if ($first) {
                            $q->where($column, 'LIKE', "%$search%");
                            $first = false;
                        } else {
                            $q->orWhere($column, 'LIKE', "%$search%");
                        }
                    }
                });
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        // Include users who have the 'Super Admin' role but don't have a corresponding staff row.
        // This ensures Super Admin accounts are visible in the staff listing even if no staff record was created.
        try {
            $existingUserIds = $res->pluck('user_id')->filter()->unique()->toArray();
            $extraSuperAdmins = User::whereHas('roles', function ($q) {
                $q->where('name', 'Super Admin');
            })->whereNull('deleted_at')
                ->whereNotIn('id', $existingUserIds)
                ->get();

            foreach ($extraSuperAdmins as $sa) {
                if ($this->hasStaffTable()) {
                    // Create a staff record for this super admin if one doesn't exist
                    $existing = Staff::where('user_id', $sa->id)->first();
                    if (! $existing) {
                        $staff = new Staff;
                        $staff->user_id = $sa->id;
                        $staff->save();
                        // attach the user relation for consistency
                        $staff->user = $sa;
                        $res->push($staff);
                    } else {
                        // ensure user relation is loaded and push existing
                        $existing->user = $sa;
                        $res->push($existing);
                    }
                } else {
                    // fallback: create a lightweight object if staff table doesn't exist
                    $obj = new \stdClass;
                    $obj->id = 0;
                    $obj->user_id = $sa->id;
                    $obj->user = $sa;
                    $res->push($obj);
                }
            }

            // Increment total to account for the appended super admins (created or fallback)
            $total += $extraSuperAdmins->count();
        } catch (\Throwable $e) {
            // If anything goes wrong, silently continue with existing results.
        }

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $tempRow = [];
        $no = 1;
        foreach ($res as $row) {
            $operate = '<a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id='.$row->id.' data-url='.url('staff').' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a class="btn btn-xs btn-gradient-danger btn-rounded btn-icon deletedata" data-id='.$row->id.' data-user_id='.$row->user_id.' data-url='.url('staff', $row->user_id).' title="Delete"><i class="fa fa-trash"></i></a>';

            $data = getSettings('date_formate');
            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['role_id'] = $row->user->roles->pluck('id')->implode(', ');
            $tempRow['roles'] = $row->user->roles->pluck('name')->implode(', ');
            $tempRow['first_name'] = $this->resolveFirstName($row->user);
            $tempRow['last_name'] = $this->resolveLastName($row->user);
            $tempRow['gender'] = $row->user->gender ?? '';
            $tempRow['address'] = $row->user->current_address ?? '';
            $tempRow['email'] = $row->user->email ?? '';
            $tempRow['dob'] = ! empty($row->user->dob) ? date($data['date_formate'], strtotime($row->user->dob)) : '';
            $tempRow['mobile'] = $row->user->mobile ?? '';
            $tempRow['image'] = $row->user->image;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request->all());
        if (! Auth::user()->can('staff-edit')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
            'gender' => 'required',
            'mobile' => 'required|numeric|regex:/^[0-9]{7,16}$/',
            'image' => 'mimes:jpeg,png,jpg|image|max:2048',
            'dob' => 'required',
            'address' => 'nullable',

        ], [
            'mobile.regex' => __('The mobile number must be a length of 7 to 15 digits.'),
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
            ];

            return response()->json($response);
        }
        try {
            $role = Role::findOrFail($request->role_id);

            $user = User::findorFail($request->user_id);

            if ($request->hasFile('image')) {

                if (Storage::disk('public')->exists($user->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('image'));
                }

                $image = $request->file('image');
                // made file name with combination of current time
                $file_name = time().'-'.$image->getClientOriginalName();
                // made file path to store in database
                $file_path = 'staff/'.$file_name;
                // resized image
                resizeImage($image);
                // stored image to storage/public/teachers folder
                $destinationPath = storage_path('app/public/staff');
                $image->move($destinationPath, $file_name);

                $user->image = $file_path;
            }

            $this->fillUserFromRequest($user, $request);
            $user->save();

            $user->syncRoles($role);
            ResponseService::successResponse(trans('data_store_successfully'));
        } catch (\Throwable $e) {
            ResponseService::errorResponse(trans('error_occurred'), $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Auth::user()->can('staff-delete')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        try {
            $user = User::find($id);

            if (! Auth::user()->hasRole('Super Admin') && (int) $user->school_id !== (int) Auth::user()->school_id) {
                return ResponseService::errorResponse(trans('no_permission_message'));
            }
            if (Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $user->delete();

            if ($this->hasStaffTable()) {
                $staff = Staff::where('user_id', $id);
                $staff->delete();
            }
            ResponseService::successResponse(trans('data_delete_successfully'));
        } catch (\Throwable $e) {
            ResponseService::errorResponse(trans('error_occurred'), null, null, $e);
        }
    }

    private function hasStaffTable(): bool
    {
        return Schema::hasTable((new Staff)->getTable());
    }

    private function hasUserColumn(string $column): bool
    {
        if (empty($this->userColumns)) {
            $this->userColumns = Schema::getColumnListing('users');
        }

        return in_array($column, $this->userColumns, true);
    }

    private function fillUserFromRequest(User $user, Request $request, ?string $plainPassword = null): void
    {
        $firstName = trim((string) $request->first_name);
        $lastName = trim((string) $request->last_name);

        if ($plainPassword !== null && $this->hasUserColumn('password')) {
            $user->password = Hash::make($plainPassword);
        }

        if ($this->hasUserColumn('first_name')) {
            $user->first_name = $firstName;
        }
        if ($this->hasUserColumn('last_name')) {
            $user->last_name = $lastName;
        }
        if ($this->hasUserColumn('name')) {
            $user->name = trim($firstName.' '.$lastName);
        }

        if ($this->hasUserColumn('email')) {
            $user->email = $request->email;
        }
        if ($this->hasUserColumn('gender')) {
            $user->gender = $request->gender;
        }
        if ($this->hasUserColumn('mobile')) {
            $user->mobile = $request->mobile;
        }
        if ($this->hasUserColumn('dob')) {
            $user->dob = date('Y-m-d', strtotime((string) $request->dob));
        }
        if ($this->hasUserColumn('current_address')) {
            $user->current_address = $request->address;
        }
    }

    private function resolveFirstName(User $user): string
    {
        if (! empty($user->first_name)) {
            return (string) $user->first_name;
        }

        if (! empty($user->name)) {
            return explode(' ', (string) $user->name)[0] ?? '';
        }

        return '';
    }

    private function resolveLastName(User $user): string
    {
        if (! empty($user->last_name)) {
            return (string) $user->last_name;
        }

        if (! empty($user->name)) {
            $nameParts = explode(' ', (string) $user->name, 2);

            return $nameParts[1] ?? '';
        }

        return '';
    }
}
