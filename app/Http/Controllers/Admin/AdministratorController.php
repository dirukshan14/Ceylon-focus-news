<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Manages administrator accounts (users who hold at least one role).
 * All actions here require the manage-administrators permission, which
 * only Super Admin has by default (see RoleSeeder).
 */
class AdministratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-administrators');
    }

    public function index()
    {
        $admins = User::whereHas('roles')->orWhere('is_super_admin', true)
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.administrators.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.administrators.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Generate a strong temporary password; the new admin sets their own
        // on first login (must_change_password flag).
        $this->guardRoleAssignment($request->user(), $data['roles']);

        $tempPassword = Str::password(16);

        $admin = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'status' => 'active',
            'must_change_password' => true,
        ]);

        $admin->roles()->sync($data['roles']);
        AuditLog::record('administrator.created', $admin, ['roles' => $data['roles']]);

        // In production this would email the temp password via a Notification
        // rather than displaying it - shown here once for the Super Admin to relay.
        return redirect()->route('admin.administrators.index')
            ->with('success', "Administrator created. Temporary password: {$tempPassword}");
    }

    public function edit(User $administrator)
    {
        $this->authorize('update', $administrator);

        $roles = Role::orderBy('name')->get();
        $administrator->load('roles');

        return view('admin.administrators.edit', ['admin' => $administrator, 'roles' => $roles]);
    }

    public function update(Request $request, User $administrator)
    {
        $this->authorize('update', $administrator);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$administrator->id],
            'status' => ['required', 'in:active,inactive,suspended'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Guard: the primary Super Admin account can't be demoted or deactivated
        // by anyone, including another Super Admin, to avoid full lockout.
        $this->guardRoleAssignment($request->user(), $data['roles']);

        $primaryId = User::where('is_super_admin', true)->oldest('id')->value('id');
        if ($administrator->id === $primaryId) {
            $data['status'] = 'active';
        }

        $administrator->update(collect($data)->except('roles')->toArray());
        $administrator->roles()->sync($data['roles']);

        AuditLog::record('administrator.updated', $administrator, ['roles' => $data['roles'], 'status' => $data['status']]);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator updated.');
    }

    public function destroy(User $administrator)
    {
        $this->authorize('delete', $administrator);

        AuditLog::record('administrator.deleted', $administrator, ['email' => $administrator->email]);
        $administrator->delete(); // soft delete

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator removed.');
    }

    public function resetPassword(User $administrator)
    {
        $this->authorize('update', $administrator);

        $tempPassword = Str::password(16);
        $administrator->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        AuditLog::record('administrator.password_reset', $administrator);

        return back()->with('success', "Password reset. Temporary password: {$tempPassword}");
    }

    /**
     * SECURITY (privilege escalation): only a Super Admin may hand out the
     * Super Admin role or any role carrying administrator/role-management
     * permissions. Everyone else may only assign roles whose permissions they
     * already hold themselves.
     */
    private function guardRoleAssignment(User $actor, array $roleIds): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        $privileged = [Permission::MANAGE_ADMINISTRATORS, Permission::MANAGE_ROLES];

        foreach (Role::with('permissions')->whereIn('id', $roleIds)->get() as $role) {
            abort_if($role->slug === Role::SUPER_ADMIN, 403, 'Only a Super Admin can assign the Super Admin role.');

            foreach ($role->permissions as $permission) {
                abort_if(
                    in_array($permission->slug, $privileged, true) || ! $actor->hasPermission($permission->slug),
                    403,
                    'You cannot assign a role that grants permissions you do not have.'
                );
            }
        }
    }
}
