<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Fuel Requisitions</h2>
                <p class="mt-2 text-sm text-slate-500">Manage and track fuel requests across all trips and fleets</p>
            </div>
            
            @can('fuel.create')
                <button 
                    x-data="{}"
                    @click="$dispatch('open-fuel-modal')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                    <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Fuel Requisition
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6" 
         x-data="fuelManager()" 
         @open-fuel-modal.window="showCreateModal = true">
        
        <!-- Create Fuel Requisition Modal -->
        @can('fuel.create')
            <div x-show="showCreateModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showCreateModal = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="showCreateModal" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
                        
                        <!-- Modal Header -->
                        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200/60 bg-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                    <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">New Fuel Requisition</h3>
                                    <p class="text-xs text-slate-500">Submit a fuel request for approval</p>
                                </div>
                            </div>
                            <button @click="showCreateModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form method="POST" action="{{ route('fuel.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 md:grid-cols-2">
                                <!-- Order Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Order <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="order_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose an order...</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->encrypted_id }}">{{ $order->order_number }} - {{ $order->cargo_type ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Fleet Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Fleet <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="fleet_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose a fleet...</option>
                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }} ({{ $fleet->capacity_tons }} tons)</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Fuel Station -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Fuel Station <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="fuel_station" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="e.g., Total, Shell, Ola">
                                </div>

                                <!-- Additional Litres -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Additional Litres <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="additional_litres" 
                                           step="0.01" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="0.00">
                                </div>

                                <!-- Fuel Price -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Fuel Price (per litre) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-500">TSh</span>
                                        <input type="number" 
                                               name="fuel_price" 
                                               step="0.01" 
                                               required 
                                               class="w-full rounded-xl border-slate-300 bg-slate-50 pl-14 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Discount -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Discount (Optional)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-500">TSh</span>
                                        <input type="number" 
                                               name="discount" 
                                               step="0.01" 
                                               value="0"
                                               class="w-full rounded-xl border-slate-300 bg-slate-50 pl-14 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Payment Channel -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Payment Channel <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="payment_channel" 
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                            required>
                                        <option value="">Select channel...</option>
                                        <option value="M-PESA">M-PESA</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Fuel Card">Fuel Card</option>
                                    </select>
                                </div>

                                <!-- Payment Account -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Account / Phone Number <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="payment_account" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="e.g., 07XX XXX XXX or Account number">
                                </div>

                                <!-- Remarks (Optional) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Additional Remarks
                                    </label>
                                    <textarea name="remarks" 
                                              rows="2"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                              placeholder="Any additional information..."></textarea>
                                </div>

                                <!-- Total Preview -->
                                <div class="md:col-span-2 rounded-xl bg-slate-50 p-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-slate-700">Total Amount Preview:</span>
                                        <span class="text-lg font-bold text-[var(--nmis-primary)]" id="total-preview">TSh 0.00</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">Calculated as (litres × price) - discount</p>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" 
                                        @click="showCreateModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                    Submit Requisition
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Success Message -->
        @if(session('success'))
            <div class="animate-slide-down rounded-xl border border-emerald-200 bg-emerald-50/90 backdrop-blur-sm px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-emerald-100 p-1">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-5">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Requisitions</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $requisitions->total() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Submitted</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="submitted-count">{{ $requisitions->where('status', 'submitted')->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Supervisor Approved</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]" id="supervisor-count">{{ $requisitions->where('status', 'supervisor_approved')->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Accountant Approved</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]" id="accountant-count">{{ $requisitions->where('status', 'accountant_approved')->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Amount</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="total-amount">TSh {{ number_format($requisitions->sum('total_amount'), 0) }}</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       id="fuel-search"
                       placeholder="Search by order number, fleet code, station..." 
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
            </div>
            <div class="flex items-center gap-3">
                <select id="fuel-status-filter" 
                        class="rounded-xl border-slate-200 bg-white px-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all min-w-[150px]">
                    <option value="">All Statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="supervisor_approved">Supervisor Approved</option>
                    <option value="supervisor_rejected">Supervisor Rejected</option>
                    <option value="accountant_approved">Accountant Approved</option>
                    <option value="accountant_rejected">Accountant Rejected</option>
                </select>
                <button type="button" 
                        id="fuel-filter-btn"
                        class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all shadow-lg shadow-[var(--nmis-primary)]/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Requisitions Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Requisition ID</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Order & Fleet</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Station & Amount</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Timeline</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($requisitions as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                                #{{ $item->id }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900">REQ-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</div>
                                            <div class="text-xs text-slate-500">Requested: {{ $item->requester?->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $item->order?->order_number ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                                        </svg>
                                        {{ $item->fleet?->fleet_code ?? 'No fleet' }} - {{ $item->fleet?->plate_number ?? '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $item->fuel_station }}</div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ number_format($item->additional_litres, 1) }} L × TSh {{ number_format($item->fuel_price, 2) }}
                                        @if($item->discount > 0)
                                            <span class="text-amber-600"> (Discount: TSh {{ number_format($item->discount, 2) }})</span>
                                        @endif
                                    </div>
                                    <div class="text-sm font-semibold text-[var(--nmis-primary)] mt-1">
                                        TSh {{ number_format($item->total_amount, 2) }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'submitted' => 'bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]',
                                            'supervisor_approved' => 'bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]',
                                            'supervisor_rejected' => 'bg-rose-100 text-rose-700',
                                            'accountant_approved' => 'bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)]',
                                            'accountant_rejected' => 'bg-rose-100 text-rose-700',
                                        ];
                                        $statusIcons = [
                                            'submitted' => 'M12 4v16m8-8H4',
                                            'supervisor_approved' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'supervisor_rejected' => 'M6 18L18 6M6 6l12 12',
                                            'accountant_approved' => 'M5 13l4 4L19 7',
                                            'accountant_rejected' => 'M6 18L18 6M6 6l12 12',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$item->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcons[$item->status] ?? 'M12 4v16m8-8H4' }}"></path>
                                        </svg>
                                        {{ str_replace('_', ' ', ucfirst($item->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1 text-xs">
                                        @if($item->supervisor)
                                            <div class="flex items-center gap-1 text-slate-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span class="font-medium">Supervisor:</span> {{ $item->supervisor->name }}
                                                @if($item->supervisor_remarks)
                                                    <span class="text-slate-400" title="{{ $item->supervisor_remarks }}">(remarks)</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if($item->accountant)
                                            <div class="flex items-center gap-1 text-slate-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="font-medium">Accountant:</span> {{ $item->accountant->name }}
                                                @if($item->accountant_remarks)
                                                    <span class="text-slate-400" title="{{ $item->accountant_remarks }}">(remarks)</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if(!$item->supervisor && !$item->accountant)
                                            <span class="text-slate-400">Pending initial review</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2 min-w-[200px]">
                                        @can('fuel.approve.supervisor')
                                            @if ($item->status === 'submitted')
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('fuel.supervisor.decision', $item->encrypted_id) }}" 
                                                          class="flex-1"
                                                          onsubmit="return confirm('Approve this requisition?')">
                                                        @csrf
                                                        <input type="hidden" name="approved" value="1" />
                                                        <div class="flex gap-1">
                                                            <input name="remarks" 
                                                                   class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1 text-xs focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20" 
                                                                   placeholder="Remarks (optional)" />
                                                            <button type="submit" 
                                                                    class="rounded-lg bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-200 transition-colors"
                                                                    title="Approve">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </form>
                                                    <form method="POST" action="{{ route('fuel.supervisor.decision', $item->encrypted_id) }}" 
                                                          class="flex-1"
                                                          onsubmit="return confirm('Reject this requisition?')">
                                                        @csrf
                                                        <input type="hidden" name="approved" value="0" />
                                                        <div class="flex gap-1">
                                                            <input name="remarks" 
                                                                   class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1 text-xs focus:border-rose-300 focus:ring-1 focus:ring-rose-200" 
                                                                   placeholder="Reason (required)" 
                                                                   required />
                                                            <button type="submit" 
                                                                    class="rounded-lg bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-200 transition-colors"
                                                                    title="Reject">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        @endcan

                                        @can('fuel.approve.accounting')
                                            @if ($item->status === 'supervisor_approved')
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('fuel.accountant.decision', $item->encrypted_id) }}" 
                                                          class="flex-1"
                                                          onsubmit="return confirm('Finalize this requisition?')">
                                                        @csrf
                                                        <input type="hidden" name="approved" value="1" />
                                                        <div class="flex gap-1">
                                                            <input name="remarks" 
                                                                   class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1 text-xs focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20" 
                                                                   placeholder="Remarks (optional)" />
                                                            <button type="submit" 
                                                                    class="rounded-lg bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-200 transition-colors"
                                                                    title="Finalize">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </form>
                                                    <form method="POST" action="{{ route('fuel.accountant.decision', $item->encrypted_id) }}" 
                                                          class="flex-1"
                                                          onsubmit="return confirm('Reject this requisition?')">
                                                        @csrf
                                                        <input type="hidden" name="approved" value="0" />
                                                        <div class="flex gap-1">
                                                            <input name="remarks" 
                                                                   class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-1 text-xs focus:border-rose-300 focus:ring-1 focus:ring-rose-200" 
                                                                   placeholder="Reason (required)" 
                                                                   required />
                                                            <button type="submit" 
                                                                    class="rounded-lg bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-200 transition-colors"
                                                                    title="Reject">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        @endcan
                                        
                                        @if(!in_array($item->status, ['submitted', 'supervisor_approved']))
                                            <span class="text-xs text-slate-400">No actions available</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-1">No requisitions found</h3>
                                        <p class="text-sm text-slate-500 mb-4">Get started by creating your first fuel requisition</p>
                                        @can('fuel.create')
                                            <button 
                                                x-data="{}"
                                                @click="$dispatch('open-fuel-modal')"
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                New Requisition
                            </button>
                                        @endcan
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
            {{ $requisitions->links() }}
        </div>
    </div>

    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }

        [x-cloak] {
            display: none !important;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @push('scripts')
    <script>
        function fuelManager() {
            return {
                showCreateModal: false,
                
                calculateTotal() {
                    const litres = parseFloat(document.querySelector('input[name="additional_litres"]')?.value) || 0;
                    const price = parseFloat(document.querySelector('input[name="fuel_price"]')?.value) || 0;
                    const discount = parseFloat(document.querySelector('input[name="discount"]')?.value) || 0;
                    const total = (litres * price) - discount;
                    
                    document.getElementById('total-preview').textContent = 'TSh ' + total.toFixed(2);
                }
            }
        }

        // Live calculation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['additional_litres', 'fuel_price', 'discount'];
            inputs.forEach(id => {
                const input = document.querySelector(`[name="${id}"]`);
                if (input) {
                    input.addEventListener('input', () => {
                        if (window.fuelManager) {
                            window.fuelManager.calculateTotal();
                        }
                    });
                }
            });
        });

        // Live search functionality
        document.getElementById('fuel-search')?.addEventListener('keyup', filterRequisitions);
        document.getElementById('fuel-status-filter')?.addEventListener('change', filterRequisitions);
        document.getElementById('fuel-filter-btn')?.addEventListener('click', filterRequisitions);

        function filterRequisitions() {
            const searchTerm = document.getElementById('fuel-search').value.toLowerCase();
            const statusFilter = document.getElementById('fuel-status-filter').value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan="6"]')) return; // Skip empty state row
                
                const orderText = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const stationText = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const status = row.querySelector('td:nth-child(4) span')?.textContent.trim().toLowerCase().replace(/\s+/g, '_') || '';
                
                const matchesSearch = orderText.includes(searchTerm) || stationText.includes(searchTerm);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        // Auto-hide success message after 5 seconds
        const successMessage = document.querySelector('.animate-slide-down');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
    @endpush
</x-app-layout>