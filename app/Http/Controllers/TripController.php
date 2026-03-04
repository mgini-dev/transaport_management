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
use Illuminate\Support\Collection;
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

            $trips = $this->tripRepository->listForIndex(
                user: $request->user(),
                skip: $skip,
                take: $take,
                status: $request->string('status')->toString() ?: null,
                search: $request->string('search')->toString() ?: null
            );

            $total = $this->tripRepository->countForIndex(
                user: $request->user(),
                status: $request->string('status')->toString() ?: null,
                search: $request->string('search')->toString() ?: null
            );

            $stats = $this->tripRepository->statsForUser($request->user());

            return response()->json([
                'data' => TripListResource::collection($trips),
                'meta' => [
                    'total' => $total,
                    'skip' => $skip,
                    'take' => $take,
                ],
                'stats' => $stats,
            ]);
        }

        return view('trips.index');
    }

    public function show(Request $request, string $tripId): View
    {
        $trip = Trip::query()
            ->with([
                'creator:id,name',
                'closer:id,name',
                'orders' => fn ($query) => $query->with('customer:id,name')->latest(),
            ])
            ->findOrFail(EncryptedId::decode($tripId));

        $this->authorize('view', $trip);

        $orders = $trip->orders;
        $statusSummary = [
            'total' => $orders->count(),
            'created' => $orders->where('status', 'created')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'assigned' => $orders->where('status', 'assigned')->count(),
            'transportation' => $orders->where('status', 'transportation')->count(),
            'incomplete' => $orders->where('status', 'incomplete')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
        ];

        return view('trips.show', [
            'trip' => $trip,
            'statusSummary' => $statusSummary,
        ]);
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
        $forceClose = (bool) $request->boolean('force_close');

        $openOrders = $trip->orders()
            ->select(['id', 'order_number', 'status'])
            ->where('status', '!=', 'completed')
            ->get();

        if ($forceClose && $openOrders->isNotEmpty() && ! $request->has('explanations')) {
            $statusBreakdown = $openOrders
                ->groupBy('status')
                ->map(fn (Collection $items) => $items->count())
                ->toArray();

            return back()->with('trip_close_warning', [
                'trip_number' => $trip->trip_number,
                'trip_show_url' => route('trips.show', $trip->encrypted_id),
                'close_url' => route('trips.close', $trip->encrypted_id),
                'total_incomplete' => $openOrders->count(),
                'status_breakdown' => $statusBreakdown,
                'incomplete_orders' => $openOrders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ])->values()->all(),
            ]);
        }

        if ($forceClose && $openOrders->isNotEmpty()) {
            $rules = [
                'explanations' => ['required', 'array'],
            ];
            foreach ($openOrders as $order) {
                $rules['explanations.'.$order->id] = ['required', 'string', 'min:10'];
            }

            $validated = $request->validate($rules, [
                'explanations.required' => 'Please provide explanations for all incomplete orders.',
                'explanations.*.required' => 'Each incomplete order needs an explanation before force closing.',
                'explanations.*.min' => 'Each explanation should be at least 10 characters.',
            ]);
            $orderExplanations = $validated['explanations'] ?? [];
        } else {
            $orderExplanations = [];
        }

        if (! $forceClose && $openOrders->isNotEmpty()) {
            $statusBreakdown = $openOrders
                ->groupBy('status')
                ->map(fn (Collection $items) => $items->count())
                ->toArray();

            return back()->with('trip_close_warning', [
                'trip_number' => $trip->trip_number,
                'trip_show_url' => route('trips.show', $trip->encrypted_id),
                'close_url' => route('trips.close', $trip->encrypted_id),
                'total_incomplete' => $openOrders->count(),
                'status_breakdown' => $statusBreakdown,
                'incomplete_orders' => $openOrders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ])->values()->all(),
            ]);
        }

        $this->tripService->close($trip, $request->user(), $forceClose, $orderExplanations);

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
