<?php

namespace App\Http\Controllers;

use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\Trip;
use App\Support\NmisDataScope;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $preset = $this->resolvePreset($user);
        $tripQuery = NmisDataScope::ownOrAll(Trip::query(), $user, 'created_by', 'trips.view_all');
        $orderQuery = NmisDataScope::ownOrAll(Order::query(), $user, 'created_by', 'orders.view_all');
        $fuelQuery = NmisDataScope::ownOrAll(FuelRequisition::query(), $user, 'requested_by', 'fuel.view_all');
        $orderStatusSummary = (clone $orderQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalOrders = (clone $orderQuery)->count();
        $completedOrders = (clone $orderQuery)->where('status', 'completed')->count();
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        $days = collect(range(6, 0))->map(fn (int $offset) => Carbon::now()->subDays($offset));
        $tripTrend = $days->map(function (Carbon $day) use ($tripQuery) {
            return (clone $tripQuery)->whereDate('created_at', $day->toDateString())->count();
        })->all();
        $months = collect(range(5, 0))->map(fn (int $offset) => Carbon::now()->subMonths($offset)->startOfMonth())
            ->push(Carbon::now()->startOfMonth());
        $fuelSpendTrend = $months->map(function (Carbon $month) use ($fuelQuery) {
            return (float) ((clone $fuelQuery)
                ->where('status', 'accountant_approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount'));
        })->all();
        $approvalPipeline = [
            'submitted' => (clone $fuelQuery)->where('status', 'submitted')->count(),
            'supervisor_approved' => (clone $fuelQuery)->where('status', 'supervisor_approved')->count(),
            'accountant_approved' => (clone $fuelQuery)->where('status', 'accountant_approved')->count(),
            'rejected' => (clone $fuelQuery)->whereIn('status', ['supervisor_rejected', 'accountant_rejected'])->count(),
        ];

        return view('dashboard', [
            'stats' => [
                'open_trips' => (clone $tripQuery)->where('status', 'open')->count(),
                'closed_trips' => (clone $tripQuery)->where('status', 'closed')->count(),
                'orders_in_progress' => (clone $orderQuery)->whereIn('status', ['created', 'processing', 'assigned'])->count(),
                'fuel_pending' => (clone $fuelQuery)->whereIn('status', ['submitted', 'supervisor_approved'])->count(),
                'completion_rate' => $completionRate,
            ],
            'charts' => [
                'order_status' => [
                    'created' => (int) ($orderStatusSummary['created'] ?? 0),
                    'processing' => (int) ($orderStatusSummary['processing'] ?? 0),
                    'assigned' => (int) ($orderStatusSummary['assigned'] ?? 0),
                    'completed' => (int) ($orderStatusSummary['completed'] ?? 0),
                ],
                'trip_trend_labels' => $days->map(fn (Carbon $day) => $day->format('D'))->all(),
                'trip_trend_values' => $tripTrend,
                'fuel_spend_labels' => $months->map(fn (Carbon $month) => $month->format('M'))->all(),
                'fuel_spend_values' => $fuelSpendTrend,
                'approval_pipeline' => $approvalPipeline,
            ],
            'preset' => $preset,
            'widgets' => [
                'trip_trend' => in_array($preset, ['admin', 'fleet', 'ops', 'orders'], true),
                'order_status' => in_array($preset, ['admin', 'fleet', 'ops', 'orders'], true),
                'fuel_spend' => in_array($preset, ['admin', 'fuel', 'approvals'], true),
                'approval_pipeline' => in_array($preset, ['admin', 'fuel', 'approvals'], true),
            ],
            'notifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    private function resolvePreset($user): string
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

        return 'ops';
    }
}
