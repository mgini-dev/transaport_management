<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Dynamic Reports</h2>
                <p class="mt-1 text-sm text-slate-500">Comprehensive reports for trips, orders, drivers, and fuel consumption with branded exports.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.export.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                    PDF Export
                </a>
                <a href="{{ route('admin.reports.export.excel', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all">
                    Excel Export
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
            <form method="GET" class="grid gap-4 lg:grid-cols-6">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Report Type</label>
                    <select name="report_type" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="trips" {{ $filters['report_type'] === 'trips' ? 'selected' : '' }}>Trips Report</option>
                        <option value="orders" {{ $filters['report_type'] === 'orders' ? 'selected' : '' }}>Orders Report</option>
                        <option value="drivers" {{ $filters['report_type'] === 'drivers' ? 'selected' : '' }}>Driver Performance</option>
                        <option value="fuel" {{ $filters['report_type'] === 'fuel' ? 'selected' : '' }}>Fuel Requisitions</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">From Date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">To Date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Trip</label>
                    <select name="trip_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="">All Trips</option>
                        @foreach($tripOptions as $trip)
                            <option value="{{ $trip->id }}" {{ (int) $filters['trip_id'] === (int) $trip->id ? 'selected' : '' }}>
                                {{ $trip->trip_number }} ({{ strtoupper($trip->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Order</label>
                    <select name="order_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="">All Orders</option>
                        @foreach($orderOptions as $order)
                            <option value="{{ $order->id }}" {{ (int) $filters['order_id'] === (int) $order->id ? 'selected' : '' }}>
                                {{ $order->order_number }} ({{ strtoupper($order->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Driver</label>
                    <select name="driver_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="">All Drivers</option>
                        @foreach($driverOptions as $driver)
                            <option value="{{ $driver->id }}" {{ (int) $filters['driver_id'] === (int) $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Order Status</label>
                    <select name="order_status" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="">All Statuses</option>
                        @foreach(['created','processing','assigned','transportation','incomplete','completed'] as $status)
                            <option value="{{ $status }}" {{ $filters['order_status'] === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-5 flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all">
                        Generate Report
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($data['summary'] as $label => $value)
                <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</p>
                </div>
            @endforeach
        </div>

        @if($filters['report_type'] === 'trips')
            <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">Trips Tracking Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Trip</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Completed</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Incomplete</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">In Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel (L)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($data['rows'] as $trip)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $trip->trip_number }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst($trip->status) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $trip->orders_count }}</td>
                                    <td class="px-6 py-4 text-sm text-emerald-700 font-semibold">{{ $trip->orders_completed_count }}</td>
                                    <td class="px-6 py-4 text-sm text-rose-700 font-semibold">{{ $trip->orders_incomplete_count }}</td>
                                    <td class="px-6 py-4 text-sm text-indigo-700 font-semibold">{{ $trip->orders_active_count }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format((float) ($trip->fuel_consumption_litres ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format((float) ($trip->fuel_consumption_amount ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $trip->created_at?->format('d M Y, h:i A') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('trips.show', $trip->encrypted_id) }}" class="text-sm font-semibold text-[var(--nmis-primary)] hover:underline">View Full Trip</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-6 py-10 text-center text-sm text-slate-500">No trips found for selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($filters['report_type'] === 'orders')
            <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">Orders Full Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Trip</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Weight</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Distance</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel (L)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($data['rows'] as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $order->trip?->trip_number ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $order->customer?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst($order->status) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) $order->weight_tons, 2) }} t</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) $order->agreed_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $order->distance_km ? number_format((float) $order->distance_km, 2).' km' : '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) ($order->fuel_consumption_litres ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) ($order->fuel_consumption_amount ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('orders.show', $order->encrypted_id) }}" class="text-sm font-semibold text-[var(--nmis-primary)] hover:underline">View Full Order</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-6 py-10 text-center text-sm text-slate-500">No orders found for selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($filters['report_type'] === 'drivers')
            <div class="grid gap-6 xl:grid-cols-2">
                <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-900">Driver Summary (Custom Time)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Trips</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Orders</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Legs</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel (L)</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($data['driver_summary'] as $row)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $row['driver_name'] }}</td>
                                        <td class="px-6 py-4 text-sm">{{ $row['trips_performed'] }}</td>
                                        <td class="px-6 py-4 text-sm">{{ $row['orders_handled'] }}</td>
                                        <td class="px-6 py-4 text-sm">{{ $row['legs_count'] }} ({{ $row['completed_legs'] }} completed)</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format((float) $row['fuel_consumption_litres'], 2) }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format((float) $row['fuel_consumption_amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No driver data found for selected filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-900">Driver Trip and Order Fuel Details</h3>
                    </div>
                    <div class="max-h-[560px] overflow-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Driver</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Trip</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Orders</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Legs</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Completed</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Active</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel (L)</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fuel Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($data['driver_trip_summary'] as $tripRow)
                                    <tr>
                                        <td class="px-4 py-3 text-xs font-semibold text-slate-900">{{ $tripRow['driver_name'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $tripRow['trip_number'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $tripRow['orders'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $tripRow['legs_count'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $tripRow['completed_legs'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $tripRow['active_legs'] }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold text-slate-900">{{ number_format((float) $tripRow['fuel_consumption_litres'], 2) }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold text-slate-900">{{ number_format((float) $tripRow['fuel_consumption_amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No driver trip activity found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">Fuel Requisition Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fleet</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Litres</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($data['rows'] as $rq)
                                <tr>
                                    <td class="px-6 py-4 text-sm">#{{ $rq->id }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $rq->requisition_type === 'fleet_only' ? 'Fleet Only' : 'Order Based' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $rq->order?->order_number ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $rq->fleet?->fleet_code ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ str_replace('_', ' ', ucfirst($rq->status)) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) $rq->additional_litres, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ number_format((float) $rq->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('fuel.show', $rq->encrypted_id) }}" class="text-sm font-semibold text-[var(--nmis-primary)] hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">No fuel requisitions found for selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-app-layout>
