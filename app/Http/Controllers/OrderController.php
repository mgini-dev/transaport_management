<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Resources\OrderListResource;
use App\Models\Order;
use App\Models\Trip;
use App\Repositories\OrderRepository;
use App\Services\AuditLogService;
use App\Services\OrderService;
use App\Support\EncryptedId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderService $orderService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        if ($request->ajax()) {
            $skip = (int) $request->integer('skip', 0);
            $take = min((int) $request->integer('take', 15), 100);

            $orders = $this->orderRepository->listForIndex($request->user(), $skip, $take);

            return response()->json(['data' => OrderListResource::collection($orders)]);
        }

        return view('orders.index', [
            'trips' => Trip::query()->where('status', 'open')->orderByDesc('id')->get(),
            'customers' => Customer::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $data = $request->validate([
            'trip_id' => ['required', 'string'],
            'customer_id' => ['required', 'string'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'cargo_description' => ['nullable', 'string'],
            'weight_tons' => ['required', 'numeric', 'min:0'],
            'agreed_price' => ['required', 'numeric', 'min:0'],
            'origin_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            'expected_loading_date' => ['nullable', 'date'],
            'expected_leaving_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $tripId = EncryptedId::decode($data['trip_id']);
        $customerId = EncryptedId::decode($data['customer_id']);

        if (! $tripId || ! $customerId) {
            return back()->withErrors(['trip_id' => 'Invalid trip or customer identifier.'])->withInput();
        }

        try {
            $order = $this->orderService->create([
                ...$data,
                'trip_id' => $tripId,
                'customer_id' => $customerId,
            ], $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['trip_id' => $exception->getMessage()])->withInput();
        }

        $this->auditLogService->record(
            action: 'order.created',
            user: $request->user(),
            loggable: $order,
            context: ['order_number' => $order->order_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Order {$order->order_number} created.");
    }

    public function updateStatus(Request $request, string $orderId): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:created,processing,assigned,completed'],
            'remarks' => ['nullable', 'string'],
        ]);

        $order = Order::query()->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('updateStatus', $order);
        $order = $this->orderService->updateStatus($order, $data['status'], $request->user(), $data['remarks'] ?? null);

        $this->auditLogService->record(
            action: 'order.status.updated',
            user: $request->user(),
            loggable: $order,
            context: ['status' => $data['status']],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Order {$order->order_number} status updated.");
    }
}
