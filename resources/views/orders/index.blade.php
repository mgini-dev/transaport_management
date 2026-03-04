<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Orders Management</h2>
                <p class="mt-2 text-sm text-slate-500">Track and manage all customer orders across trips</p>
            </div>
            
            @can('orders.create')
                <button 
                    x-data="{}"
                    @click="$dispatch('open-order-modal')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                    <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Order
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6" 
         x-data="{ openOrderModal: @js($autoOpenCreateModal && auth()->user()->can('orders.create')) }" 
         @open-order-modal.window="openOrderModal = true">
        
        <!-- Create Order Modal -->
        @can('orders.create')
            <div x-show="openOrderModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openOrderModal = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="openOrderModal" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
                        
                        <!-- Modal Header -->
                        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200/60 bg-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                    <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Create New Order</h3>
                                    <p class="text-xs text-slate-500">Fill in the order details below</p>
                                </div>
                            </div>
                            <button @click="openOrderModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form method="POST" action="{{ route('orders.store') }}" class="p-6">
                            @csrf
                            <div class="grid gap-5 md:grid-cols-2">
                                <!-- Trip Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Trip <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="trip_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose a trip...</option>
                                        @foreach ($trips as $trip)
                                            <option value="{{ $trip->encrypted_id }}" {{ ($prefillTripEncryptedId ?? null) === $trip->encrypted_id ? 'selected' : '' }}>{{ $trip->trip_number }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Customer Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Select Customer <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="customer_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                        <option value="">Choose a customer...</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->encrypted_id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Cargo Type -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Cargo Type <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="cargo_type" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="e.g., Electronics, Food, Equipment">
                                </div>

                                <!-- Cargo Description -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Cargo Description
                                    </label>
                                    <input type="text" 
                                           name="cargo_description" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="Brief description">
                                </div>

                                <!-- Weight -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Weight (tons) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="weight_tons" 
                                           step="0.01" 
                                           required 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                           placeholder="0.00">
                                </div>

                                <!-- Agreed Price -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Agreed Price <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-500">$</span>
                                        <input type="number" 
                                               name="agreed_price" 
                                               step="0.01" 
                                               required 
                                               class="w-full rounded-xl border-slate-300 bg-slate-50 pl-8 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Origin Region -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Origin Region (Tanzania) <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="origin_address"
                                            required
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                        <option value="">Choose origin region...</option>
                                        @foreach($tanzaniaRegions as $region)
                                            <option value="{{ $region }}" {{ old('origin_address') === $region ? 'selected' : '' }}>{{ $region }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Destination Region -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Destination Region (Tanzania) <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="destination_address"
                                            required
                                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                        <option value="">Choose destination region...</option>
                                        @foreach($tanzaniaRegions as $region)
                                            <option value="{{ $region }}" {{ old('destination_address') === $region ? 'selected' : '' }}>{{ $region }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Expected Loading Date -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Expected Loading Date
                                    </label>
                                    <input type="date" 
                                           name="expected_loading_date" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                </div>

                                <!-- Expected Leaving Date -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Expected Leaving Date
                                    </label>
                                    <input type="date" 
                                           name="expected_leaving_date" 
                                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                </div>

                                <!-- Remarks -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Remarks
                                    </label>
                                    <textarea name="remarks" 
                                              rows="2"
                                              class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                              placeholder="Any additional notes..."></textarea>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                                <button type="button" 
                                        @click="openOrderModal = false"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                    Create Order
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
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Orders</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="total-orders">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">In Progress</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]" id="orders-processing">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Completed</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]" id="orders-completed">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Weight</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="total-weight">0 t</p>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200/70 bg-blue-50/70 p-4">
            <p class="text-sm font-semibold text-blue-900">Workflow reminder</p>
            <p class="mt-1 text-sm text-blue-800">
                After the creator submits an order, the next user should first move it from <span class="font-semibold">Created</span> to <span class="font-semibold">Processing</span>.
            </p>
        </div>

        <!-- Search and Redesigned Tabs -->
        <div class="space-y-4">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       id="order-search"
                       placeholder="Search by order number, cargo type, customer..."
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
            </div>

            <!-- Well-Arranged Status Tabs -->
            <div class="flex flex-wrap items-center gap-2" id="order-status-tabs">
                <button type="button" class="status-tab" data-status="">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">All Orders</span>
                    </span>
                    <span id="tab-count-all" class="status-tab-count">0</span>
                </button>
                
                <button type="button" class="status-tab" data-status="created">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Created</span>
                    </span>
                    <span id="tab-count-created" class="status-tab-count">0</span>
                </button>
                
                <button type="button" class="status-tab" data-status="processing">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Processing</span>
                    </span>
                    <span id="tab-count-processing" class="status-tab-count">0</span>
                </button>
                
                <button type="button" class="status-tab" data-status="assigned">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Assigned</span>
                    </span>
                    <span id="tab-count-assigned" class="status-tab-count">0</span>
                </button>

                <button type="button" class="status-tab" data-status="transportation">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Transportation</span>
                    </span>
                    <span id="tab-count-transportation" class="status-tab-count">0</span>
                </button>

                <button type="button" class="status-tab" data-status="incomplete">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Incomplete</span>
                    </span>
                    <span id="tab-count-incomplete" class="status-tab-count">0</span>
                </button>

                <button type="button" class="status-tab" data-status="completed">
                    <span class="status-tab-content">
                        <span class="status-tab-dot"></span>
                        <span class="status-tab-text">Completed</span>
                    </span>
                    <span id="tab-count-completed" class="status-tab-count">0</span>
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Order Details</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Trip</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Created Date</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Distance</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet Management</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="order-table" class="divide-y divide-slate-100 bg-white">
                        <!-- Data will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Loading State -->
            <div id="loading-state" class="py-12 text-center hidden">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 mb-4">
                    <svg class="h-5 w-5 text-slate-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-500">Loading orders...</p>
            </div>
            
            <!-- Empty State -->
            <div id="empty-state" class="py-12 text-center hidden">
                <div class="flex flex-col items-center justify-center">
                    <div class="rounded-full bg-slate-100 p-3 mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-slate-900 mb-1">No orders found</h3>
                    <p class="text-sm text-slate-500 mb-4">Get started by creating your first order</p>
                    @can('orders.create')
                        <button 
                            x-data="{}"
                            @click="$dispatch('open-order-modal')"
                            class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create New Order
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500" id="pagination-info"></div>
            <div class="flex items-center gap-2" id="pagination-controls"></div>
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
        
        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 600;
        }

        .status-badge.created {
            background-color: #fef3c7;
            color: #b45309;
        }

        .status-badge.processing {
            background-color: rgba(42, 157, 143, 0.14);
            color: #0f766e;
        }

        .status-badge.assigned {
            background-color: rgba(27, 59, 134, 0.12);
            color: #1e3a8a;
        }

        .status-badge.transportation {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .status-badge.incomplete {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .status-badge.completed {
            background-color: rgba(108, 182, 63, 0.16);
            color: #3f7f1f;
        }

        /* Clean Status Tabs (white background, color on text only) */
        .status-tab {
            @apply inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            min-width: fit-content;
        }

        .status-tab:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .status-tab.active {
            background-color: #ffffff;
            box-shadow: inset 0 0 0 1px currentColor, 0 6px 12px rgba(15, 23, 42, 0.08);
        }

        .status-tab-dot {
            @apply h-2 w-2 rounded-full;
            background: currentColor;
            opacity: 0.9;
        }

        .status-tab.active .status-tab-dot {
            opacity: 1;
        }

        .status-tab-count {
            @apply inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold min-w-[1.5rem];
            background-color: #f1f5f9;
            color: #334155;
            margin-left: 0.25rem;
        }

        .status-tab.active .status-tab-count {
            background-color: color-mix(in srgb, currentColor 14%, white);
            color: currentColor;
        }

        /* Status-specific font colors */
        .status-tab[data-status=""] {
            color: #334155;
        }

        .status-tab[data-status="created"] {
            color: #b45309;
        }

        .status-tab[data-status="processing"] {
            color: #0f766e;
        }

        .status-tab[data-status="assigned"] {
            color: #1d4ed8;
        }

        .status-tab[data-status="transportation"] {
            color: #4338ca;
        }

        .status-tab[data-status="incomplete"] {
            color: #b91c1c;
        }

        .status-tab[data-status="completed"] {
            color: #15803d;
        }

        .status-tab[data-status=""] .status-tab-count {
            background-color: #e2e8f0;
            color: #334155;
        }

        .status-tab[data-status="created"] .status-tab-count {
            background-color: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .status-tab[data-status="processing"] .status-tab-count {
            background-color: rgba(20, 184, 166, 0.14);
            color: #0f766e;
        }

        .status-tab[data-status="assigned"] .status-tab-count {
            background-color: rgba(59, 130, 246, 0.14);
            color: #1d4ed8;
        }

        .status-tab[data-status="transportation"] .status-tab-count {
            background-color: rgba(99, 102, 241, 0.14);
            color: #4338ca;
        }

        .status-tab[data-status="incomplete"] .status-tab-count {
            background-color: rgba(239, 68, 68, 0.14);
            color: #b91c1c;
        }

        .status-tab[data-status="completed"] .status-tab-count {
            background-color: rgba(34, 197, 94, 0.14);
            color: #15803d;
        }

        .processing-action-btn {
            @apply inline-flex items-center gap-1.5 rounded-lg border px-3.5 py-1.5 text-xs font-semibold transition-all;
            border-color: #99f6e4;
            background: #ffffff;
            color: #0f766e;
            box-shadow: 0 2px 6px rgba(15, 118, 110, 0.08);
        }

        .processing-action-btn:hover {
            border-color: #2dd4bf;
            background: #f0fdfa;
            box-shadow: 0 6px 14px rgba(15, 118, 110, 0.14);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .status-tab {
                @apply px-2.5 py-1.5 text-xs;
            }
            
            .status-tab-count {
                @apply px-1.5 py-0.5 text-[10px];
            }
        }
    </style>

    @push('scripts')
    <script>
        let currentPage = 0;
        let currentStatus = '';
        const pageSize = 10;
        const csrfToken = '{{ csrf_token() }}';

        async function loadOrders(resetPage = true) {
            if (resetPage) currentPage = 0;
            
            const search = encodeURIComponent(document.getElementById('order-search').value || '');
            const status = encodeURIComponent(currentStatus);
            
            // Show loading state
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('order-table').innerHTML = '';
            document.getElementById('empty-state').classList.add('hidden');
            
            try {
                const response = await fetch(`{{ route('orders.index') }}?skip=${currentPage * pageSize}&take=${pageSize}&search=${search}&status=${status}`, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await response.json();
                
                const table = document.getElementById('order-table');
                table.innerHTML = '';

                // Update stats
                updateStats(payload.stats || payload.meta || {});
                setActiveStatusTab(currentStatus);
                
                if (!payload.data?.length) {
                    document.getElementById('loading-state').classList.add('hidden');
                    document.getElementById('empty-state').classList.remove('hidden');
                    document.getElementById('pagination-info').innerHTML = '';
                    document.getElementById('pagination-controls').innerHTML = '';
                    return;
                }

                payload.data.forEach((order) => {
                    const statusClass = order.status?.toLowerCase() || 'created';
                    const statusText = order.status?.charAt(0).toUpperCase() + order.status?.slice(1) || 'Created';
                    const isUnattended = order.status === 'created';
                    
                    table.insertAdjacentHTML('beforeend', `
                        <tr class="hover:bg-slate-50/80 transition-colors duration-200 group ${isUnattended ? 'bg-amber-50/50' : ''}">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-[var(--nmis-primary)]">
                                            ${order.order_number?.substring(0, 2).toUpperCase() || 'OR'}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm ${isUnattended ? 'font-bold text-amber-900' : 'font-medium text-slate-900'}">
                                            ${order.order_number || 'N/A'}
                                            ${isUnattended ? '<span class="ml-2 rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Unattended</span>' : ''}
                                        </div>
                                        <div class="text-xs text-slate-500">${order.cargo_type || 'No cargo type'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-slate-900">${order.trip_number || '-'}</div>
                                <div class="text-xs text-slate-500">${order.trip_route || ''}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-slate-900">${order.customer || '-'}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="status-badge ${statusClass}">
                                    ${statusText}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-slate-700">${order.created_at_label || '-'}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-slate-900">${order.can_view_distance ? (order.distance_km ? order.distance_km + ' km' : 'Not calculated') : 'Restricted'}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                ${order.can_manage_legs ? 
                                    `<a href="${order.legs_url}" class="inline-flex items-center gap-1 text-sm text-[var(--nmis-primary)] hover:text-[var(--nmis-secondary)] transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Manage Fleet
                                    </a>` : 
                                    (order.status === 'incomplete'
                                        ? `<span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Trip Closed Incomplete</span>`
                                        : (order.can_end_order
                                        ? `<a href="${order.end_order_url}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-700 hover:text-indigo-800 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                End Order
                                           </a>`
                                        : '<span class="text-sm text-slate-400">-</span>'))
                                }
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                    ${order.can_start_processing ? `
                                        <form method="POST" action="${order.status_update_url}" class="inline">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="status" value="processing">
                                            <button type="submit" class="processing-action-btn" title="Move order to Processing">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Start Processing
                                            </button>
                                        </form>
                                    ` : ''}
                                    <a href="${order.show_url}" 
                                       class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-[var(--nmis-primary)] hover:text-white transition-all" 
                                       title="View Details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                // Update pagination
                updatePagination(payload.meta || payload);
                
            } catch (error) {
                console.error('Failed to load orders:', error);
                document.getElementById('order-table').innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-sm text-rose-600">Error loading orders. Please try again.</div>
                        </td>
                    </tr>
                `;
            } finally {
                document.getElementById('loading-state').classList.add('hidden');
            }
        }

        function updateStats(stats) {
            document.getElementById('total-orders').textContent = stats.total || 0;
            document.getElementById('orders-processing').textContent = stats.processing || 0;
            document.getElementById('orders-completed').textContent = stats.completed || 0;
            document.getElementById('total-weight').textContent = (stats.total_weight || 0) + ' t';
            updateTabCounts(stats);
        }

        function updateTabCounts(stats) {
            document.getElementById('tab-count-all').textContent = stats.total || 0;
            document.getElementById('tab-count-created').textContent = stats.created || 0;
            document.getElementById('tab-count-processing').textContent = stats.processing || 0;
            document.getElementById('tab-count-assigned').textContent = stats.assigned || 0;
            document.getElementById('tab-count-transportation').textContent = stats.transportation || 0;
            document.getElementById('tab-count-incomplete').textContent = stats.incomplete || 0;
            document.getElementById('tab-count-completed').textContent = stats.completed || 0;
        }

        function setActiveStatusTab(status) {
            document.querySelectorAll('.status-tab').forEach((tab) => {
                const isActive = tab.dataset.status === status;
                tab.classList.toggle('active', isActive);
            });
        }

        function updatePagination(meta) {
            const total = meta.total || 0;
            const lastPage = Math.ceil(total / pageSize) - 1;

            if (total === 0) {
                document.getElementById('pagination-info').innerHTML = 'No records found';
                document.getElementById('pagination-controls').innerHTML = '';
                return;
            }
            
            document.getElementById('pagination-info').innerHTML = 
                `Showing ${currentPage * pageSize + 1} to ${Math.min((currentPage + 1) * pageSize, total)} of ${total} orders`;

            let controls = '';
            
            // Previous button
            controls += `
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage === 0 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage === 0 ? 'disabled' : `onclick="changePage(${currentPage - 1})"`}>
                    Previous
                </button>
            `;
            
            // Next button
            controls += `
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage >= lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage >= lastPage ? 'disabled' : `onclick="changePage(${currentPage + 1})"`}>
                    Next
                </button>
            `;
            
            document.getElementById('pagination-controls').innerHTML = controls;
        }

        function changePage(page) {
            currentPage = page;
            loadOrders(false);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setActiveStatusTab(currentStatus);
            loadOrders();
            
            document.querySelectorAll('.status-tab').forEach((tab) => {
                tab.addEventListener('click', () => {
                    currentStatus = tab.dataset.status || '';
                    loadOrders(true);
                });
            });
            
            // Search on enter key
            document.getElementById('order-search')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadOrders(true);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
