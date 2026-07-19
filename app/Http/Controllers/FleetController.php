<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\MaintenanceLog;
use App\Models\FleetDriverHistory;
use App\Services\FleetService;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FleetController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly FleetService $fleetService
    ) {
    }

    public function index(): View
    {
        return view('fleet.index', [
            'fleets' => Fleet::query()->latest()->paginate(20),
            'needsServiceCount' => Fleet::query()->needsService()->count(),
        ]);
    }

    public function maintenance(): View
    {
        $logs = MaintenanceLog::query()->with('fleet', 'recordedBy')->latest('performed_at');
        
        return view('fleet.maintenance', [
            'logs' => $logs->paginate(20),
            'totalCost' => MaintenanceLog::sum('cost'),
            'recordsCount' => MaintenanceLog::count(),
            'needsServiceCount' => Fleet::query()->needsService()->count(),
        ]);
    }

    public function assignments(): View
    {
        return view('fleet.assignments', [
            'history' => FleetDriverHistory::query()->with('fleet', 'driver')->latest('assigned_at')->paginate(20),
            'activePairings' => FleetDriverHistory::query()->whereNull('unassigned_at')->count(),
            'totalAssignments' => FleetDriverHistory::count(),
        ]);
    }

    public function alerts(): View
    {
        return view('fleet.alerts', [
            'fleets' => Fleet::query()->needsService()->latest()->paginate(20),
        ]);
    }

    public function show(string $fleetId): View
    {
        $fleet = Fleet::query()
            ->with(['maintenanceLogs.recordedBy', 'driverHistory.driver'])
            ->findOrFail(EncryptedId::decode($fleetId));

        return view('fleet.show', [
            'fleet' => $fleet,
            'efficiency' => $this->fleetService->calculateEfficiency($fleet),
            'recentLogs' => $fleet->maintenanceLogs()->take(5)->get(),
            'history' => $fleet->driverHistory()->take(10)->get(),
        ]);
    }

    public function edit(string $fleetId): View
    {
        return view('fleet.edit', [
            'fleet' => Fleet::query()->findOrFail(EncryptedId::decode($fleetId)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'fleet_code' => ['required', 'string', 'max:100', 'unique:fleets,fleet_code'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:100', 'unique:fleets,plate_number'],
            'trailer_number' => ['nullable', 'string', 'max:100', 'unique:fleets,trailer_number'],
            'capacity_tons' => ['required', 'numeric', 'min:0'],
            'current_odometer' => ['required', 'numeric', 'min:0'],
            'oil_change_interval_km' => ['required', 'integer', 'min:100'],
            'fuel_consumption_benchmark' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        // Calculate next service due
        $data['last_service_odometer'] = $data['current_odometer'];
        $data['next_service_due_km'] = $data['current_odometer'] + $data['oil_change_interval_km'];

        $fleet = Fleet::query()->create($data);

        $this->auditLogService->record(
            action: 'fleet.created',
            user: $request->user(),
            loggable: $fleet,
            context: ['fleet_code' => $fleet->fleet_code],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('fleet.index')->with('status', 'Fleet created.');
    }

    public function update(Request $request, string $fleetId): RedirectResponse
    {
        $fleet = Fleet::query()->findOrFail(EncryptedId::decode($fleetId));

        $rules = [
            'fleet_code' => ['required', 'string', 'max:100', 'unique:fleets,fleet_code,'.$fleet->id],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:100', 'unique:fleets,plate_number,'.$fleet->id],
            'trailer_number' => ['nullable', 'string', 'max:100', 'unique:fleets,trailer_number,'.$fleet->id],
            'capacity_tons' => ['required', 'numeric', 'min:0'],
            'current_odometer' => ['required', 'numeric', 'min:'.$fleet->current_odometer],
            'oil_change_interval_km' => ['required', 'integer', 'min:100'],
            'fuel_consumption_benchmark' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        // Update next service if interval changed
        if ($fleet->oil_change_interval_km != $data['oil_change_interval_km']) {
            $data['next_service_due_km'] = $fleet->last_service_odometer + $data['oil_change_interval_km'];
        }

        $fleet->update($data);

        $this->auditLogService->record(
            action: 'fleet.updated',
            user: $request->user(),
            loggable: $fleet,
            context: ['fleet_code' => $fleet->fleet_code],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('fleet.index')->with('status', 'Fleet updated.');
    }

    public function logMaintenance(Request $request, string $fleetId): RedirectResponse
    {
        $fleet = Fleet::query()->findOrFail(EncryptedId::decode($fleetId));

        $data = $request->validate([
            'service_type' => ['required', 'string'],
            'odometer_reading' => ['required', 'numeric', 'min:'.$fleet->last_service_odometer],
            'cost' => ['required', 'numeric', 'min:0'],
            'performed_at' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.category' => ['required_with:items', 'string'],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.cost' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.lifespan_km' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->fleetService->recordMaintenance($fleet, $data, $request->user());

        return back()->with('status', 'Maintenance record saved successfully.');
    }

    public function deferMaintenance(Request $request, string $fleetId): RedirectResponse
    {
        $fleet = Fleet::query()->findOrFail(EncryptedId::decode($fleetId));

        $data = $request->validate([
            'extra_km' => ['required', 'numeric', 'min:100'],
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->fleetService->deferMaintenance($fleet, $data['extra_km'], $data['reason']);

        return back()->with('status', 'Service has been deferred.');
    }

    public function alertsCheck()
    {
        $overdue = Fleet::overdue()->count();
        $approaching = Fleet::all()->filter(fn($f) => $f->serviceStatus() === 'approaching')->count();

        return response()->json([
            'overdue' => $overdue,
            'approaching' => $approaching,
        ]);
    }

    public function destroy(Request $request, string $fleetId): RedirectResponse
    {
        $fleet = Fleet::query()->withCount('legs')->findOrFail(EncryptedId::decode($fleetId));

        if ($fleet->legs_count > 0) {
            return back()->withErrors(['fleet' => 'Cannot delete fleet that has assigned trip legs.']);
        }

        $fleetCode = $fleet->fleet_code;
        $fleet->delete();

        $this->auditLogService->record(
            action: 'fleet.deleted',
            user: $request->user(),
            loggable: null,
            context: ['fleet_code' => $fleetCode],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Fleet deleted.');
    }
}
