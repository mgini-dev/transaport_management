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
        $tripQuery = NmisDataScope::ownOrAll(Trip::query(), $user, 'created_by', 'trips.view_all');
        $orderQuery = NmisDataScope::ownOrAll(Order::query(), $user, 'created_by', 'orders.view_all');
        $fuelQuery = NmisDataScope::ownOrAll(FuelRequisition::query(), $user, 'requested_by', 'fuel.view_all');
        $orderStatusSummary = (clone $orderQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $days = collect(range(6, 0))->map(fn (int $offset) => Carbon::now()->subDays($offset));
        $tripTrend = $days->map(function (Carbon $day) use ($tripQuery) {
            return (clone $tripQuery)->whereDate('created_at', $day->toDateString())->count();
        })->all();

        return view('dashboard', [
            'stats' => [
                'open_trips' => (clone $tripQuery)->where('status', 'open')->count(),
                'closed_trips' => (clone $tripQuery)->where('status', 'closed')->count(),
                'orders_in_progress' => (clone $orderQuery)->whereIn('status', ['created', 'processing', 'assigned'])->count(),
                'fuel_pending' => (clone $fuelQuery)->whereIn('status', ['submitted', 'supervisor_approved'])->count(),
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
            ],
            'notifications' => $user->notifications()->latest()->take(10)->get(),
        ]);
    }
}
