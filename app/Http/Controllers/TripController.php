<?php

namespace App\Http\Controllers;

use App\Http\Resources\TripListResource;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\AuditLogService;
use App\Services\TripService;
use App\Support\EncryptedId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly TripService $tripService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Trip::class);

        if ($request->ajax()) {
            $skip = (int) $request->integer('skip', 0);
            $take = min((int) $request->integer('take', 15), 100);

            $trips = $this->tripRepository->listForIndex($request->user(), $skip, $take);

            return response()->json(['data' => TripListResource::collection($trips)]);
        }

        return view('trips.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Trip::class);

        $trip = $this->tripService->create($request->user());

        $this->auditLogService->record(
            action: 'trip.created',
            user: $request->user(),
            loggable: $trip,
            context: ['trip_number' => $trip->trip_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Trip {$trip->trip_number} created.");
    }

    public function close(Request $request, string $tripId): RedirectResponse
    {
        $trip = Trip::query()->findOrFail(EncryptedId::decode($tripId));
        $this->authorize('close', $trip);

        $this->tripService->close($trip, $request->user());

        $this->auditLogService->record(
            action: 'trip.closed',
            user: $request->user(),
            loggable: $trip,
            context: ['trip_number' => $trip->trip_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Trip {$trip->trip_number} closed.");
    }
}
