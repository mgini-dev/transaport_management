<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-bold gradient-text">Maintenance Center</h2>
            <p class="mt-2 text-sm text-slate-500">Global tracking of all repairs, services, and inspections</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @include('fleet.partials.navigation')

        <!-- Maintenance Summary Cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Maintenance Cost</p>
                    <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">TSh {{ number_format($totalCost ?? 0, 0) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Service Records</p>
                    <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($recordsCount ?? 0, 0) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Vehicles Needing Service</p>
                    <div class="rounded-lg {{ ($needsServiceCount ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold {{ ($needsServiceCount ?? 0) > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $needsServiceCount ?? 0 }}</p>
            </div>
        </div>

        <!-- Maintenance Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm transition-all hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <h3 class="text-sm font-bold text-slate-900">Recent Maintenance History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Service Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Vehicle Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Service Category</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Odometer At Service</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Service Cost</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-600">
                                    {{ $log->performed_at->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('fleet.show', $log->fleet->encrypted_id) }}" class="inline-flex items-center gap-2 text-sm font-bold text-[var(--nmis-primary)] hover:text-[var(--nmis-secondary)] transition-colors">
                                        <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-[var(--nmis-primary)]/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h1"></path>
                                            </svg>
                                        </div>
                                        {{ $log->fleet->fleet_code }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                        {{ str_replace('_', ' ', $log->service_type) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600 tabular-nums">
                                    {{ number_format($log->odometer_reading, 0) }} km
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900 text-right tabular-nums">
                                    TSh {{ number_format($log->cost, 0) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                            {{ substr($log->recordedBy->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-sm text-slate-500">{{ $log->recordedBy->name ?? 'System' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="h-12 w-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <p class="text-sm italic">No maintenance records found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $logs->links() }}
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
