<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function searchEmployees(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        if ($query === '' || mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $employees = Employee::query()
            ->select(['id', 'employee_number', 'first_name', 'middle_name', 'last_name', 'email'])
            ->where('employment_status', 'active')
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('employee_number', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('middle_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('employee_number')
            ->limit(12)
            ->get()
            ->map(static fn (Employee $employee): array => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ])
            ->values();

        return response()->json(['data' => $employees]);
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

        $normalizedEmail = Str::lower(trim((string) $data['email']));
        $employee = Employee::query()
            ->where('email', $normalizedEmail)
            ->where('employment_status', 'active')
            ->first();

        if (! $employee) {
            return back()
                ->withErrors(['email' => 'Selected employee not found or employee is not active.'])
                ->withInput();
        }

        $data['name'] = $employee->full_name;
        $data['email'] = $employee->email;

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

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'nmis-users-'.now()->format('Ymd-His').'.csv';
        $users = User::query()->with('roles')->orderBy('name')->get();

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Active', 'Roles', 'Created At']);
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->is_active ? 'Yes' : 'No',
                    $user->roles->pluck('name')->implode('; '),
                    (string) $user->created_at,
                ]);
            }
            fclose($handle);
        }, $fileName);
    }
}
