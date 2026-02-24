<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $users = User::query()->with(['roles', 'permissions'])->orderBy('name')->get();

        $permissionsByGroup = $permissions->groupBy(function (Permission $permission) {
            return explode('.', $permission->name)[0] ?? 'misc';
        });

        return view('admin.roles.index', compact('roles', 'permissions', 'users', 'permissionsByGroup'));
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        $permission = Permission::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $this->auditLogService->record(
            action: 'admin.permission.created',
            user: $request->user(),
            loggable: $permission,
            context: ['name' => $permission->name],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Permission created.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->auditLogService->record(
            action: 'admin.role.created',
            user: $request->user(),
            loggable: $role,
            context: ['permissions' => $data['permissions'] ?? []],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Role created.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        $this->auditLogService->record(
            action: 'admin.role.updated',
            user: $request->user(),
            loggable: $role,
            context: ['permissions' => $data['permissions'] ?? []],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Role {$role->name} updated.");
    }

    public function updateUserPermissions(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->syncPermissions($data['permissions'] ?? []);

        $this->auditLogService->record(
            action: 'admin.user.permissions.updated',
            user: $request->user(),
            loggable: $user,
            context: ['permissions' => $data['permissions'] ?? []],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Direct permissions updated for {$user->name}.");
    }
}
