<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            'plate_number' => ['required', 'string', 'max:100', 'unique:fleets,plate_number'],
            'capacity_tons' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
        ];

        if (Schema::hasColumn('fleets', 'trailer_number')) {
            $rules['trailer_number'] = ['required', 'string', 'max:100', 'unique:fleets,trailer_number'];
        }

        $data = $request->validate($rules);

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

    public function update(Request $request, string $fleetId): RedirectResponse
    {
        $fleet = Fleet::query()->findOrFail(EncryptedId::decode($fleetId));

        $rules = [
            'fleet_code' => ['required', 'string', 'max:100', 'unique:fleets,fleet_code,'.$fleet->id],
            'plate_number' => ['required', 'string', 'max:100', 'unique:fleets,plate_number,'.$fleet->id],
            'capacity_tons' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
        ];

        if (Schema::hasColumn('fleets', 'trailer_number')) {
            $rules['trailer_number'] = ['required', 'string', 'max:100', 'unique:fleets,trailer_number,'.$fleet->id];
        }

        $data = $request->validate($rules);

        $fleet->update($data);

        $this->auditLogService->record(
            action: 'fleet.updated',
            user: $request->user(),
            loggable: $fleet,
            context: ['fleet_code' => $fleet->fleet_code, 'trailer_number' => $fleet->trailer_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('fleet.index')->with('status', 'Fleet updated.');
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
