<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\OrderLeg;
use App\Repositories\DriverRepository;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use App\Support\NmisDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepository $driverRepository,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Driver::class);

        if ($request->ajax()) {
            $skip = (int) $request->integer('skip', 0);
            $take = min((int) $request->integer('take', 15), 100);

            return response()->json([
                'data' => $this->driverRepository->listForIndex(
                    user: $request->user(),
                    skip: $skip,
                    take: $take,
                    search: $request->string('search')->toString() ?: null,
                    active: $request->string('active')->toString()
                )->map(function (Driver $driver) {
                    return [
                        'id' => $driver->encrypted_id,
                        'name' => $driver->name,
                        'license_number' => $driver->license_number,
                        'mobile_number' => $driver->mobile_number,
                        'driver_address' => $driver->driver_address,
                        'contact1_name' => $driver->contact1_name,
                        'contact1_phone' => $driver->contact1_phone,
                        'contact1_address' => $driver->contact1_address,
                        'contact2_name' => $driver->contact2_name,
                        'contact2_phone' => $driver->contact2_phone,
                        'contact2_address' => $driver->contact2_address,
                        'fleet' => $driver->fleet?->fleet_code.' - '.$driver->fleet?->plate_number,
                        'is_active' => $driver->is_active,
                    ];
                }),
            ]);
        }

        $search = $request->string('search')->toString();
        $active = $request->string('active')->toString();

        $baseQuery = NmisDataScope::ownOrAll(
            query: Driver::query(),
            user: $request->user(),
            ownerColumn: 'created_by',
            viewAllPermission: 'drivers.view_all'
        );

        $statsQuery = clone $baseQuery;
        $listQuery = (clone $baseQuery)
            ->with('fleet')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('license_number', 'like', '%'.$search.'%')
                        ->orWhere('mobile_number', 'like', '%'.$search.'%')
                        ->orWhere('contact1_name', 'like', '%'.$search.'%')
                        ->orWhere('contact1_phone', 'like', '%'.$search.'%')
                        ->orWhere('contact2_name', 'like', '%'.$search.'%')
                        ->orWhere('contact2_phone', 'like', '%'.$search.'%');
                });
            })
            ->when($active !== '', fn ($query) => $query->where('is_active', (bool) $active))
            ->latest();

        return view('drivers.index', [
            'drivers' => $listQuery->paginate(20)->withQueryString(),
            'driverStats' => [
                'total' => (clone $statsQuery)->count(),
                'active' => (clone $statsQuery)->where('is_active', true)->count(),
                'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
                'with_fleet' => (clone $statsQuery)->whereNotNull('fleet_id')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Driver::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:drivers,license_number'],
            'mobile_number' => ['required', 'string', 'max:50'],
            'driver_address' => ['required', 'string'],
            'contact1_name' => ['required', 'string', 'max:255'],
            'contact1_phone' => ['required', 'string', 'max:50'],
            'contact1_address' => ['required', 'string'],
            'contact2_name' => ['nullable', 'string', 'max:255'],
            'contact2_phone' => ['nullable', 'string', 'max:50'],
            'contact2_address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $driver = Driver::query()->create([
            'fleet_id' => null,
            'name' => $data['name'],
            'license_number' => $data['license_number'],
            'mobile_number' => $data['mobile_number'],
            'driver_address' => $data['driver_address'],
            'contact1_name' => $data['contact1_name'],
            'contact1_phone' => $data['contact1_phone'],
            'contact1_address' => $data['contact1_address'],
            'contact2_name' => $data['contact2_name'] ?? null,
            'contact2_phone' => $data['contact2_phone'] ?? null,
            'contact2_address' => $data['contact2_address'] ?? null,
            'is_active' => (bool) $data['is_active'],
            'created_by' => $request->user()->id,
        ]);

        $this->auditLogService->record(
            action: 'driver.created',
            user: $request->user(),
            loggable: $driver,
            context: ['license_number' => $driver->license_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Driver created successfully.');
    }

    public function update(Request $request, string $driverId): RedirectResponse
    {
        $driver = Driver::query()->findOrFail(EncryptedId::decode($driverId));
        $this->authorize('update', $driver);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:drivers,license_number,'.$driver->id],
            'mobile_number' => ['required', 'string', 'max:50'],
            'driver_address' => ['required', 'string'],
            'contact1_name' => ['required', 'string', 'max:255'],
            'contact1_phone' => ['required', 'string', 'max:50'],
            'contact1_address' => ['required', 'string'],
            'contact2_name' => ['nullable', 'string', 'max:255'],
            'contact2_phone' => ['nullable', 'string', 'max:50'],
            'contact2_address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $driver->update([
            'name' => $data['name'],
            'license_number' => $data['license_number'],
            'mobile_number' => $data['mobile_number'],
            'driver_address' => $data['driver_address'],
            'contact1_name' => $data['contact1_name'],
            'contact1_phone' => $data['contact1_phone'],
            'contact1_address' => $data['contact1_address'],
            'contact2_name' => $data['contact2_name'] ?? null,
            'contact2_phone' => $data['contact2_phone'] ?? null,
            'contact2_address' => $data['contact2_address'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        $this->auditLogService->record(
            action: 'driver.updated',
            user: $request->user(),
            loggable: $driver,
            context: ['is_active' => $driver->is_active],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Driver updated successfully.');
    }

    public function destroy(Request $request, string $driverId): RedirectResponse
    {
        $driver = Driver::query()->findOrFail(EncryptedId::decode($driverId));
        $this->authorize('delete', $driver);

        if (OrderLeg::query()->where('driver_id', $driver->id)->exists()) {
            return back()->withErrors(['driver' => 'Cannot delete driver with assigned trip legs.']);
        }

        $driverName = $driver->name;
        $driver->delete();

        $this->auditLogService->record(
            action: 'driver.deleted',
            user: $request->user(),
            loggable: null,
            context: ['name' => $driverName],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Driver deleted successfully.');
    }
}
