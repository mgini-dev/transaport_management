<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Order {{ $order->order_number }}</h2>
                <p class="mt-2 text-sm text-slate-500">Comprehensive order details, trip context, status flow, and legs.</p>
            </div>
            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Status</p>
                <p class="mt-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $order->status === 'assigned' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $order->status === 'processing' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $order->status === 'created' ? 'bg-slate-100 text-slate-700' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Trip</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $order->trip?->trip_number ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Weight</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $order->weight_tons, 2) }} t</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Agreed Price</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $order->agreed_price, 2) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Order Information</h3>
                <dl class="mt-4 grid gap-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Customer</dt>
                        <dd class="font-medium text-slate-900">{{ $order->customer?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Cargo Type</dt>
                        <dd class="font-medium text-slate-900">{{ $order->cargo_type }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Distance</dt>
                        <dd class="font-medium text-slate-900">{{ $order->distance_km ? number_format((float) $order->distance_km, 2).' km' : 'Not calculated' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Created By</dt>
                        <dd class="font-medium text-slate-900">{{ $order->creator?->name ?? '-' }}</dd>
                    </div>
                </dl>
                <div class="mt-4 border-t border-slate-200/70 pt-4 text-sm">
                    <p class="font-semibold text-slate-700">Route</p>
                    <p class="mt-1 text-slate-600">{{ $order->origin_address }}</p>
                    <p class="my-1 text-xs font-medium uppercase tracking-wide text-slate-400">to</p>
                    <p class="text-slate-600">{{ $order->destination_address }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Status History</h3>
                <div class="mt-4 space-y-3">
                    @forelse($order->statusHistory as $history)
                        <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-3">
                            <p class="text-sm font-medium text-slate-900">
                                {{ ucfirst($history->from_status ?? 'N/A') }} to {{ ucfirst($history->to_status) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $history->changedBy?->name ?? 'System' }} on {{ $history->created_at?->format('d M Y, h:i A') }}
                            </p>
                            @if($history->remarks)
                                <p class="mt-2 text-sm text-slate-700">{{ $history->remarks }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No status updates recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="border-b border-slate-200/70 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Assigned Legs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Seq</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($order->legs as $leg)
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $leg->leg_sequence }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->origin_address }} to {{ $leg->destination_address }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->fleet?->fleet_code ?? $leg->fleet?->plate_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->driver?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ ucfirst($leg->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No legs assigned for this order yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
