<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_departments'),
        ];
    }

    public function index()
    {
        $departments = Department::whereNull('parent_id')
            ->with(['children.users', 'users'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $mainDepartments = Department::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.departments.create', compact('mainDepartments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => __('departments.name_required'),
        ]);

        Department::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', __('departments.created'));
    }

    public function edit(Department $department)
    {
        $mainDepartments = Department::whereNull('parent_id')
            ->where('id', '!=', $department->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.departments.edit', compact('department', 'mainDepartments'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => __('departments.name_required'),
        ]);

        if ($request->parent_id == $department->id) {
            return redirect()->back()->with('error', __('departments.cannot_self_parent'));
        }

        $department->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', __('departments.updated'));
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', __('departments.has_users'));
        }

        if ($department->children()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', __('departments.has_children'));
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', __('departments.deleted'));
    }

    public function toggleStatus(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);

        return redirect()->route('admin.departments.index')
            ->with('success', $department->is_active ? __('departments.activated') : __('departments.deactivated'));
    }

    public function members(Department $department)
    {
        $department->load(['users', 'children.users', 'parent']);

        $allUsers = User::where('is_active', true)
            ->whereNull('department_id')
            ->orWhere('department_id', $department->id)
            ->orderBy('name')
            ->get();

        return view('admin.departments.members', compact('department', 'allUsers'));
    }

    public function assignMembers(Request $request, Department $department)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ], [
            'user_ids.required' => __('departments.user_required'),
        ]);

        User::whereIn('id', $request->user_ids)->update(['department_id' => $department->id]);

        return redirect()->route('admin.departments.members', $department)
            ->with('success', __('departments.members_added'));
    }

    public function removeMember(Department $department, User $user)
    {
        if ($user->department_id == $department->id) {
            $user->update(['department_id' => null]);
        }

        return redirect()->route('admin.departments.members', $department)
            ->with('success', __('departments.member_removed'));
    }
}
