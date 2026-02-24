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

        return view('orders.legs', [
            'order' => $order,
            'legs' => $order->legs()->with(['fleet', 'driver'])->orderBy('leg_sequence')->get(),
            'fleets' => Fleet::query()->whereNotIn('id', $availableFleetIds)->orderBy('fleet_code')->get(),
            'drivers' => Driver::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $orderId): RedirectResponse
    {
        $order = Order::query()->with('trip')->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('manageLegs', $order);

        $data = $request->validate([
            'fleet_id' => ['required', 'string'],
            'driver_id' => ['nullable', 'string'],
            'origin_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fleetId = EncryptedId::decode($data['fleet_id']);
        $driverId = filled($data['driver_id']) ? EncryptedId::decode($data['driver_id']) : null;

        if (! $fleetId) {
            return back()->withErrors(['fleet_id' => 'Invalid fleet identifier.'])->withInput();
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

        return back()->with('status', 'Fleet leg assigned.');
    }

    public function complete(Request $request, string $legId): RedirectResponse
    {
        $leg = OrderLeg::query()->with('order')->findOrFail(EncryptedId::decode($legId));
        $this->authorize('manageLegs', $leg->order);
        $leg = $this->fleetAssignmentService->complete($leg);

        $this->auditLogService->record(
            action: 'order.leg.completed',
            user: $request->user(),
            loggable: $leg,
            context: ['order_id' => $leg->order_id, 'fleet_id' => $leg->fleet_id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Fleet leg marked completed.');
    }
}
