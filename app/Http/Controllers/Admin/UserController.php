<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_users', only: ['index']),
            new Middleware('permission:create_user', only: ['create', 'store']),
            new Middleware('permission:edit_user', only: ['edit', 'update']),
            new Middleware('permission:delete_user', only: ['destroy', 'bulkAction']),
            new Middleware('permission:toggle_user_status', only: ['toggleStatus']),
        ];
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'permissions']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->latest();
                break;
        }

        $users = $query->paginate(12)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }


    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $permissions = Permission::orderBy('group')->get()->groupBy('group');
        $roles = \Spatie\Permission\Models\Role::all();
        $departments = Department::where('is_active', true)->orderBy('sort_order')->get();
        $groupLabels = $this->getPermissionGroupLabels();
        return view('users.create', compact('permissions', 'roles', 'departments', 'groupLabels'));
    }


    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'department_id' => $request->department_id,
            'job_title' => $request->job_title,
        ]);

        $user->syncRoles($request->input('roles', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }


    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $permissions = Permission::orderBy('group')->get()->groupBy('group');
        $roles = \Spatie\Permission\Models\Role::all();
        $departments = Department::where('is_active', true)->orderBy('sort_order')->get();
        $user->load(['permissions', 'roles']);
        $groupLabels = $this->getPermissionGroupLabels();
        return view('users.edit', compact('user', 'permissions', 'roles', 'departments', 'groupLabels'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => true,
            'department_id' => $request->department_id,
            'job_title' => $request->job_title,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $user->syncRoles($request->input('roles', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }


    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id == auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        User::whereKey($user->id)->update(['is_active' => DB::raw('1 - is_active')]);
        $user->refresh();
        $message = $user->is_active ? 'تم تفعيل المستخدم بنجاح' : 'تم إلغاء تفعيل المستخدم بنجاح';
        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Bulk actions for users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        $userIds = $request->users;
        $currentUserId = auth()->id();

        switch ($request->action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['is_active' => true]);
                $message = 'تم تفعيل المستخدمين المحددين بنجاح';
                break;

            case 'deactivate':
                $filteredUserIds = array_diff($userIds, [$currentUserId]);
                User::whereIn('id', $filteredUserIds)->update(['is_active' => false]);
                $message = 'تم إلغاء تفعيل المستخدمين المحددين بنجاح';
                break;

            case 'delete':
                $filteredUserIds = array_diff($userIds, [$currentUserId]);
                User::whereIn('id', $filteredUserIds)->delete();
                $message = 'تم حذف المستخدمين المحددين بنجاح';
                break;
        }

        return redirect()->route('admin.users.index')
                        ->with('success', $message);
    }

    private function getPermissionGroupLabels(): array
    {
        return [
            'local_companies' => ['label' => 'الشركات المحلية', 'icon' => 'fas fa-building', 'color' => 'primary'],
            'foreign_companies' => ['label' => 'الشركات الأجنبية', 'icon' => 'fas fa-globe-americas', 'color' => 'info'],
            'pharmaceutical_products' => ['label' => 'الأصناف الدوائية', 'icon' => 'fas fa-capsules', 'color' => 'success'],
            'invoices' => ['label' => 'الفواتير', 'icon' => 'fas fa-file-invoice-dollar', 'color' => 'warning'],
            'documents' => ['label' => 'المستندات', 'icon' => 'fas fa-folder-open', 'color' => 'secondary'],
            'users' => ['label' => 'المستخدمين والأقسام', 'icon' => 'fas fa-users-cog', 'color' => 'danger'],
            'announcements' => ['label' => 'التعميمات', 'icon' => 'fas fa-bullhorn', 'color' => 'primary'],
            'reports' => ['label' => 'التقارير', 'icon' => 'fas fa-chart-bar', 'color' => 'info'],
            'representatives' => ['label' => 'ممثلي الشركات', 'icon' => 'fas fa-id-card', 'color' => 'success'],
            'settings' => ['label' => 'الإعدادات', 'icon' => 'fas fa-cogs', 'color' => 'dark'],
        ];
    }
}
