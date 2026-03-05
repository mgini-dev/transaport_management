<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Fleet;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\Trip;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $preset = $this->resolvePreset($user);
        $can = $this->permissionMap($user);

        $tripQuery = $can['trips']
            ? NmisDataScope::ownOrAll(Trip::query(), $user, 'created_by', 'trips.view_all')
            : null;
        $orderQuery = $can['orders']
            ? NmisDataScope::ownOrAll(Order::query(), $user, 'created_by', 'orders.view_all')
            : null;
        $fuelQuery = $can['fuel']
            ? NmisDataScope::ownOrAll(FuelRequisition::query(), $user, 'requested_by', 'fuel.view_all')
            : null;
        $driverQuery = $can['drivers']
            ? NmisDataScope::ownOrAll(Driver::query(), $user, 'created_by', 'drivers.view_all')
            : null;

        $counts = [
            'customers_total' => $can['customers'] ? Customer::query()->count() : 0,
            'fleets_total' => $can['fleet'] ? Fleet::query()->count() : 0,
            'drivers_total' => $driverQuery ? (clone $driverQuery)->count() : 0,
            'drivers_active' => $driverQuery ? (clone $driverQuery)->where('is_active', true)->count() : 0,
            'employees_total' => $can['hr'] ? Employee::query()->count() : 0,
            'employees_active' => $can['hr'] ? Employee::query()->where('employment_status', 'active')->count() : 0,
            'trips_total' => $tripQuery ? (clone $tripQuery)->count() : 0,
            'trips_open' => $tripQuery ? (clone $tripQuery)->where('status', 'open')->count() : 0,
            'trips_closed' => $tripQuery ? (clone $tripQuery)->where('status', 'closed')->count() : 0,
            'orders_total' => $orderQuery ? (clone $orderQuery)->count() : 0,
            'orders_active' => $orderQuery ? (clone $orderQuery)->whereIn('status', ['created', 'processing', 'assigned', 'transportation'])->count() : 0,
            'orders_completed' => $orderQuery ? (clone $orderQuery)->where('status', 'completed')->count() : 0,
            'orders_incomplete' => $orderQuery ? (clone $orderQuery)->where('status', 'incomplete')->count() : 0,
            'fuel_total' => $fuelQuery ? (clone $fuelQuery)->count() : 0,
            'fuel_pending' => $fuelQuery ? (clone $fuelQuery)->whereIn('status', ['submitted', 'supervisor_approved'])->count() : 0,
            'fuel_approved' => $fuelQuery ? (clone $fuelQuery)->where('status', 'accountant_approved')->count() : 0,
            'fuel_rejected' => $fuelQuery ? (clone $fuelQuery)->whereIn('status', ['supervisor_rejected', 'accountant_rejected'])->count() : 0,
            'fuel_approved_month_amount' => $fuelQuery
                ? (float) ((clone $fuelQuery)
                    ->where('status', 'accountant_approved')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount'))
                : 0.0,
        ];

        $completionRate = $counts['orders_total'] > 0
            ? round(($counts['orders_completed'] / $counts['orders_total']) * 100, 1)
            : 0.0;

        $statsCards = $this->buildStatsCards($can, $counts, $completionRate);
        $quickLinks = $this->buildQuickLinks($can, $counts);
        $charts = $this->buildCharts($can, $tripQuery, $orderQuery, $fuelQuery);

        $adminOverview = null;
        if ($can['admin']) {
            $adminOverview = $this->buildAdminOverview();
            $charts['users_by_role_labels'] = $adminOverview['users_by_role_labels'];
            $charts['users_by_role_values'] = $adminOverview['users_by_role_values'];
        }

        return view('dashboard', [
            'preset' => $preset,
            'can' => $can,
            'statsCards' => $statsCards,
            'quickLinks' => $quickLinks,
            'charts' => $charts,
            'widgets' => [
                'trip_trend' => $can['trips'],
                'order_status' => $can['orders'],
                'fuel_spend' => $can['fuel'],
                'approval_pipeline' => $can['fuel'] && ($can['fuel_approver'] || $can['admin']),
                'users_by_role' => $can['admin'],
            ],
            'roleInfo' => [
                'roles' => $user->getRoleNames()->values()->all(),
                'permissions_total' => $user->getAllPermissions()->count(),
            ],
            'adminOverview' => $adminOverview,
            'notifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function permissionMap(User $user): array
    {
        return [
            'customers' => $user->can('customers.view'),
            'trips' => $user->can('trips.view'),
            'orders' => $user->can('orders.view'),
            'fleet' => $user->can('fleet.view'),
            'drivers' => $user->can('drivers.view'),
            'fuel' => $user->can('fuel.view'),
            'fuel_approver' => $user->can('fuel.approve.supervisor') || $user->can('fuel.approve.accounting'),
            'hr' => $user->can('hr.employees.view') || $user->can('hr.employees.manage'),
            'admin_users' => $user->can('admin.users.manage'),
            'admin_roles' => $user->can('admin.roles.manage'),
            'admin_logs' => $user->can('admin.logs.view'),
            'admin_reports' => $user->can('admin.dashboard.view_all'),
            'admin' => $user->can('admin.dashboard.view_all'),
        ];
    }

    /**
     * @param  array<string, bool>  $can
     * @param  array<string, int|float>  $counts
     * @return array<int, array{label: string, value: string, hint: string, tone: string}>
     */
    private function buildStatsCards(array $can, array $counts, float $completionRate): array
    {
        $cards = [];

        if ($can['trips']) {
            $cards[] = [
                'label' => 'Open Trips',
                'value' => number_format((int) $counts['trips_open']),
                'hint' => 'Trips currently active',
                'tone' => 'primary',
            ];
            $cards[] = [
                'label' => 'Closed Trips',
                'value' => number_format((int) $counts['trips_closed']),
                'hint' => 'Trips completed',
                'tone' => 'secondary',
            ];
        }

        if ($can['orders']) {
            $cards[] = [
                'label' => 'Active Orders',
                'value' => number_format((int) $counts['orders_active']),
                'hint' => 'Created / processing / assigned / transportation',
                'tone' => 'accent',
            ];
            $cards[] = [
                'label' => 'Order Completion',
                'value' => number_format($completionRate, 1).'%',
                'hint' => number_format((int) $counts['orders_completed']).' completed of '.number_format((int) $counts['orders_total']),
                'tone' => 'primary',
            ];
        }

        if ($can['fuel']) {
            $cards[] = [
                'label' => 'Fuel Pending',
                'value' => number_format((int) $counts['fuel_pending']),
                'hint' => 'Submitted and supervisor approved queue',
                'tone' => 'warning',
            ];
            $cards[] = [
                'label' => 'Fuel Approved (Month)',
                'value' => number_format((float) $counts['fuel_approved_month_amount'], 2),
                'hint' => 'Approved amount for current month',
                'tone' => 'secondary',
            ];
        }

        if ($can['drivers']) {
            $cards[] = [
                'label' => 'Active Drivers',
                'value' => number_format((int) $counts['drivers_active']),
                'hint' => number_format((int) $counts['drivers_total']).' total visible drivers',
                'tone' => 'accent',
            ];
        }

        if ($can['hr']) {
            $cards[] = [
                'label' => 'Active Employees',
                'value' => number_format((int) $counts['employees_active']),
                'hint' => number_format((int) $counts['employees_total']).' total employees',
                'tone' => 'primary',
            ];
        }

        if ($can['admin']) {
            $cards[] = [
                'label' => 'System Users',
                'value' => number_format(User::query()->count()),
                'hint' => number_format(User::query()->where('is_active', true)->count()).' active accounts',
                'tone' => 'secondary',
            ];
            $cards[] = [
                'label' => 'Audit Logs',
                'value' => number_format(AuditLog::query()->count()),
                'hint' => 'Tracked activity records',
                'tone' => 'slate',
            ];
        }

        return $cards;
    }

    /**
     * @param  array<string, bool>  $can
     * @param  array<string, int|float>  $counts
     * @return array<int, array{label: string, description: string, route: string|null, metric: string}>
     */
    private function buildQuickLinks(array $can, array $counts): array
    {
        $links = [];

        if ($can['customers']) {
            $links[] = [
                'label' => 'Customers',
                'description' => 'Manage customer records and contacts',
                'route' => route('customers.index'),
                'metric' => number_format((int) $counts['customers_total']).' records',
            ];
        }
        if ($can['trips']) {
            $links[] = [
                'label' => 'Trips',
                'description' => 'Monitor trip lifecycle and closures',
                'route' => route('trips.index'),
                'metric' => number_format((int) $counts['trips_total']).' visible',
            ];
        }
        if ($can['orders']) {
            $links[] = [
                'label' => 'Orders',
                'description' => 'Track order progress and assignment',
                'route' => route('orders.index'),
                'metric' => number_format((int) $counts['orders_total']).' visible',
            ];
        }
        if ($can['fleet']) {
            $links[] = [
                'label' => 'Fleet',
                'description' => 'Fleet units, status, and availability',
                'route' => route('fleet.index'),
                'metric' => number_format((int) $counts['fleets_total']).' units',
            ];
        }
        if ($can['drivers']) {
            $links[] = [
                'label' => 'Drivers',
                'description' => 'Driver profiles and assignment readiness',
                'route' => route('drivers.index'),
                'metric' => number_format((int) $counts['drivers_total']).' visible',
            ];
        }
        if ($can['fuel']) {
            $links[] = [
                'label' => 'Fuel Requisitions',
                'description' => 'Requisition flow and approvals',
                'route' => route('fuel.index'),
                'metric' => number_format((int) $counts['fuel_total']).' visible',
            ];
        }
        if ($can['hr']) {
            $links[] = [
                'label' => 'HR Employees',
                'description' => 'Employee records and contract statuses',
                'route' => route('hr.employees.index'),
                'metric' => number_format((int) $counts['employees_total']).' records',
            ];
        }
        if ($can['admin_users']) {
            $links[] = [
                'label' => 'Admin Users',
                'description' => 'Manage user accounts and role assignment',
                'route' => route('admin.users.index'),
                'metric' => number_format(User::query()->count()).' users',
            ];
        }
        if ($can['admin_roles']) {
            $links[] = [
                'label' => 'Roles & Permissions',
                'description' => 'Security model and direct permissions',
                'route' => route('admin.roles.index'),
                'metric' => number_format(Role::query()->count()).' roles',
            ];
        }
        if ($can['admin_logs']) {
            $links[] = [
                'label' => 'Audit Logs',
                'description' => 'Trace user actions and system activity',
                'route' => route('admin.logs.index'),
                'metric' => number_format(AuditLog::query()->count()).' records',
            ];
        }
        if ($can['admin_reports']) {
            $links[] = [
                'label' => 'Reports',
                'description' => 'Exportable operational analytics',
                'route' => route('admin.reports.index'),
                'metric' => 'Excel / PDF exports',
            ];
        }

        return $links;
    }

    /**
     * @param  array<string, bool>  $can
     * @param  \Illuminate\Database\Eloquent\Builder<Trip>|null  $tripQuery
     * @param  \Illuminate\Database\Eloquent\Builder<Order>|null  $orderQuery
     * @param  \Illuminate\Database\Eloquent\Builder<FuelRequisition>|null  $fuelQuery
     * @return array<string, mixed>
     */
    private function buildCharts(array $can, $tripQuery, $orderQuery, $fuelQuery): array
    {
        $charts = [
            'trip_trend_labels' => [],
            'trip_trend_values' => [],
            'order_status_labels' => ['Created', 'Processing', 'Assigned', 'Transportation', 'Incomplete', 'Completed'],
            'order_status_values' => [0, 0, 0, 0, 0, 0],
            'fuel_spend_labels' => [],
            'fuel_spend_values' => [],
            'approval_pipeline_labels' => ['Submitted', 'Supervisor Approved', 'Accountant Approved', 'Rejected'],
            'approval_pipeline_values' => [0, 0, 0, 0],
            'users_by_role_labels' => [],
            'users_by_role_values' => [],
        ];

        if ($can['trips'] && $tripQuery) {
            $days = collect(range(6, 0))->map(fn (int $offset) => Carbon::now()->subDays($offset));
            $charts['trip_trend_labels'] = $days->map(fn (Carbon $day) => $day->format('D'))->all();
            $charts['trip_trend_values'] = $days->map(
                fn (Carbon $day) => (clone $tripQuery)->whereDate('created_at', $day->toDateString())->count()
            )->all();
        }

        if ($can['orders'] && $orderQuery) {
            $statusSummary = (clone $orderQuery)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $charts['order_status_values'] = [
                (int) ($statusSummary['created'] ?? 0),
                (int) ($statusSummary['processing'] ?? 0),
                (int) ($statusSummary['assigned'] ?? 0),
                (int) ($statusSummary['transportation'] ?? 0),
                (int) ($statusSummary['incomplete'] ?? 0),
                (int) ($statusSummary['completed'] ?? 0),
            ];
        }

        if ($can['fuel'] && $fuelQuery) {
            $months = collect(range(5, 0))->map(fn (int $offset) => Carbon::now()->subMonths($offset)->startOfMonth())
                ->push(Carbon::now()->startOfMonth());

            $charts['fuel_spend_labels'] = $months->map(fn (Carbon $month) => $month->format('M'))->all();
            $charts['fuel_spend_values'] = $months->map(function (Carbon $month) use ($fuelQuery): float {
                return (float) ((clone $fuelQuery)
                    ->where('status', 'accountant_approved')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_amount'));
            })->all();

            $charts['approval_pipeline_values'] = [
                (clone $fuelQuery)->where('status', 'submitted')->count(),
                (clone $fuelQuery)->where('status', 'supervisor_approved')->count(),
                (clone $fuelQuery)->where('status', 'accountant_approved')->count(),
                (clone $fuelQuery)->whereIn('status', ['supervisor_rejected', 'accountant_rejected'])->count(),
            ];
        }

        return $charts;
    }

    /**
     * @return array{
     *   totals: array<string, int>,
     *   users_by_role_labels: array<int, string>,
     *   users_by_role_values: array<int, int>,
     *   recent_logs: \Illuminate\Support\Collection<int, AuditLog>
     * }
     */
    private function buildAdminOverview(): array
    {
        $roles = Role::query()->withCount('users')->orderByDesc('users_count')->get(['id', 'name']);

        return [
            'totals' => [
                'users' => User::query()->count(),
                'users_active' => User::query()->where('is_active', true)->count(),
                'users_inactive' => User::query()->where('is_active', false)->count(),
                'roles' => Role::query()->count(),
                'permissions' => Permission::query()->count(),
                'customers' => Customer::query()->count(),
                'fleets' => Fleet::query()->count(),
                'drivers' => Driver::query()->count(),
                'employees' => Employee::query()->count(),
                'trips' => Trip::query()->count(),
                'orders' => Order::query()->count(),
                'fuel_requisitions' => FuelRequisition::query()->count(),
                'audit_logs' => AuditLog::query()->count(),
            ],
            'users_by_role_labels' => $roles->pluck('name')->values()->all(),
            'users_by_role_values' => $roles->pluck('users_count')->map(fn ($value) => (int) $value)->values()->all(),
            'recent_logs' => AuditLog::query()
                ->with('user:id,name')
                ->latest('id')
                ->limit(8)
                ->get(),
        ];
    }

    private function resolvePreset(User $user): string
    {
        if ($user->can('admin.dashboard.view_all')) {
            return 'admin';
        }

        if ($user->can('fuel.approve.accounting') || $user->can('fuel.approve.supervisor')) {
            return 'approvals';
        }

        if ($user->can('fuel.create')) {
            return 'fuel';
        }

        if ($user->can('fleet.assign') || $user->can('fleet.view')) {
            return 'fleet';
        }

        if ($user->can('orders.create')) {
            return 'orders';
        }

        if ($user->can('hr.employees.view') || $user->can('hr.employees.manage')) {
            return 'hr';
        }

        return 'ops';
    }
}

