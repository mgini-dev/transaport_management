<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\FuelBalance;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Services\AuditLogService;
use App\Services\DistanceService;
use App\Services\FuelRequisitionService;
use App\Support\EncryptedId;
use App\Support\NmisDataScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class FuelRequisitionController extends Controller
{
    public function __construct(
        private readonly FuelRequisitionService $fuelRequisitionService,
        private readonly AuditLogService $auditLogService,
        private readonly DistanceService $distanceService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FuelRequisition::class);

        $orders = Order::query()
            ->with(['legs:id,order_id,fleet_id'])
            ->where('status', 'assigned')
            ->orderByDesc('id')
            ->take(100)
            ->get();
        $completedOrders = Order::query()
            ->with(['legs:id,order_id,fleet_id'])
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->take(100)
            ->get();
        $fleets = Fleet::query()->orderBy('fleet_code')->get();

        $fleetBalances = FuelBalance::query()
            ->select('fleet_id', 'remaining_litres')
            ->whereIn('fleet_id', $fleets->pluck('id')->all())
            ->latest('id')
            ->get()
            ->groupBy('fleet_id')
            ->map(fn ($group) => (float) $group->first()->remaining_litres);

        $orderFleetMap = $orders
            ->concat($completedOrders)
            ->unique('id')
            ->mapWithKeys(function (Order $order) {
                return [(string) $order->id => $order->legs->pluck('fleet_id')->filter()->unique()->map(
                fn ($id) => (int) $id
            )->values()->all()];
            });

        $availableStatuses = [
            'submitted',
            'supervisor_approved',
            'supervisor_rejected',
            'accountant_approved',
            'accountant_rejected',
        ];
        $activeStatus = $request->string('status')->toString() ?: 'all';
        if (! in_array($activeStatus, ['all', ...$availableStatuses], true)) {
            $activeStatus = 'all';
        }

        $baseRequisitionQuery = NmisDataScope::ownOrAll(
            query: FuelRequisition::query(),
            user: $request->user(),
            ownerColumn: 'requested_by',
            viewAllPermission: 'fuel.view_all'
        );
        $statusCounts = (clone $baseRequisitionQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count);
        $totalRequisitions = (clone $baseRequisitionQuery)->count();
        $totalAmount = (float) (clone $baseRequisitionQuery)->sum('total_amount');

        $requisitions = (clone $baseRequisitionQuery)
            ->when($activeStatus !== 'all', fn ($query) => $query->where('status', $activeStatus))
            ->with(['order.trip', 'order.customer', 'order.legs.fleet', 'fleet', 'requester', 'supervisor', 'accountant'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('fuel.index', [
            'orders' => $orders,
            'fleets' => $fleets,
            'completedOrders' => $completedOrders,
            'fleetBalances' => $fleetBalances,
            'orderFleetMap' => $orderFleetMap,
            'requisitions' => $requisitions,
            'activeStatus' => $activeStatus,
            'statusTabs' => [
                ['key' => 'all', 'label' => 'All', 'count' => $totalRequisitions],
                ['key' => 'submitted', 'label' => 'Submitted', 'count' => (int) ($statusCounts['submitted'] ?? 0)],
                ['key' => 'supervisor_approved', 'label' => 'Supervisor Approved', 'count' => (int) ($statusCounts['supervisor_approved'] ?? 0)],
                ['key' => 'supervisor_rejected', 'label' => 'Supervisor Rejected', 'count' => (int) ($statusCounts['supervisor_rejected'] ?? 0)],
                ['key' => 'accountant_approved', 'label' => 'Accountant Approved', 'count' => (int) ($statusCounts['accountant_approved'] ?? 0)],
                ['key' => 'accountant_rejected', 'label' => 'Accountant Rejected', 'count' => (int) ($statusCounts['accountant_rejected'] ?? 0)],
            ],
            'statusCounts' => $statusCounts,
            'totalRequisitions' => $totalRequisitions,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function show(string $requisitionId): View
    {
        $decodedId = EncryptedId::decode($requisitionId);
        $requisition = FuelRequisition::query()
            ->with([
                'order.trip',
                'order.customer',
                'order.legs.fleet',
                'order.legs.driver',
                'fleet',
                'requester',
                'supervisor',
                'accountant',
            ])
            ->findOrFail($decodedId);
        $this->authorize('view', $requisition);

        return view('fuel.show', [
            'requisition' => $requisition,
            'canSupervisorAction' => auth()->user()?->can('supervisorDecision', $requisition) && $requisition->status === 'submitted',
            'canAccountantAction' => auth()->user()?->can('accountantDecision', $requisition) && $requisition->status === 'supervisor_approved',
            'canViewOrder' => $requisition->order && auth()->user()?->can('view', $requisition->order),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FuelRequisition::class);

        $data = $request->validate([
            'requisition_type' => ['required', 'in:order_based,fleet_only'],
            'order_id' => ['nullable', 'string'],
            'fleet_id' => ['required', 'string'],
            'fuel_station' => ['required', 'string', 'max:255'],
            'additional_distance_km' => ['nullable', 'numeric', 'min:0'],
            'fuel_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_channel' => ['required', 'string', 'max:100'],
            'payment_account' => ['required', 'string', 'max:100'],
            'origin_address' => ['nullable', 'string'],
            'destination_address' => ['nullable', 'string'],
        ]);

        $orderId = filled($data['order_id'] ?? null) ? EncryptedId::decode((string) $data['order_id']) : null;
        $fleetId = EncryptedId::decode($data['fleet_id']);

        if (! $fleetId) {
            return back()->withErrors(['order_id' => 'Invalid order or fleet identifier.'])->withInput();
        }

        if ($data['requisition_type'] === 'order_based' && ! $orderId) {
            return back()->withErrors(['order_id' => 'Please select a valid assigned order.'])->withInput();
        }

        if (($orderId && ! Order::query()->whereKey($orderId)->exists()) || ! Fleet::query()->whereKey($fleetId)->exists()) {
            return back()->withErrors(['order_id' => 'Order or fleet could not be found.'])->withInput();
        }

        try {
            $requisition = $this->fuelRequisitionService->create([
                ...$data,
                'order_id' => $orderId,
                'fleet_id' => $fleetId,
            ], $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['order_id' => $exception->getMessage()])->withInput();
        }

        $this->auditLogService->record(
            action: 'fuel.requisition.created',
            user: $request->user(),
            loggable: $requisition,
            context: ['status' => $requisition->status],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Fuel requisition #{$requisition->id} created.");
    }

    public function storeBalance(Request $request): RedirectResponse
    {
        $this->authorize('create', FuelRequisition::class);

        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'fleet_id' => ['required', 'string'],
            'remaining_litres' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $orderId = EncryptedId::decode($data['order_id']);
        $fleetId = EncryptedId::decode($data['fleet_id']);

        if (! $orderId || ! $fleetId) {
            return back()->withErrors(['order_id' => 'Invalid order or fleet identifier.'])->withInput();
        }

        try {
            $balance = $this->fuelRequisitionService->recordBalance([
                ...$data,
                'order_id' => $orderId,
                'fleet_id' => $fleetId,
            ], $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['remaining_litres' => $exception->getMessage()])->withInput();
        }

        $this->auditLogService->record(
            action: 'fuel.balance.recorded',
            user: $request->user(),
            loggable: $balance,
            context: ['order_id' => $balance->order_id, 'fleet_id' => $balance->fleet_id, 'remaining_litres' => $balance->remaining_litres],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Fuel balance recorded successfully.');
    }

    public function estimateDistance(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', FuelRequisition::class);

        $data = $request->validate([
            'origin_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
        ]);

        $distance = $this->distanceService->calculateKm($data['origin_address'], $data['destination_address']);
        if ($distance === null) {
            return response()->json([
                'message' => 'Distance could not be calculated right now. Please try again shortly.',
            ], 422);
        }

        return response()->json(['distance_km' => $distance]);
    }

    public function supervisorDecision(Request $request, string $requisitionId): RedirectResponse
    {
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ]);

        $requisition = FuelRequisition::query()->findOrFail(EncryptedId::decode($requisitionId));
        $this->authorize('supervisorDecision', $requisition);
        $requisition = $this->fuelRequisitionService->supervisorDecision($requisition, (bool) $data['approved'], $request->user(), $data['remarks'] ?? null);

        $this->auditLogService->record(
            action: 'fuel.requisition.supervisor_decision',
            user: $request->user(),
            loggable: $requisition,
            context: ['status' => $requisition->status],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Supervisor decision saved for requisition #{$requisition->id}.");
    }

    public function accountantDecision(Request $request, string $requisitionId): RedirectResponse
    {
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ]);

        $requisition = FuelRequisition::query()->findOrFail(EncryptedId::decode($requisitionId));
        $this->authorize('accountantDecision', $requisition);
        $requisition = $this->fuelRequisitionService->accountantDecision($requisition, (bool) $data['approved'], $request->user(), $data['remarks'] ?? null);

        $this->auditLogService->record(
            action: 'fuel.requisition.accountant_decision',
            user: $request->user(),
            loggable: $requisition,
            context: ['status' => $requisition->status],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Accountant decision saved for requisition #{$requisition->id}.");
    }
}
