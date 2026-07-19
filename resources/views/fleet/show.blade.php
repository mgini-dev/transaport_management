<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('fleet.index') }}" class="rounded-lg bg-white p-2 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-bold gradient-text">{{ $fleet->fleet_code }}</h2>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                            {{ $fleet->vehicle_type ?? 'Vehicle' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Deep Profile & Performance Analytics</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @can('fleet.create')
                    <a href="{{ route('fleet.edit', $fleet->encrypted_id) }}" 
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Details
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ 
        activeTab: 'overview',
        showLogModal: false,
        showDeferModal: false
    }">
        @include('fleet.partials.navigation')
        <!-- Quick Stats Grid -->
        <div class="grid gap-4 sm:grid-cols-4">
            <!-- Current Mileage -->
            <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Odometer</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900">{{ number_format($fleet->current_odometer, 0) }}</span>
                    <span class="text-sm font-medium text-slate-500">km</span>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>Total distance tracked</span>
                </div>
            </div>

            <!-- Fuel Efficiency -->
            <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Fuel Efficiency</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900">{{ $efficiency > 0 ? $efficiency : '--' }}</span>
                    <span class="text-sm font-medium text-slate-500">km/L</span>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    @if($fleet->fuel_consumption_benchmark)
                        <span>Target: {{ $fleet->fuel_consumption_benchmark }} L/100km</span>
                    @else
                        <span>Benchmark not set</span>
                    @endif
                </div>
            </div>

            <!-- Service Status -->
            <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Service Status</p>
                @php
                    $status = $fleet->serviceStatus();
                    $colors = [
                        'good' => 'text-emerald-600 bg-emerald-50',
                        'approaching' => 'text-amber-600 bg-amber-50',
                        'overdue' => 'text-rose-600 bg-rose-50'
                    ];
                @endphp
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-xl px-3 py-1 text-sm font-bold uppercase {{ $colors[$status] }}">
                        {{ $status }}
                    </span>
                </div>
                <div class="mt-4 text-xs text-slate-500">
                    Due at: <span class="font-bold text-slate-900">{{ number_format($fleet->next_service_due_km, 0) }} km</span>
                </div>
            </div>

            <!-- Active Driver -->
            <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Assignment</p>
                @php $currentDriver = $fleet->drivers()->where('is_active', true)->first(); @endphp
                @if($currentDriver)
                    <div class="mt-2 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold">
                            {{ substr($currentDriver->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $currentDriver->name }}</p>
                            <p class="text-[10px] text-slate-500">{{ $currentDriver->mobile_number }}</p>
                        </div>
                    </div>
                @else
                    <div class="mt-2">
                        <span class="text-sm font-medium text-slate-400 italic">No active driver</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Component Health Dashboard (NEW Deep Tracking) -->
        <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Component Health Status</h3>
                    <p class="text-xs text-slate-500 uppercase font-bold tracking-widest mt-1">Lifecycle Tracking & Expiry Prediction</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c-2.288 0-4.47.61-6.354 1.674M12 2.944c2.288 0 4.47.61 6.354 1.674M12 22c-2.288 0-4.47-.61-6.354-1.674M12 22c2.288 0 4.47-.61 6.354-1.674"></path></svg>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($fleet->latestComponentItems() as $item)
                    @php
                        $remaining = $item->next_due_km - $fleet->current_odometer;
                        $percent = $item->lifespan_km > 0 ? max(0, min(100, ($remaining / $item->lifespan_km) * 100)) : 100;
                        $color = $percent < 20 ? 'rose' : ($percent < 50 ? 'amber' : 'emerald');
                    @endphp
                    <div class="group relative rounded-xl border border-slate-100 p-4 transition-all hover:shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase text-slate-400">{{ $item->category }}</span>
                            <span class="text-[10px] font-black text-{{ $color }}-600 bg-{{ $color }}-50 px-2 py-0.5 rounded-full">
                                {{ number_format($percent, 0) }}% Life
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 truncate">{{ $item->description }}</h4>
                        <div class="mt-4 space-y-2">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full bg-{{ $color }}-500 transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold">
                                <span class="text-slate-400">Next Change:</span>
                                <span class="{{ $percent < 20 ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ number_format($item->next_due_km, 0) }} km
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-slate-200 p-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 mb-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <p class="text-sm text-slate-500 italic">No component tracking data yet. Start by logging an itemized service completion.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Content Area with Tabs -->
        <div class="grid gap-6 lg:grid-cols-3">
            
            <!-- Left Column: Tabs and Timeline -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tabs Header -->
                <div class="flex items-center gap-1 rounded-2xl bg-slate-100 p-1">
                    <button @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold transition-all">
                        Overview
                    </button>
                    <button @click="activeTab = 'drivers'" 
                            :class="activeTab === 'drivers' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold transition-all">
                        Driver History
                    </button>
                    <button @click="activeTab = 'maintenance'" 
                            :class="activeTab === 'maintenance' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold transition-all">
                        Maintenance
                    </button>
                    <button @click="activeTab = 'fuel'" 
                            :class="activeTab === 'fuel' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold transition-all">
                        Fuel Log
                    </button>
                </div>

                <!-- Tab Content: Overview -->
                <div x-show="activeTab === 'overview'" class="space-y-6">
                    <!-- Technical Specifications -->
                    <div class="rounded-2xl border border-slate-200/60 bg-white p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Technical Specifications</h3>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-[var(--nmis-primary)]">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Plate Number</p>
                                    <p class="text-base font-bold text-slate-900">{{ $fleet->plate_number }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-[var(--nmis-primary)]">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Trailer Number</p>
                                    <p class="text-base font-bold text-slate-900">{{ $fleet->trailer_number ?? 'Not Assigned' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-[var(--nmis-primary)]">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Load Capacity</p>
                                    <p class="text-base font-bold text-slate-900">{{ number_format($fleet->capacity_tons, 1) }} Tons</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-[var(--nmis-primary)]">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Service Interval</p>
                                    <p class="text-base font-bold text-slate-900">Every {{ number_format($fleet->oil_change_interval_km, 0) }} km</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Timeline -->
                    <div class="rounded-2xl border border-slate-200/60 bg-white p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">Recent Activity</h3>
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @forelse($history as $item)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-100" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex items-start space-x-3">
                                                <div class="relative">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 py-1.5">
                                                    <div class="text-sm text-slate-500">
                                                        <span class="font-bold text-slate-900">{{ $item->driver->name }}</span>
                                                        assigned to vehicle at 
                                                        <span class="font-medium text-slate-900">{{ number_format($item->start_odometer, 0) }} km</span>
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-400">
                                                        {{ $item->assigned_at->format('M d, Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <p class="text-sm text-slate-500 italic">No recent activity recorded.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Drivers -->
                <div x-show="activeTab === 'drivers'" class="rounded-2xl border border-slate-200/60 bg-white overflow-hidden shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Driver</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned At</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Start Odo</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">End Odo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @foreach($fleet->driverHistory as $entry)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">{{ $entry->driver->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $entry->assigned_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ number_format($entry->start_odometer, 0) }} km</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $entry->end_odometer ? number_format($entry->end_odometer, 0) . ' km' : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tab Content: Maintenance -->
                <div x-show="activeTab === 'maintenance'" class="space-y-6">
                    <div class="rounded-2xl border border-slate-200/60 bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Odometer</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach($fleet->maintenanceLogs as $log)
                                    <tr class="group border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-all">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-700">{{ $log->performed_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center rounded-lg px-2 py-1 text-[10px] font-black bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)] uppercase tracking-wider">
                                                        {{ str_replace('_', ' ', $log->service_type) }}
                                                    </span>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                                        Recorded by: {{ $log->recordedBy->name ?? 'System' }}
                                                    </span>
                                                    @if($log->remarks)
                                                        <span class="text-[10px] italic text-slate-400 font-medium truncate max-w-[200px]" title="{{ $log->remarks }}">
                                                            "{{ $log->remarks }}"
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if($log->items->count() > 0)
                                                    <div class="grid grid-cols-1 gap-1.5 pl-2 border-l-2 border-slate-100">
                                                        @foreach($log->items as $item)
                                                            <div class="flex items-center justify-between gap-4">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-[9px] font-black text-slate-400 uppercase w-12">{{ $item->category }}</span>
                                                                    <span class="text-[11px] font-bold text-slate-600">{{ $item->description }}</span>
                                                                </div>
                                                                <span class="text-[10px] font-black text-slate-400">TSh {{ number_format($item->cost, 0) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-black tracking-tight">{{ number_format($log->odometer_reading, 0) }} <span class="text-[10px] text-slate-400 uppercase">km</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-black text-slate-900">TSh {{ number_format($log->cost + $log->items->sum('cost'), 0) }}</span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase">Total Bill</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Fuel Log -->
                <div x-show="activeTab === 'fuel'" class="space-y-6">
                    <div class="rounded-2xl border border-slate-200/60 bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Odometer</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Litres</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($fleet->fuelRequisitions()->latest()->get() as $req)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $req->created_at->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 uppercase">{{ $req->requisition_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ number_format($req->odometer_reading, 0) }} km</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">{{ number_format($req->litres, 1) }} L</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $req->status === 'accountant_approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ str_replace('_', ' ', $req->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">No fuel records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Stats & Actions -->
            <div class="space-y-6">
                <!-- Maintenance Prediction Card -->
                <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white shadow-xl">
                    <h3 class="text-lg font-bold">Predictive Alert</h3>
                    <p class="mt-2 text-sm text-slate-400 text-pretty">Based on your current mileage, we predict your next oil change will be needed in:</p>
                    
                    <div class="mt-6 flex items-center justify-center">
                        <div class="relative h-40 w-40">
                            <!-- Circular Progress -->
                            <svg class="h-full w-full" viewBox="0 0 36 36">
                                <path class="text-slate-700" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                @php 
                                    $percent = min(100, max(0, ($fleet->current_odometer - $fleet->last_service_odometer) / max(1, $fleet->oil_change_interval_km) * 100));
                                @endphp
                                <path class="{{ $percent > 90 ? 'text-rose-500' : 'text-sky-500' }}" 
                                      stroke-width="3" 
                                      stroke-dasharray="{{ $percent }}, 100" 
                                      stroke-linecap="round" 
                                      stroke="currentColor" 
                                      fill="none" 
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black">{{ number_format($fleet->kmsUntilService(), 0) }}</span>
                                <span class="text-[10px] uppercase font-bold text-slate-400">KM Left</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Last Service</span>
                            <span>{{ number_format($fleet->last_service_odometer, 0) }} km</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-sky-400">
                            <span>Next Target</span>
                            <span>{{ number_format($fleet->next_service_due_km, 0) }} km</span>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <button @click="showLogModal = true"
                                class="w-full rounded-xl bg-sky-500 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/20 hover:bg-sky-400 transition-all">
                            Log Completion
                        </button>
                        <button @click="showDeferModal = true"
                                class="w-full rounded-xl border border-slate-600 bg-transparent py-3 text-sm font-bold text-slate-300 hover:bg-slate-700 transition-all">
                            Defer Service
                        </button>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-3">Vehicle Notes</h3>
                    <p class="text-sm text-slate-500 leading-relaxed italic">
                        {{ $fleet->notes ?? 'No additional notes for this vehicle.' }}
                    </p>
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

    <!-- Log Maintenance Modal -->
    <div x-show="showLogModal" 
         class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/90 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-0 overflow-hidden shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)]" @click.away="showLogModal = false">
            <!-- Modal Header -->
            <div class="bg-[var(--nmis-primary)] p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight">Log Service: <span class="text-sky-300">{{ $fleet->fleet_code }}</span></h3>
                        <p class="mt-1 text-sm text-sky-100/80 font-medium">Feeding technical details for lifecycle tracking</p>
                    </div>
                    <button @click="showLogModal = false" class="rounded-full bg-white/10 p-2 hover:bg-white/20 transition-all">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('fleet.maintenance.log', $fleet->encrypted_id) }}" method="POST" class="p-8"
                  x-data="{ 
                      items: [],
                      manualCost: 0,
                      addItem() {
                          this.items.push({ category: 'engine', description: '', cost: 0, lifespan_km: 5000 });
                      },
                      removeItem(index) {
                          this.items.splice(index, 1);
                      },
                      get totalCost() {
                          return this.items.reduce((sum, item) => sum + parseFloat(item.cost || 0), 0) + parseFloat(this.manualCost || 0);
                      }
                  }">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">General Service Type</label>
                        <select name="service_type" required class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 px-5 py-4 text-base font-bold text-slate-700 focus:border-[var(--nmis-primary)] focus:ring-0 transition-all outline-none">
                            <option value="preventive">Preventive Maintenance</option>
                            <option value="corrective">Corrective (Repair)</option>
                            <option value="inspection">Routine Inspection</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Service Completion Date</label>
                        <input type="date" name="performed_at" value="{{ date('Y-m-d') }}" required class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 px-5 py-4 text-base font-bold text-slate-700 focus:border-[var(--nmis-primary)] focus:ring-0 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Current Odometer (km)</label>
                        <div class="relative">
                            <input type="number" name="odometer_reading" value="{{ $fleet->current_odometer }}" required class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 pl-5 pr-12 py-4 text-lg font-black text-slate-900 focus:border-[var(--nmis-primary)] focus:ring-0 transition-all outline-none">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 uppercase">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Labor & Extra Cost</label>
                        <div class="relative">
                            <input type="number" name="cost" x-model="manualCost" required placeholder="0" class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 pl-12 pr-5 py-4 text-lg font-black text-slate-900 focus:border-[var(--nmis-primary)] focus:ring-0 transition-all outline-none border-dashed">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">TSh</span>
                        </div>
                    </div>
                </div>

                <!-- Itemized Breakdown -->
                <div class="mt-10 border-t border-slate-100 pt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-base font-black text-slate-900 uppercase tracking-widest flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-[var(--nmis-secondary)]"></span>
                            Itemized Components
                        </h4>
                        <button type="button" @click="addItem()" class="inline-flex items-center gap-2 rounded-xl bg-sky-50 px-4 py-2 text-xs font-black text-sky-600 hover:bg-sky-100 transition-all uppercase tracking-tighter">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Component
                        </button>
                    </div>
                    
                    <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-2xl border-2 border-slate-50 bg-slate-50/30 p-5 space-y-4 relative group hover:border-[var(--nmis-secondary)]/30 transition-all">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="'COMPONENT #' + (index + 1)"></span>
                                    <button type="button" @click="removeItem(index)" class="text-rose-400 hover:text-rose-600 transition-all">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-4">
                                        <select :name="'items['+index+'][category]'" x-model="item.category" class="w-full rounded-xl border-slate-200 text-sm font-bold text-slate-700 bg-white py-3 focus:ring-0">
                                            <option value="engine">Engine / Oil</option>
                                            <option value="tires">Tires</option>
                                            <option value="brakes">Brakes</option>
                                            <option value="battery">Battery</option>
                                            <option value="suspension">Suspension</option>
                                            <option value="other">Other Parts</option>
                                        </select>
                                    </div>
                                    <div class="col-span-8">
                                        <input type="text" :name="'items['+index+'][description]'" x-model="item.description" placeholder="Technical Description (e.g. Shell Rimula R4 5L)" class="w-full rounded-xl border-slate-200 text-sm font-bold bg-white py-3 focus:ring-0">
                                    </div>
                                    <div class="col-span-6">
                                        <div class="relative">
                                            <input type="number" :name="'items['+index+'][cost]'" x-model="item.cost" placeholder="Item Cost" class="w-full rounded-xl border-slate-200 text-sm font-bold bg-white py-3 pl-10 focus:ring-0">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">TSh</span>
                                        </div>
                                    </div>
                                    <div class="col-span-6">
                                        <div class="relative">
                                            <input type="number" :name="'items['+index+'][lifespan_km]'" x-model="item.lifespan_km" placeholder="Lifespan (km)" class="w-full rounded-xl border-slate-200 text-sm font-bold bg-white py-3 pr-10 focus:ring-0">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">km</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Grand Total Footer -->
                    <div class="mt-8 rounded-2xl bg-slate-900 p-6 text-white flex justify-between items-center shadow-lg shadow-slate-900/20">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Final Estimated Cost</p>
                            <span class="text-2xl font-black tracking-tight" x-text="'TSh ' + new Intl.NumberFormat().format(totalCost)"></span>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center text-[var(--nmis-secondary)]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="showLogModal = false" class="flex-1 rounded-2xl border-2 border-slate-100 py-4 text-base font-black text-slate-500 hover:bg-slate-50 transition-all uppercase tracking-widest">Discard</button>
                    <button type="submit" class="flex-[2] rounded-2xl bg-[var(--nmis-primary)] py-4 text-base font-black text-white shadow-xl shadow-[var(--nmis-primary)]/20 hover:scale-[1.02] transition-all uppercase tracking-widest">Complete Record</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Defer Maintenance Modal -->
    <div x-show="showDeferModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl" @click.away="showDeferModal = false">
            <h3 class="text-xl font-black text-slate-900">Defer Service</h3>
            <p class="mt-2 text-sm text-slate-500">Postpone the next service target by a specific distance.</p>

            <form action="{{ route('fleet.defer', $fleet->encrypted_id) }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Add Distance (km)</label>
                    <input type="number" name="extra_km" value="500" step="100" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Reason for Deferral</label>
                    <textarea name="reason" required rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500/20 transition-all resize-none" placeholder="Why is this service being postponed?"></textarea>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" @click="showDeferModal = false" class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-amber-500 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 hover:bg-amber-400 transition-all">Postpone Service</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    </div>
</x-app-layout>
