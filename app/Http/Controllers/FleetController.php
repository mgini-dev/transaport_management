<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        return view('fleet.index', [
            'fleets' => Fleet::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fleet_code' => ['required', 'string', 'max:100', 'unique:fleets,fleet_code'],
            'plate_number' => ['required', 'string', 'max:100', 'unique:fleets,plate_number'],
            'capacity_tons' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
        ]);

        $fleet = Fleet::query()->create($data);

        $this->auditLogService->record(
            action: 'fleet.created',
            user: $request->user(),
            loggable: $fleet,
            context: ['fleet_code' => $fleet->fleet_code],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Fleet created.');
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
