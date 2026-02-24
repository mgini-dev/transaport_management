<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->latest()->paginate(15),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $this->auditLogService->record(
            action: 'admin.user.created',
            user: $request->user(),
            loggable: $user,
            context: ['roles' => $data['roles'] ?? []],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'is_active' => ['required', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => (bool) $data['is_active'],
        ]);
        $user->syncRoles($data['roles'] ?? []);

        $this->auditLogService->record(
            action: 'admin.user.updated',
            user: $request->user(),
            loggable: $user,
            context: ['roles' => $data['roles'] ?? [], 'is_active' => (bool) $data['is_active']],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'User updated successfully.');
    }
}

