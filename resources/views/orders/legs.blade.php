<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('orders.index') }}" class="text-slate-400 hover:text-[var(--nmis-primary)] transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h2 class="text-3xl font-bold gradient-text">Order Legs</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Managing legs for order: <span class="font-semibold text-[var(--nmis-primary)]">{{ $order->order_number }}</span></p>
            </div>
            
            <button 
                x-data="{}"
                @click="$dispatch('open-leg-modal')"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Leg
            </button>
        </div>
    </x-slot>

    <div class="space-y-6" 
         x-data="{ openLegModal: false }" 
         @open-leg-modal.window="openLegModal = true">
        
        <!-- Add Leg Modal -->
        <div x-show="openLegModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openLegModal = false"></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="openLegModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    
                    <!-- Modal Header -->
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200/60 bg-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10">
                                <svg class="h-5 w-5 text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Add Fleet Assignment Leg</h3>
                                <p class="text-xs text-slate-500">Assign a fleet and driver to this order leg</p>
                            </div>
                        </div>
                        <button @click="openLegModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form method="POST" action="{{ route('orders.legs.store', $order->encrypted_id) }}" class="p-6">
                        @csrf
                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- Fleet Selection -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Select Fleet <span class="text-rose-500">*</span>
                                </label>
                                <select name="fleet_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all" required>
                                    <option value="">Choose available fleet...</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }} ({{ $fleet->type ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Driver Selection -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Select Driver (Optional)
                                </label>
                                <select name="driver_id" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                                    <option value="">Choose driver...</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->encrypted_id }}">{{ $driver->name }} - {{ $driver->license_number }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Origin Address -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Origin Address <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="origin_address" 
                                          required 
                                          rows="3"
                                          class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                          placeholder="Full origin address"></textarea>
                            </div>

                            <!-- Destination Address -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Destination Address <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="destination_address" 
                                          required 
                                          rows="3"
                                          class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all resize-none"
                                          placeholder="Full destination address"></textarea>
                            </div>

                            <!-- Distance -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Distance (km) - Optional
                                </label>
                                <input type="number" 
                                       name="distance_km" 
                                       step="0.01" 
                                       class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                       placeholder="Enter distance in kilometers">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                            <button type="button" 
                                    @click="openLegModal = false"
                                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Assign Leg
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Legs</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $legs->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Active Legs</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $legs->where('status', 'active')->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Completed Legs</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $legs->where('status', 'completed')->count() }}</p>
            </div>
        </div>

        <!-- Legs List Section -->
        <section class="rounded-xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                            </svg>
                        </span>
                        Assigned Legs
                    </h3>
                    <span class="inline-flex items-center rounded-full bg-[var(--nmis-primary)]/10 px-3 py-1 text-xs font-medium text-[var(--nmis-primary)]">
                        {{ $legs->count() }} {{ Str::plural('Leg', $legs->count()) }}
                    </span>
                </div>
            </div>

            @if($legs->isEmpty())
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-900 mb-1">No legs assigned yet</h3>
                        <p class="text-sm text-slate-500 mb-4">Get started by adding your first leg</p>
                        <button 
                            x-data="{}"
                            @click="$dispatch('open-leg-modal')"
                            class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add New Leg
                        </button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gradient-to-r from-slate-50 to-white">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sequence</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Driver</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Route</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($legs as $leg)
                                <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-[var(--nmis-primary)]">#{{ $leg->leg_sequence }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $leg->fleet?->fleet_code ?? 'N/A' }}</div>
                                        <div class="text-xs text-slate-500">{{ $leg->fleet?->plate_number ?? 'No fleet' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-slate-900">{{ $leg->driver?->name ?? '-' }}</div>
                                        @if($leg->driver?->license_number)
                                            <div class="text-xs text-slate-500">{{ $leg->driver->license_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 max-w-xs">
                                            <span class="font-medium">{{ Str::limit($leg->origin_address, 30) }}</span>
                                            <svg class="h-3 w-3 inline mx-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                            </svg>
                                            <span class="font-medium">{{ Str::limit($leg->destination_address, 30) }}</span>
                                        </div>
                                        @if($leg->distance_km)
                                            <div class="text-xs text-slate-500 mt-1">{{ $leg->distance_km }} km</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($leg->status === 'active')
                                            <span class="status-badge active">
                                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-[var(--nmis-secondary)] animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="status-badge completed">Completed</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        @if ($leg->status === 'active')
                                            <form method="POST" action="{{ route('orders.legs.complete', $leg->encrypted_id) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to mark this leg as complete?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200 transition-all">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Mark Complete
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Completed
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- Back to Orders -->
        <div class="flex justify-start">
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-[var(--nmis-primary)] transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Orders
            </a>
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
            @apply inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium;
        }
        
        .status-badge.active {
            @apply bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)];
        }
        
        .status-badge.completed {
            @apply bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)];
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Custom scrollbar for modal */
        .overflow-y-auto::-webkit-scrollbar {
            width: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>