<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\Trip;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $data = $this->buildReportData($filters);

        return view('admin.reports.index', [
            'filters' => $filters,
            'data' => $data,
            'tripOptions' => Trip::query()->latest()->limit(300)->get(['id', 'trip_number', 'status']),
            'orderOptions' => Order::query()->latest()->limit(400)->get(['id', 'order_number', 'status']),
            'driverOptions' => Driver::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $data = $this->buildReportData($filters);
        $reportType = $filters['report_type'];
        $company = $this->companyProfile();

        $fileName = 'nmis-report-'.$reportType.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($data, $reportType, $company): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [$company['name']]);
            fputcsv($handle, [$company['address'] ?: '-']);
            fputcsv($handle, ['Phone: '.($company['phone'] ?: '-').' | Email: '.($company['email'] ?: '-').' | Website: '.($company['website'] ?: '-')]);
            fputcsv($handle, ['Logo: '.($company['logo_file'] ?: '-')]);
            fputcsv($handle, ['Report: '.($data['title'] ?? 'Dynamic Report')]);
            fputcsv($handle, ['Generated At: '.now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            if ($reportType === 'trips') {
                fputcsv($handle, ['Trip Number', 'Status', 'Created By', 'Closed By', 'Created At', 'Closed At', 'Orders', 'Completed', 'Incomplete', 'In Progress', 'Fuel Consumption (L)', 'Fuel Amount']);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, [
                        $row->trip_number,
                        $row->status,
                        $row->creator?->name ?? '-',
                        $row->closer?->name ?? '-',
                        (string) $row->created_at,
                        (string) $row->closed_at,
                        (int) $row->orders_count,
                        (int) $row->orders_completed_count,
                        (int) $row->orders_incomplete_count,
                        (int) $row->orders_active_count,
                        (float) ($row->fuel_consumption_litres ?? 0),
                        (float) ($row->fuel_consumption_amount ?? 0),
                    ]);
                }
            } elseif ($reportType === 'orders') {
                fputcsv($handle, ['Order Number', 'Trip', 'Customer', 'Status', 'Weight (Tons)', 'Agreed Price', 'Distance (KM)', 'Fuel Consumption (L)', 'Fuel Amount', 'Created By', 'Created At']);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, [
                        $row->order_number,
                        $row->trip?->trip_number ?? '-',
                        $row->customer?->name ?? '-',
                        $row->status,
                        (float) $row->weight_tons,
                        (float) $row->agreed_price,
                        $row->distance_km !== null ? (float) $row->distance_km : '',
                        (float) ($row->fuel_consumption_litres ?? 0),
                        (float) ($row->fuel_consumption_amount ?? 0),
                        $row->creator?->name ?? '-',
                        (string) $row->created_at,
                    ]);
                }
            } elseif ($reportType === 'drivers') {
                fputcsv($handle, ['Driver Summary']);
                fputcsv($handle, ['Driver', 'Mobile', 'Trips Performed', 'Orders Handled', 'Legs', 'Completed Legs', 'Active Legs', 'Fuel Consumption (L)', 'Fuel Amount']);
                foreach ($data['driver_summary'] as $summary) {
                    fputcsv($handle, [
                        $summary['driver_name'],
                        $summary['mobile_number'],
                        $summary['trips_performed'],
                        $summary['orders_handled'],
                        $summary['legs_count'],
                        $summary['completed_legs'],
                        $summary['active_legs'],
                        $summary['fuel_consumption_litres'],
                        $summary['fuel_consumption_amount'],
                    ]);
                }

                fputcsv($handle, []);
                fputcsv($handle, ['Driver Trip / Order Fuel Breakdown']);
                fputcsv($handle, ['Driver', 'Trip', 'Orders (Trip)', 'Legs', 'Completed Legs', 'Active Legs', 'Fuel Consumption (L)', 'Fuel Amount']);
                foreach ($data['driver_trip_summary'] as $summary) {
                    fputcsv($handle, [
                        $summary['driver_name'],
                        $summary['trip_number'],
                        $summary['orders'],
                        $summary['legs_count'],
                        $summary['completed_legs'],
                        $summary['active_legs'],
                        $summary['fuel_consumption_litres'],
                        $summary['fuel_consumption_amount'],
                    ]);
                }
            } else {
                fputcsv($handle, ['Requisition ID', 'Type', 'Order', 'Fleet', 'Requested By', 'Status', 'Requested Litres', 'Total Amount', 'Created At']);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->requisition_type,
                        $row->order?->order_number ?? '-',
                        $row->fleet?->fleet_code ?? '-',
                        $row->requester?->name ?? '-',
                        $row->status,
                        (float) $row->additional_litres,
                        (float) $row->total_amount,
                        (string) $row->created_at,
                    ]);
                }
            }

            fclose($handle);
        }, $fileName);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $data = $this->buildReportData($filters);
        $company = $this->companyProfile();

        $html = view('admin.reports.excel', [
            'filters' => $filters,
            'data' => $data,
            'generatedAt' => now(),
            'company' => $company,
        ])->render();

        $filename = 'nmis-report-'.$filters['report_type'].'-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(
            static function () use ($html): void {
                echo $html;
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $data = $this->buildReportData($filters);
        $company = $this->companyProfile();

        $html = view('admin.reports.pdf', [
            'filters' => $filters,
            'data' => $data,
            'generatedAt' => now(),
            'company' => $company,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'nmis-report-'.$filters['report_type'].'-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(
            static function () use ($dompdf): void {
                echo $dompdf->output();
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * @return array{report_type: string, from_date: string, to_date: string, trip_id: int|null, order_id: int|null, driver_id: int|null, order_status: string|null}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'report_type' => ['nullable', 'in:trips,orders,drivers,fuel'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'order_status' => ['nullable', 'in:created,processing,assigned,transportation,incomplete,completed'],
        ]);

        $from = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfDay()
            : now()->endOfDay();

        return [
            'report_type' => (string) ($validated['report_type'] ?? 'trips'),
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'trip_id' => isset($validated['trip_id']) ? (int) $validated['trip_id'] : null,
            'order_id' => isset($validated['order_id']) ? (int) $validated['order_id'] : null,
            'driver_id' => isset($validated['driver_id']) ? (int) $validated['driver_id'] : null,
            'order_status' => isset($validated['order_status']) ? (string) $validated['order_status'] : null,
        ];
    }

    /**
     * @param  array{report_type: string, from_date: string, to_date: string, trip_id: int|null, order_id: int|null, driver_id: int|null, order_status: string|null}  $filters
     * @return array<string, mixed>
     */
    private function buildReportData(array $filters): array
    {
        $from = Carbon::parse($filters['from_date'])->startOfDay();
        $to = Carbon::parse($filters['to_date'])->endOfDay();

        if ($filters['report_type'] === 'orders') {
            $query = Order::query()
                ->with(['trip:id,trip_number,status', 'customer:id,name', 'creator:id,name'])
                ->withSum('fuelRequisitions as fuel_consumption_litres', 'additional_litres')
                ->withSum('fuelRequisitions as fuel_consumption_amount', 'total_amount')
                ->whereBetween('created_at', [$from, $to])
                ->when($filters['trip_id'], fn ($q) => $q->where('trip_id', $filters['trip_id']))
                ->when($filters['order_id'], fn ($q) => $q->whereKey($filters['order_id']))
                ->when($filters['order_status'], fn ($q) => $q->where('status', $filters['order_status']))
                ->latest();

            $rows = $query->limit(1200)->get();

            return [
                'title' => 'Orders Report',
                'rows' => $rows,
                'summary' => [
                    'total_orders' => $rows->count(),
                    'total_weight' => (float) $rows->sum('weight_tons'),
                    'total_value' => (float) $rows->sum('agreed_price'),
                    'total_fuel_litres' => (float) $rows->sum('fuel_consumption_litres'),
                    'total_fuel_amount' => (float) $rows->sum('fuel_consumption_amount'),
                    'completed_orders' => (int) $rows->where('status', 'completed')->count(),
                    'incomplete_orders' => (int) $rows->where('status', 'incomplete')->count(),
                ],
            ];
        }

        if ($filters['report_type'] === 'drivers') {
            $legs = OrderLeg::query()
                ->with([
                    'driver:id,name,mobile_number,license_number,driver_address,contact1_name,contact1_phone',
                    'order:id,order_number,trip_id,created_at,status',
                    'order.trip:id,trip_number',
                    'fleet:id,fleet_code,plate_number',
                ])
                ->whereNotNull('driver_id')
                ->whereHas('order', function ($query) use ($from, $to, $filters): void {
                    $query->whereBetween('created_at', [$from, $to])
                        ->when($filters['trip_id'], fn ($q) => $q->where('trip_id', $filters['trip_id']))
                        ->when($filters['order_id'], fn ($q) => $q->whereKey($filters['order_id']));
                })
                ->when($filters['driver_id'], fn ($q) => $q->where('driver_id', $filters['driver_id']))
                ->latest('id')
                ->limit(5000)
                ->get();

            $orderIds = $legs->pluck('order_id')->filter()->unique()->map(fn ($id) => (int) $id)->values();
            $tripIds = $legs->pluck('order.trip_id')->filter()->unique()->map(fn ($id) => (int) $id)->values();

            $orderFuelTotals = $this->orderFuelTotals($orderIds);
            $tripFuelTotals = $this->tripFuelTotals($tripIds, $orderIds);

            $legs = $legs->map(function (OrderLeg $leg) use ($orderFuelTotals, $tripFuelTotals): OrderLeg {
                $orderId = (int) $leg->order_id;
                $tripId = (int) ($leg->order?->trip_id ?? 0);
                $orderFuel = $orderFuelTotals->get($orderId, ['litres' => 0.0, 'amount' => 0.0]);
                $tripFuel = $tripFuelTotals->get($tripId, ['litres' => 0.0, 'amount' => 0.0]);

                $leg->setAttribute('order_fuel_consumption_litres', (float) ($orderFuel['litres'] ?? 0.0));
                $leg->setAttribute('order_fuel_consumption_amount', (float) ($orderFuel['amount'] ?? 0.0));
                $leg->setAttribute('trip_fuel_consumption_litres', (float) ($tripFuel['litres'] ?? 0.0));
                $leg->setAttribute('trip_fuel_consumption_amount', (float) ($tripFuel['amount'] ?? 0.0));

                return $leg;
            });

            $driverSummary = $legs
                ->groupBy('driver_id')
                ->map(function (Collection $items) use ($orderFuelTotals): array {
                    $first = $items->first();
                    $driverOrderIds = $items->pluck('order_id')->filter()->unique()->map(fn ($id) => (int) $id);
                    return [
                        'driver_id' => (int) $first->driver_id,
                        'driver_name' => $first->driver?->name ?? 'Unknown',
                        'mobile_number' => $first->driver?->mobile_number ?? '-',
                        'trips_performed' => (int) $items->pluck('order.trip_id')->filter()->unique()->count(),
                        'orders_handled' => (int) $items->pluck('order_id')->filter()->unique()->count(),
                        'legs_count' => (int) $items->count(),
                        'completed_legs' => (int) $items->where('status', 'completed')->count(),
                        'active_legs' => (int) $items->where('status', 'active')->count(),
                        'fuel_consumption_litres' => (float) $driverOrderIds->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['litres' => 0.0])['litres'] ?? 0.0)),
                        'fuel_consumption_amount' => (float) $driverOrderIds->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['amount' => 0.0])['amount'] ?? 0.0)),
                    ];
                })
                ->values()
                ->sortByDesc('trips_performed')
                ->values();

            $driverTripSummary = $legs
                ->groupBy(fn (OrderLeg $leg) => $leg->driver_id.'|'.($leg->order?->trip_id ?? 0))
                ->map(function (Collection $items) use ($orderFuelTotals): array {
                    $first = $items->first();
                    $tripId = (int) ($first->order?->trip_id ?? 0);
                    $orderIdsInTrip = $items->pluck('order_id')->filter()->unique()->map(fn ($id) => (int) $id)->values();
                    $orderNumbers = $items->pluck('order.order_number')->filter()->unique()->values()->implode(', ');

                    return [
                        'driver_id' => (int) $first->driver_id,
                        'driver_name' => $first->driver?->name ?? 'Unknown',
                        'trip_id' => $tripId,
                        'trip_number' => $first->order?->trip?->trip_number ?? '-',
                        'orders_count' => $orderIdsInTrip->count(),
                        'orders' => $orderNumbers !== '' ? $orderNumbers : '-',
                        'legs_count' => (int) $items->count(),
                        'completed_legs' => (int) $items->where('status', 'completed')->count(),
                        'active_legs' => (int) $items->where('status', 'active')->count(),
                        'fuel_consumption_litres' => (float) $orderIdsInTrip->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['litres' => 0.0])['litres'] ?? 0.0)),
                        'fuel_consumption_amount' => (float) $orderIdsInTrip->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['amount' => 0.0])['amount'] ?? 0.0)),
                    ];
                })
                ->values()
                ->sortBy([
                    ['driver_name', 'asc'],
                    ['trip_number', 'asc'],
                ])
                ->values();

            $totalFuelLitres = (float) $orderIds->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['litres' => 0.0])['litres'] ?? 0.0));
            $totalFuelAmount = (float) $orderIds->sum(fn (int $orderId) => (float) ($orderFuelTotals->get($orderId, ['amount' => 0.0])['amount'] ?? 0.0));

            return [
                'title' => 'Driver Performance Report',
                'rows' => $legs,
                'driver_summary' => $driverSummary,
                'driver_trip_summary' => $driverTripSummary,
                'summary' => [
                    'drivers_involved' => $driverSummary->count(),
                    'total_legs' => (int) $legs->count(),
                    'completed_legs' => (int) $legs->where('status', 'completed')->count(),
                    'active_legs' => (int) $legs->where('status', 'active')->count(),
                    'fuel_consumption_litres' => $totalFuelLitres,
                    'fuel_consumption_amount' => $totalFuelAmount,
                ],
            ];
        }

        if ($filters['report_type'] === 'fuel') {
            $query = FuelRequisition::query()
                ->with(['order:id,order_number', 'fleet:id,fleet_code,plate_number', 'requester:id,name'])
                ->whereBetween('created_at', [$from, $to])
                ->when($filters['trip_id'], function ($q) use ($filters): void {
                    $q->whereHas('order', fn ($inner) => $inner->where('trip_id', $filters['trip_id']));
                })
                ->when($filters['order_id'], fn ($q) => $q->where('order_id', $filters['order_id']))
                ->latest();

            $rows = $query->limit(1200)->get();

            return [
                'title' => 'Fuel Requisition Report',
                'rows' => $rows,
                'summary' => [
                    'total_requisitions' => (int) $rows->count(),
                    'approved_count' => (int) $rows->where('status', 'accountant_approved')->count(),
                    'rejected_count' => (int) $rows->whereIn('status', ['supervisor_rejected', 'accountant_rejected'])->count(),
                    'total_litres_requested' => (float) $rows->sum('additional_litres'),
                    'total_amount' => (float) $rows->sum('total_amount'),
                ],
            ];
        }

        $query = Trip::query()
            ->with(['creator:id,name', 'closer:id,name'])
            ->withCount('orders')
            ->withCount([
                'orders as orders_completed_count' => fn ($q) => $q->where('status', 'completed'),
                'orders as orders_incomplete_count' => fn ($q) => $q->where('status', 'incomplete'),
                'orders as orders_active_count' => fn ($q) => $q->whereNotIn('status', ['completed', 'incomplete']),
            ])
            ->whereBetween('created_at', [$from, $to])
            ->when($filters['trip_id'], fn ($q) => $q->whereKey($filters['trip_id']))
            ->latest();

        $rows = $query->limit(1200)->get();
        $tripIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->values();
        $tripFuelTotals = $this->tripFuelTotals($tripIds);

        $rows = $rows->map(function (Trip $trip) use ($tripFuelTotals): Trip {
            $fuel = $tripFuelTotals->get((int) $trip->id, ['litres' => 0.0, 'amount' => 0.0]);
            $trip->setAttribute('fuel_consumption_litres', (float) ($fuel['litres'] ?? 0.0));
            $trip->setAttribute('fuel_consumption_amount', (float) ($fuel['amount'] ?? 0.0));

            return $trip;
        });

        return [
            'title' => 'Trips Report',
            'rows' => $rows,
            'summary' => [
                'total_trips' => (int) $rows->count(),
                'open_trips' => (int) $rows->where('status', 'open')->count(),
                'closed_trips' => (int) $rows->where('status', 'closed')->count(),
                'total_orders' => (int) $rows->sum('orders_count'),
                'fuel_consumption_litres' => (float) $rows->sum('fuel_consumption_litres'),
                'fuel_consumption_amount' => (float) $rows->sum('fuel_consumption_amount'),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $orderIds
     * @return Collection<int, array{litres: float, amount: float}>
     */
    private function orderFuelTotals(Collection $orderIds): Collection
    {
        if ($orderIds->isEmpty()) {
            return collect();
        }

        return FuelRequisition::query()
            ->selectRaw('order_id, SUM(additional_litres) as litres, SUM(total_amount) as amount')
            ->whereNotNull('order_id')
            ->whereIn('order_id', $orderIds->all())
            ->groupBy('order_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->order_id => [
                    'litres' => (float) $row->litres,
                    'amount' => (float) $row->amount,
                ],
            ]);
    }

    /**
     * @param  Collection<int, int>  $tripIds
     * @param  Collection<int, int>|null  $restrictOrderIds
     * @return Collection<int, array{litres: float, amount: float}>
     */
    private function tripFuelTotals(Collection $tripIds, ?Collection $restrictOrderIds = null): Collection
    {
        if ($tripIds->isEmpty()) {
            return collect();
        }

        return FuelRequisition::query()
            ->selectRaw('orders.trip_id as trip_id, SUM(fuel_requisitions.additional_litres) as litres, SUM(fuel_requisitions.total_amount) as amount')
            ->join('orders', 'orders.id', '=', 'fuel_requisitions.order_id')
            ->whereIn('orders.trip_id', $tripIds->all())
            ->when($restrictOrderIds && $restrictOrderIds->isNotEmpty(), fn ($q) => $q->whereIn('orders.id', $restrictOrderIds->all()))
            ->groupBy('orders.trip_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->trip_id => [
                    'litres' => (float) $row->litres,
                    'amount' => (float) $row->amount,
                ],
            ]);
    }

    /**
     * @return array{name: string, address: string, phone: string, email: string, website: string, logo_data_uri: string|null, logo_file: string}
     */
    private function companyProfile(): array
    {
        $logoPath = public_path('images/nexus-logo.png');
        $logoDataUri = null;

        if (is_file($logoPath)) {
            $mimeType = mime_content_type($logoPath) ?: 'image/png';
            $contents = @file_get_contents($logoPath);
            if ($contents !== false) {
                $logoDataUri = 'data:'.$mimeType.';base64,'.base64_encode($contents);
            }
        }

        return [
            'name' => (string) config('app.company_name', config('app.name', 'NMIS')),
            'address' => (string) config('app.company_address', ''),
            'phone' => (string) config('app.company_phone', ''),
            'email' => (string) config('app.company_email', ''),
            'website' => (string) config('app.company_website', ''),
            'logo_data_uri' => $logoDataUri,
            'logo_file' => is_file($logoPath) ? basename($logoPath) : '-',
        ];
    }
}
