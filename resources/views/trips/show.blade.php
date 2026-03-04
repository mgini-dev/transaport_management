<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Trip {{ $trip->trip_number }}</h2>
                <p class="mt-2 text-sm text-slate-500">Comprehensive trip view with all linked orders and progress.</p>
            </div>
            <div class="flex items-center gap-2">
                @can('orders.create')
                    @if($trip->status === 'open')
                        <a href="{{ route('orders.index', ['trip' => $trip->encrypted_id, 'open' => 1]) }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-[1.02] transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Order
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m-12.728 0a9 9 0 010-12.728m12.728 0L5.636 18.364"></path>
                            </svg>
                            Trip Closed
                        </span>
                    @endif
                @endcan
                <a href="{{ route('trips.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Trips
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Status</p>
                <p class="mt-2 text-2xl font-bold {{ $trip->status === 'open' ? 'text-[var(--nmis-secondary)]' : 'text-[var(--nmis-accent)]' }}">
                    {{ ucfirst($trip->status) }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Orders</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $statusSummary['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Created By</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $trip->creator?->name ?? 'System' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Created On</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $trip->created_at?->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Created</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $statusSummary['created'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Processing</p>
                <p class="mt-1 text-xl font-bold text-amber-600">{{ $statusSummary['processing'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Assigned</p>
                <p class="mt-1 text-xl font-bold text-blue-600">{{ $statusSummary['assigned'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Transportation</p>
                <p class="mt-1 text-xl font-bold text-indigo-600">{{ $statusSummary['transportation'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Incomplete</p>
                <p class="mt-1 text-xl font-bold text-rose-600">{{ $statusSummary['incomplete'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Completed</p>
                <p class="mt-1 text-xl font-bold text-emerald-600">{{ $statusSummary['completed'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="border-b border-slate-200/70 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Orders In This Trip</h3>
                <p class="mt-1 text-sm text-slate-500">All orders linked to {{ $trip->trip_number }}.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Order No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cargo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($trip->orders as $order)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">{{ $order->customer?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $order->cargo_type }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <span class="font-medium">{{ $order->origin_address }}</span>
                                    <span class="text-slate-400">to</span>
                                    <span class="font-medium">{{ $order->destination_address }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $order->status === 'transportation' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                        {{ $order->status === 'incomplete' ? 'bg-rose-100 text-rose-700' : '' }}
                                        {{ $order->status === 'assigned' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $order->status === 'processing' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $order->status === 'created' ? 'bg-slate-100 text-slate-700' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $order->created_at?->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    @can('view', $order)
                                        <a href="{{ route('orders.show', $order->encrypted_id) }}"
                                           class="inline-flex items-center rounded-lg bg-slate-100 p-2 text-[var(--nmis-primary)] hover:bg-[var(--nmis-primary)] hover:text-white transition-all"
                                           title="View Order Details">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No orders have been created under this trip yet.
                                </td>
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
