<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Services\AuditLogService;
use App\Services\FleetAssignmentService;
use App\Support\EncryptedId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OrderLegController extends Controller
{
    public function __construct(
        private readonly FleetAssignmentService $fleetAssignmentService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(string $orderId): View
    {
        $order = Order::query()
            ->with(['legs.fleet', 'legs.driver', 'trip'])
            ->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('manageLegs', $order);

        $availableFleetIds = OrderLeg::query()
            ->where('status', 'active')
            ->pluck('fleet_id')
            ->all();
        $busyDriverIds = OrderLeg::query()
            ->where('status', 'active')
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->all();

        return view('orders.legs', [
            'order' => $order,
            'legs' => $order->legs()->with(['fleet', 'driver'])->orderBy('leg_sequence')->get(),
            'fleets' => Fleet::query()->whereNotIn('id', $availableFleetIds)->orderBy('fleet_code')->get(),
            'drivers' => Driver::query()
                ->with('fleet')
                ->where('is_active', true)
                ->whereNotIn('id', $busyDriverIds)
                ->orderBy('name')
                ->get(),
            'canViewDistance' => auth()->user()?->can('viewDistance', $order) ?? false,
        ]);
    }

    public function store(Request $request, string $orderId): RedirectResponse
    {
        $order = Order::query()->with('trip')->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('manageLegs', $order);

        $data = $request->validate([
            'fleet_id' => ['required', 'string'],
            'driver_id' => ['required', 'string'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fleetId = EncryptedId::decode($data['fleet_id']);
        $driverId = EncryptedId::decode($data['driver_id']);

        if (! $fleetId) {
            return back()->withErrors(['fleet_id' => 'Invalid fleet identifier.'])->withInput();
        }

        if (! $driverId) {
            return back()->withErrors(['driver_id' => 'Invalid driver identifier.'])->withInput();
        }

        try {
            $leg = $this->fleetAssignmentService->assign($order, [
                ...$data,
                'fleet_id' => $fleetId,
                'driver_id' => $driverId,
            ], $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['fleet_id' => $exception->getMessage()])->withInput();
        }

        $this->auditLogService->record(
            action: 'order.leg.assigned',
            user: $request->user(),
            loggable: $leg,
            context: ['order_id' => $order->id, 'fleet_id' => $leg->fleet_id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Fleet leg assigned and driver mapped to the selected fleet.');
    }

    public function complete(Request $request, string $legId): RedirectResponse
    {
        $leg = OrderLeg::query()->with('order')->findOrFail(EncryptedId::decode($legId));
        $this->authorize('manageLegs', $leg->order);
        return back()->withErrors([
            'leg' => 'Leg completion is automatic when the whole order is completed.',
        ]);
    }
}
