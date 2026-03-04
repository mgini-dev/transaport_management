<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FuelRequisition;
use App\Http\Resources\OrderListResource;
use App\Models\Order;
use App\Models\Trip;
use App\Repositories\OrderRepository;
use App\Services\AuditLogService;
use App\Services\OrderService;
use App\Support\TanzaniaRegions;
use App\Support\EncryptedId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Dompdf\Dompdf;
use Dompdf\Options;
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

            $orders = $this->orderRepository->listForIndex(
                user: $request->user(),
                skip: $skip,
                take: $take,
                status: $request->string('status')->toString() ?: null,
                search: $request->string('search')->toString() ?: null
            );

            $total = $this->orderRepository->countForIndex(
                user: $request->user(),
                status: $request->string('status')->toString() ?: null,
                search: $request->string('search')->toString() ?: null
            );

            $stats = $this->orderRepository->statsForUser($request->user());

            return response()->json([
                'data' => OrderListResource::collection($orders),
                'meta' => [
                    'total' => $total,
                    'skip' => $skip,
                    'take' => $take,
                ],
                'stats' => $stats,
            ]);
        }

        return view('orders.index', [
            'trips' => Trip::query()->where('status', 'open')->orderByDesc('id')->get(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'tanzaniaRegions' => TanzaniaRegions::names(),
            'prefillTripEncryptedId' => $request->string('trip')->toString() ?: null,
            'autoOpenCreateModal' => $request->boolean('open'),
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
            'origin_address' => ['required', 'string', 'in:'.implode(',', TanzaniaRegions::names())],
            'destination_address' => ['required', 'string', 'different:origin_address', 'in:'.implode(',', TanzaniaRegions::names())],
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
            'status' => ['required', 'in:created,processing,assigned,transportation,completed'],
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

    public function show(Request $request, string $orderId): View
    {
        $order = Order::query()
            ->with([
                'trip:id,trip_number,status,created_at,closed_at',
                'customer:id,name,contact_person,phone,email,address',
                'creator:id,name',
                'completer:id,name',
                'deliveryNoteIssuer:id,name',
                'statusHistory' => fn ($query) => $query->with('changedBy:id,name')->latest(),
                'legs' => fn ($query) => $query->with(['fleet:id,fleet_code,plate_number', 'driver:id,name'])->orderBy('leg_sequence'),
                'fuelBalances' => fn ($query) => $query->with(['fleet:id,fleet_code,plate_number', 'updatedBy:id,name'])->latest(),
                'fuelRequisitions' => fn ($query) => $query->with([
                    'fleet:id,fleet_code,plate_number',
                    'requester:id,name',
                    'supervisor:id,name',
                    'accountant:id,name',
                ])->latest(),
            ])
            ->findOrFail(EncryptedId::decode($orderId));

        $this->authorize('view', $order);

        $completionDocumentPreview = $this->resolveCompletionDocumentPreview($order);

        return view('orders.show', [
            'order' => $order,
            'canViewDistance' => $request->user()->can('viewDistance', $order),
            'canCompleteTransportation' => $request->user()->can('completeTransportation', $order),
            'canDownloadDeliveryNote' => $order->status !== 'completed'
                && $order->trip?->status !== 'closed'
                && ($request->user()->id === $order->created_by || $request->user()->can('orders.view_all')) && FuelRequisition::query()
                ->where('order_id', $order->id)
                ->where('status', 'accountant_approved')
                ->exists(),
            'orderFleetOptions' => $order->legs()->with('fleet:id,fleet_code,plate_number')->get()->pluck('fleet')->filter()->unique('id')->values(),
            'completionDocumentPreview' => $completionDocumentPreview,
        ]);
    }

    public function calculateDistance(Request $request, string $orderId): RedirectResponse
    {
        $order = Order::query()->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('viewDistance', $order);

        try {
            $order = $this->orderService->calculateAndStoreDistance($order);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['distance' => $exception->getMessage()]);
        }

        $this->auditLogService->record(
            action: 'order.distance.calculated',
            user: $request->user(),
            loggable: $order,
            context: ['distance_km' => $order->distance_km],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Distance calculated for order {$order->order_number}.");
    }

    public function completeTransportation(Request $request, string $orderId): RedirectResponse
    {
        $order = Order::query()->with('legs')->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('completeTransportation', $order);

        $data = $request->validate([
            'fleet_balances' => ['required', 'array', 'min:1'],
            'fleet_balances.*.fleet_id' => ['required', 'string'],
            'fleet_balances.*.remaining_litres' => ['required', 'numeric', 'min:0'],
            'completion_comment' => ['nullable', 'string'],
            'signed_delivery_note' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $fleetBalances = collect($data['fleet_balances'])
            ->map(function (array $row) {
                $fleetId = EncryptedId::decode((string) $row['fleet_id']);
                return [
                    'fleet_id' => $fleetId ?: null,
                    'remaining_litres' => (float) $row['remaining_litres'],
                ];
            })
            ->all();

        if (collect($fleetBalances)->contains(fn (array $row) => ! $row['fleet_id'])) {
            return back()->withErrors(['fleet_balances' => 'One or more fleet identifiers are invalid.'])->withInput();
        }

        $path = $request->file('signed_delivery_note')->store('signed-delivery-notes', 'private');

        try {
            $order = $this->orderService->completeTransportation($order, [
                'fleet_balances' => $fleetBalances,
                'completion_comment' => $data['completion_comment'] ?? null,
                'completion_document_path' => $path,
            ], $request->user());
        } catch (RuntimeException $exception) {
            Storage::disk('private')->delete($path);
            return back()->withErrors(['signed_delivery_note' => $exception->getMessage()])->withInput();
        }

        $this->auditLogService->record(
            action: 'order.transport.completed',
            user: $request->user(),
            loggable: $order,
            context: ['order_id' => $order->id, 'fleet_balances' => $fleetBalances],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Order {$order->order_number} marked completed.");
    }

    public function deliveryNotePdf(Request $request, string $orderId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $order = Order::query()
            ->with([
                'trip',
                'customer',
                'creator',
                'legs' => fn ($query) => $query->with(['fleet', 'driver'])->orderBy('leg_sequence'),
            ])
            ->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('view', $order);
        abort_unless($request->user()->id === $order->created_by || $request->user()->can('orders.view_all'), 403, 'Only order creator can download delivery note.');
        abort_if($order->status === 'completed' || $order->trip?->status === 'closed', 403, 'Delivery note download is disabled for completed orders or closed trips.');

        $hasApprovedRequisition = FuelRequisition::query()
            ->where('order_id', $order->id)
            ->where('status', 'accountant_approved')
            ->exists();

        abort_unless($hasApprovedRequisition, 403, 'Delivery note is available only after fuel requisition approval.');

        if ($order->delivery_note_issued_at === null) {
            $order->update([
                'delivery_note_issued_at' => now(),
                'delivery_note_issued_by' => $request->user()->id,
            ]);
            $order->refresh();
        }

        $this->auditLogService->record(
            action: 'order.delivery_note.downloaded',
            user: $request->user(),
            loggable: $order,
            context: ['order_number' => $order->order_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $html = view('orders.delivery_note_pdf', [
            'order' => $order,
            'generatedAt' => now(),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'delivery-note-'.$order->order_number.'.pdf';
        return response()->streamDownload(
            static function () use ($dompdf): void {
                echo $dompdf->output();
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function previewCompletionDocument(Request $request, string $orderId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $order = Order::query()->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('view', $order);
        abort_unless($order->completion_document_path, 404, 'No completion document found.');

        $disk = Storage::disk('private');
        abort_unless($disk->exists($order->completion_document_path), 404, 'Completion document file not found.');

        $mimeType = $disk->mimeType($order->completion_document_path) ?: 'application/octet-stream';
        abort_unless(
            $mimeType === 'application/pdf' || str_starts_with($mimeType, 'image/'),
            415,
            'Preview is available for PDF and image files only.'
        );

        return $disk->response(
            $order->completion_document_path,
            $this->completionDocumentFilename($order),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ],
            'inline'
        );
    }

    public function downloadCompletionDocument(Request $request, string $orderId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $order = Order::query()->findOrFail(EncryptedId::decode($orderId));
        $this->authorize('view', $order);
        abort_unless($order->completion_document_path, 404, 'No completion document found.');

        $disk = Storage::disk('private');
        abort_unless($disk->exists($order->completion_document_path), 404, 'Completion document file not found.');

        return $disk->download(
            $order->completion_document_path,
            $this->completionDocumentFilename($order)
        );
    }

    /**
     * @return array{
     *   has_document: bool,
     *   file_missing: bool,
     *   mime_type: string|null,
     *   extension: string|null,
     *   size_bytes: int|null,
     *   can_preview: bool,
     *   preview_kind: string|null,
     *   preview_url: string|null,
     *   download_url: string|null,
     *   filename: string|null
     * }
     */
    private function resolveCompletionDocumentPreview(Order $order): array
    {
        if (! $order->completion_document_path) {
            return [
                'has_document' => false,
                'file_missing' => false,
                'mime_type' => null,
                'extension' => null,
                'size_bytes' => null,
                'can_preview' => false,
                'preview_kind' => null,
                'preview_url' => null,
                'download_url' => null,
                'filename' => null,
            ];
        }

        $disk = Storage::disk('private');
        $path = $order->completion_document_path;
        $fileExists = $disk->exists($path);
        $mimeType = $fileExists ? ($disk->mimeType($path) ?: 'application/octet-stream') : null;
        $isPreviewable = $mimeType !== null && ($mimeType === 'application/pdf' || str_starts_with($mimeType, 'image/'));
        $size = $fileExists ? $disk->size($path) : null;

        return [
            'has_document' => true,
            'file_missing' => ! $fileExists,
            'mime_type' => $mimeType,
            'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: null,
            'size_bytes' => is_int($size) ? $size : null,
            'can_preview' => $isPreviewable,
            'preview_kind' => $isPreviewable ? ($mimeType === 'application/pdf' ? 'pdf' : 'image') : null,
            'preview_url' => $isPreviewable ? route('orders.completion_document.preview', $order->encrypted_id) : null,
            'download_url' => $fileExists ? route('orders.completion_document.download', $order->encrypted_id) : null,
            'filename' => $fileExists ? $this->completionDocumentFilename($order) : null,
        ];
    }

    private function completionDocumentFilename(Order $order): string
    {
        $extension = strtolower(pathinfo((string) $order->completion_document_path, PATHINFO_EXTENSION));

        return 'completion-proof-'.$order->order_number.($extension ? '.'.$extension : '');
    }
}
