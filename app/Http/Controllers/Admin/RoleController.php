<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-roles');
    }

    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $this->guardPermissionGrant($request->user(), $data['permissions'] ?? []);

        $role = Role::create(collect($data)->except('permissions')->toArray());
        $role->permissions()->sync($data['permissions'] ?? []);

        AuditLog::record('role.created', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        // Protect the built-in super-admin role from being edited/renamed away.
        if ($role->slug === Role::SUPER_ADMIN) {
            abort(403, 'The Super Admin role cannot be modified.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $this->guardPermissionGrant($request->user(), $data['permissions'] ?? []);

        $role->update(collect($data)->except('permissions')->toArray());
        $role->permissions()->sync($data['permissions'] ?? []);

        AuditLog::record('role.updated', $role, ['permissions' => $data['permissions'] ?? []]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === Role::SUPER_ADMIN) {
            abort(403, 'The Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Cannot delete a role that is assigned to users.']);
        }

        AuditLog::record('role.deleted', $role, ['name' => $role->name]);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    /**
     * SECURITY (privilege escalation): only a Super Admin may grant the
     * administrator/role-management permissions, and nobody may grant a
     * permission they do not hold themselves.
     */
    private function guardPermissionGrant(\App\Models\User $actor, array $permissionIds): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        $privileged = [Permission::MANAGE_ADMINISTRATORS, Permission::MANAGE_ROLES];

        foreach (Permission::whereIn('id', $permissionIds)->get() as $permission) {
            abort_if(
                in_array($permission->slug, $privileged, true) || ! $actor->hasPermission($permission->slug),
                403,
                'You cannot grant permissions you do not have.'
            );
        }
    }
}
