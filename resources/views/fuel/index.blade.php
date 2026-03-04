<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Fuel Requisitions</h2>
                <p class="mt-2 text-sm text-slate-500">Manage and track fuel requests across all trips and fleets</p>
            </div>
            
            @can('fuel.create')
                <div class="flex items-center gap-2">
                    <button
                        x-data="{}"
                        @click="$dispatch('open-fuel-balance-modal')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Record Fuel Balance
                    </button>
                    <button 
                        x-data="{}"
                        @click="$dispatch('open-fuel-modal')"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Fuel Requisition
                    </button>
                </div>
            @endcan
        </div>
    </x-slot>

    @php
        $ordersMeta = $orders->mapWithKeys(fn ($order) => [
            (string) $order->id => [
                'distance_km' => $order->distance_km !== null ? (float) $order->distance_km : null,
                'status' => $order->status,
            ],
        ]);
        $fleetBalanceMeta = $fleets->mapWithKeys(fn ($fleet) => [
            (string) $fleet->id => (float) ($fleetBalances[$fleet->id] ?? 0),
        ]);
    @endphp

    <div class="space-y-6"
         x-data="fuelManager(@js($ordersMeta), @js($orderFleetMap), @js($fleetBalanceMeta))"
         @open-fuel-modal.window="showCreateModal = true"
         @open-fuel-balance-modal.window="showBalanceModal = true">
        
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
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Requisition Type <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="requisition_type" x-model="requisitionType" @change="onTypeChange()"
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="order_based">Fleet With Order</option>
                                        <option value="fleet_only">Fleet Without Order</option>
                                    </select>
                                </div>

                                <!-- Order Selection -->
                                <div x-show="requisitionType === 'order_based'" x-cloak>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Order <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="order_id" id="order-select" @change="onOrderChange()"
                                            :required="requisitionType === 'order_based'"
                                            :disabled="requisitionType !== 'order_based'"
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose an order...</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->encrypted_id }}" data-order-id="{{ $order->id }}">{{ $order->order_number }} - {{ $order->cargo_type ?? 'N/A' }} ({{ strtoupper($order->status) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="requisitionType === 'fleet_only'" x-cloak>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Route Origin <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="origin_address"
                                              rows="2"
                                              :required="requisitionType === 'fleet_only'"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                              placeholder="Enter origin address"
                                              x-model="fleetOnlyOrigin"></textarea>
                                </div>

                                <div x-show="requisitionType === 'fleet_only'" x-cloak>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Route Destination <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="destination_address"
                                              rows="2"
                                              :required="requisitionType === 'fleet_only'"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                              placeholder="Enter destination address"
                                              x-model="fleetOnlyDestination"></textarea>
                                </div>

                                <!-- Fleet Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Fleet <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="fleet_id" id="fleet-select" @change="onFleetChange()"
                                            :disabled="createFleetDisabled"
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose a fleet...</option>
                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->encrypted_id }}" data-fleet-id="{{ $fleet->id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }} | Trailer: {{ $fleet->trailer_number ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs" :class="hasAssignedFleets ? 'text-emerald-700' : 'text-amber-600'" x-text="createFleetHint"></p>
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

                                <div class="rounded-xl bg-slate-50 p-4 md:col-span-2">
                                    <p class="text-sm font-semibold text-slate-800">Distance and Fuel Estimation</p>
                                    <p class="mt-1 text-xs text-slate-500">Formula: 1 km uses 0.5 litres</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Order Distance (km)</label>
                                            <input type="number" step="0.01" name="base_distance_km" readonly
                                                   class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                                   x-model="baseDistanceKm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Additional Distance (km)</label>
                                            <input type="number" step="0.01" min="0" name="additional_distance_km"
                                                   class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20"
                                                   x-model="additionalDistanceKm" @input="recalculateDistance()">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Total Distance (km)</label>
                                            <input type="number" step="0.01" name="total_distance_km" readonly
                                                   class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                                   x-model="totalDistanceKm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Fleet Balance (L)</label>
                                            <input type="number" step="0.01" name="available_balance_litres" readonly
                                                   class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                                   x-model="availableBalanceLitres">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Estimated Fuel (L)</label>
                                            <input type="number" step="0.01" name="estimated_fuel_litres" readonly
                                                   class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                                   x-model="estimatedFuelLitres">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Required to Request (L)</label>
                                            <input type="number" step="0.01" name="additional_litres" readonly
                                                   class="w-full rounded-lg border-slate-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800"
                                                   x-model="requestedLitres">
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <button type="button"
                                                x-show="requisitionType === 'fleet_only'"
                                                @click="calculateFleetOnlyDistance()"
                                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                                            </svg>
                                            Calculate Route Distance
                                        </button>
                                        <button type="button"
                                                @click="calculateFuel()"
                                                class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-xs font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                                Calculate Fuel
                                        </button>
                                    </div>
                                    <p x-show="distanceEstimateError" x-text="distanceEstimateError" class="mt-2 text-xs text-rose-600"></p>
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
                                               @input="calculateTotal()"
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
                                               @input="calculateTotal()"
                                               class="w-full rounded-xl border-slate-300 bg-slate-50 pl-14 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Payment Channel -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Payment Channel <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="payment_channel"
                                           required
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="e.g., M-PESA, Bank Transfer, Fuel Card">
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
                                    <p class="mt-1 text-xs text-slate-500">Calculated as (required litres x price) - discount</p>
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

        @can('fuel.create')
            <div x-show="showBalanceModal"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showBalanceModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4">
                            <h3 class="text-lg font-semibold text-slate-900">Record Fuel Balance After Trip</h3>
                            <button @click="showBalanceModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('fuel.balance.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Completed Order</label>
                                    <select name="order_id" id="balance-order-select" @change="onBalanceOrderChange()"
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose completed order...</option>
                                        @foreach($completedOrders as $order)
                                            <option value="{{ $order->encrypted_id }}" data-order-id="{{ $order->id }}">{{ $order->order_number }} - {{ $order->cargo_type ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fleet</label>
                                    <select name="fleet_id" id="balance-fleet-select" :disabled="balanceFleetDisabled"
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose fleet...</option>
                                        @foreach($fleets as $fleet)
                                            <option value="{{ $fleet->encrypted_id }}" data-fleet-id="{{ $fleet->id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs" :class="balanceHasAssignedFleets ? 'text-emerald-700' : 'text-amber-600'" x-text="balanceFleetHint"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Remaining Litres</label>
                                    <input type="number" step="0.01" min="0" name="remaining_litres" required
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="0.00">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Remarks (Optional)</label>
                                    <textarea name="remarks" rows="2"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"></textarea>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" @click="showBalanceModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Cancel</button>
                                <button type="submit"
                                        class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl transition-all">Save Balance</button>
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
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalRequisitions) }}</p>
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
                <p class="mt-3 text-3xl font-bold text-slate-900" id="submitted-count">{{ number_format((int) ($statusCounts['submitted'] ?? 0)) }}</p>
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
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]" id="supervisor-count">{{ number_format((int) ($statusCounts['supervisor_approved'] ?? 0)) }}</p>
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
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]" id="accountant-count">{{ number_format((int) ($statusCounts['accountant_approved'] ?? 0)) }}</p>
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
                <p class="mt-3 text-3xl font-bold text-slate-900" id="total-amount">TSh {{ number_format($totalAmount, 0) }}</p>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="rounded-xl border border-slate-200/60 bg-white p-3 shadow-sm">
            <div class="flex flex-wrap gap-2">
                @foreach($statusTabs as $tab)
                    @php
                        $isActiveTab = $activeStatus === $tab['key'];
                        $labelColor = match ($tab['key']) {
                            'submitted' => 'text-[var(--nmis-primary)]',
                            'supervisor_approved' => 'text-[var(--nmis-secondary)]',
                            'supervisor_rejected' => 'text-rose-700',
                            'accountant_approved' => 'text-[var(--nmis-accent)]',
                            'accountant_rejected' => 'text-rose-700',
                            default => 'text-slate-700',
                        };
                    @endphp
                    <a href="{{ route('fuel.index', ['status' => $tab['key']]) }}"
                       class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition-all {{ $isActiveTab ? 'border-slate-300 bg-slate-50 shadow-sm' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                        <span class="{{ $labelColor }}">{{ $tab['label'] }}</span>
                        <span class="inline-flex min-w-[1.5rem] justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search -->
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   id="fuel-search"
                   placeholder="Search by requisition ID, order number, fleet, requester, station..."
                   class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
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
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fuel Math</th>
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
                                            <div class="text-sm {{ in_array($item->status, ['submitted', 'supervisor_approved'], true) ? 'font-bold text-slate-900' : 'font-medium text-slate-900' }}">
                                                REQ-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <div class="text-xs text-slate-500">Requested: {{ $item->requester?->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-medium text-slate-900">{{ $item->order?->order_number ?? 'No Order' }}</div>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ ($item->requisition_type ?? 'order_based') === 'fleet_only' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ ($item->requisition_type ?? 'order_based') === 'fleet_only' ? 'Fleet Only' : 'Order Based' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                                        </svg>
                                        {{ $item->fleet?->fleet_code ?? 'No fleet' }} - {{ $item->fleet?->plate_number ?? '' }}
                                    </div>
                                    @if(($item->requisition_type ?? 'order_based') === 'fleet_only')
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $item->origin_address ?? '-' }} to {{ $item->destination_address ?? '-' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $item->fuel_station }}</div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ number_format($item->additional_litres, 1) }} L x TSh {{ number_format($item->fuel_price, 2) }}
                                        @if($item->discount > 0)
                                            <span class="text-amber-600"> (Discount: TSh {{ number_format($item->discount, 2) }})</span>
                                        @endif
                                    </div>
                                    <div class="text-sm font-semibold text-[var(--nmis-primary)] mt-1">
                                        TSh {{ number_format($item->total_amount, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid gap-1 text-xs text-slate-600 min-w-[230px]">
                                        <div class="flex items-center justify-between">
                                            <span>Base distance</span>
                                            <span class="font-semibold text-slate-800">{{ number_format((float) ($item->base_distance_km ?? 0), 2) }} km</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>Additional distance</span>
                                            <span class="font-semibold text-slate-800">{{ number_format((float) ($item->additional_distance_km ?? 0), 2) }} km</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>Total distance</span>
                                            <span class="font-semibold text-slate-900">{{ number_format((float) ($item->total_distance_km ?? 0), 2) }} km</span>
                                        </div>
                                        <div class="h-px bg-slate-200 my-1"></div>
                                        <div class="flex items-center justify-between">
                                            <span>Estimated fuel</span>
                                            <span class="font-semibold text-[var(--nmis-primary)]">{{ number_format((float) ($item->estimated_fuel_litres ?? 0), 2) }} L</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>Fleet balance</span>
                                            <span class="font-semibold text-slate-800">{{ number_format((float) ($item->available_balance_litres ?? 0), 2) }} L</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-md bg-emerald-50 px-2 py-1">
                                            <span class="text-emerald-700">Requested</span>
                                            <span class="font-semibold text-emerald-700">{{ number_format((float) ($item->additional_litres ?? 0), 2) }} L</span>
                                        </div>
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
                                    <div class="flex min-w-[220px] items-center gap-2">
                                        <a href="{{ route('fuel.show', $item->encrypted_id) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-[var(--nmis-primary)] hover:bg-[var(--nmis-primary)]/5 transition-all">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.269 2.943 9.542 7-1.273 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        @if($item->status === 'submitted')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending Supervisor</span>
                                        @elseif($item->status === 'supervisor_approved')
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Pending Accounting</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
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
        function fuelManager(ordersMeta = {}, orderFleetMap = {}, fleetBalanceMeta = {}) {
            return {
                showCreateModal: false,
                showBalanceModal: false,
                requisitionType: 'order_based',
                baseDistanceKm: 0,
                additionalDistanceKm: 0,
                totalDistanceKm: 0,
                estimatedFuelLitres: 0,
                availableBalanceLitres: 0,
                requestedLitres: 0,
                fleetOnlyOrigin: '',
                fleetOnlyDestination: '',
                distanceEstimateError: '',
                createFleetDisabled: true,
                hasAssignedFleets: false,
                createFleetHint: 'Select an order first to view assigned fleet(s).',
                balanceFleetDisabled: true,
                balanceHasAssignedFleets: false,
                balanceFleetHint: 'Select a completed order first to view assigned fleet(s).',
                ordersMeta,
                orderFleetMap,
                fleetBalanceMeta,

                init() {
                    this.onTypeChange();
                    this.onOrderChange();
                    this.onBalanceOrderChange();
                },

                resetFleetOptions(fleetSelectId) {
                    const fleetSelect = document.getElementById(fleetSelectId);
                    if (!fleetSelect) {
                        return;
                    }

                    Array.from(fleetSelect.options).forEach((option, index) => {
                        option.hidden = false;
                        option.disabled = false;
                        if (index === 0) {
                            option.hidden = false;
                            option.disabled = false;
                        }
                    });
                },

                onTypeChange() {
                    this.distanceEstimateError = '';
                    if (this.requisitionType === 'fleet_only') {
                        this.resetFleetOptions('fleet-select');
                        this.createFleetDisabled = false;
                        this.hasAssignedFleets = true;
                        this.createFleetHint = 'Fleet-only requisition: choose any eligible fleet and calculate route distance.';
                        this.baseDistanceKm = 0;
                        this.recalculateDistance();
                        this.onFleetChange();
                        return;
                    }

                    this.fleetOnlyOrigin = '';
                    this.fleetOnlyDestination = '';
                    this.onOrderChange();
                },

                applyOrderFleetFilter(orderSelectId, fleetSelectId) {
                    const orderSelect = document.getElementById(orderSelectId);
                    const selectedOrderOption = orderSelect?.selectedOptions?.[0] || null;
                    const orderNumericId = selectedOrderOption?.dataset?.orderId || '';
                    const fleetSelect = document.getElementById(fleetSelectId);
                    if (!fleetSelect) {
                        return { orderNumericId, hasAssignedFleets: false, labels: [] };
                    }

                    const assignedFleetIds = (this.orderFleetMap[orderNumericId] || []).map((id) => String(id));
                    const assignedFleetLabels = [];

                    Array.from(fleetSelect.options).forEach((option, index) => {
                        if (index === 0) {
                            option.hidden = false;
                            option.disabled = false;
                            return;
                        }

                        const fleetNumericId = option.dataset?.fleetId || '';
                        const isAssigned = orderNumericId ? assignedFleetIds.includes(fleetNumericId) : false;
                        option.hidden = !isAssigned;
                        option.disabled = !isAssigned;

                        if (isAssigned) {
                            assignedFleetLabels.push(option.textContent.trim());
                        }
                    });

                    const selectedFleetOption = fleetSelect.selectedOptions?.[0] || null;
                    const selectedFleetNumericId = selectedFleetOption?.dataset?.fleetId || '';
                    if (fleetSelect.value && !assignedFleetIds.includes(selectedFleetNumericId)) {
                        fleetSelect.value = '';
                    }

                    return {
                        orderNumericId,
                        hasAssignedFleets: assignedFleetIds.length > 0,
                        labels: assignedFleetLabels,
                    };
                },

                onOrderChange() {
                    if (this.requisitionType !== 'order_based') {
                        return;
                    }

                    const result = this.applyOrderFleetFilter('order-select', 'fleet-select');
                    this.createFleetDisabled = !result.orderNumericId || !result.hasAssignedFleets;
                    this.hasAssignedFleets = result.hasAssignedFleets;
                    this.createFleetHint = !result.orderNumericId
                        ? 'Select an order first to view assigned fleet(s).'
                        : (!result.hasAssignedFleets
                            ? 'No fleet is assigned to this order yet. Assign one in Orders -> Manage Legs.'
                            : `Assigned fleet(s): ${result.labels.join(', ')}`);

                    const distance = this.ordersMeta?.[result.orderNumericId]?.distance_km;
                    this.baseDistanceKm = distance !== null && distance !== undefined ? parseFloat(distance) : 0;
                    this.recalculateDistance();
                    this.onFleetChange();
                },

                onBalanceOrderChange() {
                    const result = this.applyOrderFleetFilter('balance-order-select', 'balance-fleet-select');
                    this.balanceFleetDisabled = !result.orderNumericId || !result.hasAssignedFleets;
                    this.balanceHasAssignedFleets = result.hasAssignedFleets;
                    this.balanceFleetHint = !result.orderNumericId
                        ? 'Select a completed order first to view assigned fleet(s).'
                        : (!result.hasAssignedFleets
                            ? 'No fleet is assigned to this order yet.'
                            : `Assigned fleet(s): ${result.labels.join(', ')}`);
                },

                onFleetChange() {
                    const selectedFleetOption = document.getElementById('fleet-select')?.selectedOptions?.[0] || null;
                    const fleetNumericId = selectedFleetOption?.dataset?.fleetId || '';
                    this.availableBalanceLitres = parseFloat(this.fleetBalanceMeta?.[fleetNumericId] ?? 0);
                    this.calculateFuel();
                },

                async calculateFleetOnlyDistance() {
                    this.distanceEstimateError = '';
                    const origin = (this.fleetOnlyOrigin || '').trim();
                    const destination = (this.fleetOnlyDestination || '').trim();
                    if (!origin || !destination) {
                        this.distanceEstimateError = 'Origin and destination are required for fleet-only requisition.';
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('fuel.distance.estimate') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                origin_address: origin,
                                destination_address: destination,
                            }),
                        });

                        const payload = await response.json();
                        if (!response.ok) {
                            this.distanceEstimateError = payload?.message || 'Distance could not be calculated.';
                            return;
                        }

                        this.baseDistanceKm = parseFloat(payload.distance_km || 0);
                        this.recalculateDistance();
                        this.calculateFuel();
                    } catch (error) {
                        this.distanceEstimateError = 'Distance could not be calculated right now. Please try again shortly.';
                    }
                },

                recalculateDistance() {
                    const base = parseFloat(this.baseDistanceKm || 0);
                    const extra = parseFloat(this.additionalDistanceKm || 0);
                    this.totalDistanceKm = Math.max(base + extra, 0);
                },

                calculateFuel() {
                    this.recalculateDistance();
                    this.estimatedFuelLitres = +(this.totalDistanceKm * 0.5).toFixed(2);
                    this.requestedLitres = +Math.max(this.estimatedFuelLitres - this.availableBalanceLitres, 0).toFixed(2);
                    this.calculateTotal();
                },

                calculateTotal() {
                    const price = parseFloat(document.querySelector('input[name="fuel_price"]')?.value) || 0;
                    const discount = parseFloat(document.querySelector('input[name="discount"]')?.value) || 0;
                    const total = Math.max((this.requestedLitres * price) - discount, 0);
                    const node = document.getElementById('total-preview');
                    if (node) node.textContent = 'TSh ' + total.toFixed(2);
                }
            }
        }

        // Live search functionality (status is handled by tabs via query string)
        document.getElementById('fuel-search')?.addEventListener('keyup', filterRequisitions);

        function filterRequisitions() {
            const searchTerm = document.getElementById('fuel-search').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan="7"]')) return; // Skip empty state row

                const rowText = row.textContent.toLowerCase();
                const matchesSearch = rowText.includes(searchTerm);
                row.style.display = matchesSearch ? '' : 'none';
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
