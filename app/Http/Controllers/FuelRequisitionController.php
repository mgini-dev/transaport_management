<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Services\AuditLogService;
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
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', FuelRequisition::class);

        return view('fuel.index', [
            'orders' => Order::query()->orderByDesc('id')->take(50)->get(),
            'fleets' => Fleet::query()->orderBy('fleet_code')->get(),
            'requisitions' => NmisDataScope::ownOrAll(
                query: FuelRequisition::query(),
                user: request()->user(),
                ownerColumn: 'requested_by',
                viewAllPermission: 'fuel.view_all'
            )->with(['order', 'fleet', 'requester', 'supervisor', 'accountant'])->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FuelRequisition::class);

        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'fleet_id' => ['required', 'string'],
            'fuel_station' => ['required', 'string', 'max:255'],
            'additional_litres' => ['required', 'numeric', 'min:0'],
            'fuel_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_channel' => ['required', 'string', 'max:100'],
            'payment_account' => ['required', 'string', 'max:100'],
        ]);

        $orderId = EncryptedId::decode($data['order_id']);
        $fleetId = EncryptedId::decode($data['fleet_id']);

        if (! $orderId || ! $fleetId) {
            return back()->withErrors(['order_id' => 'Invalid order or fleet identifier.'])->withInput();
        }

        if (! Order::query()->whereKey($orderId)->exists() || ! Fleet::query()->whereKey($fleetId)->exists()) {
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
