<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-bold gradient-text text-rose-600">Service Alerts</h2>
            <p class="mt-2 text-sm text-slate-500">Vehicles requiring immediate maintenance attention</p>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ 
        showLogModal: false, 
        showDeferModal: false,
        selectedFleetId: '',
        selectedFleetCode: '',
        currentOdo: 0,
        manualCost: 0,
        items: [],
        addItem() {
            this.items.push({ category: 'engine', description: '', cost: 0, lifespan_km: 5000 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        get totalCost() {
            let itemTotal = this.items.reduce((sum, item) => sum + parseFloat(item.cost || 0), 0);
            return itemTotal + parseFloat(this.manualCost || 0);
        }
    }">
        @include('fleet.partials.navigation')

        <!-- Alerts Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($fleets as $fleet)
                @php
                    $status = $fleet->serviceStatus();
                    $colors = [
                        'good' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'approaching' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'overdue' => 'border-rose-200 bg-rose-50 text-rose-700'
                    ];
                    $badgeColors = [
                        'good' => 'bg-emerald-500',
                        'approaching' => 'bg-amber-500',
                        'overdue' => 'bg-rose-500'
                    ];
                @endphp
                <div class="group relative rounded-2xl border p-6 shadow-sm transition-all hover:shadow-md {{ $colors[$status] ?? 'border-slate-200 bg-white' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center font-black text-slate-900 shadow-sm">
                                {{ substr($fleet->fleet_code, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-black">{{ $fleet->fleet_code }}</h3>
                                <p class="text-xs font-medium opacity-70">{{ $fleet->plate_number }}</p>
                            </div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase text-white {{ $badgeColors[$status] }}">
                            {{ $status }}
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4 border-t border-current/10 pt-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase opacity-60">Odometer</p>
                            <p class="text-sm font-black">{{ number_format($fleet->current_odometer, 0) }} km</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase opacity-60">Due At</p>
                            <p class="text-sm font-black text-rose-600">{{ number_format($fleet->next_service_due_km, 0) }} km</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                            <span>Service Progress</span>
                            <span>{{ number_format(abs($fleet->kmsUntilService()), 0) }} km {{ $fleet->kmsUntilService() < 0 ? 'overdue' : 'remaining' }}</span>
                        </div>
                        @php 
                            $percent = min(100, max(0, ($fleet->current_odometer - $fleet->last_service_odometer) / max(1, $fleet->oil_change_interval_km) * 100));
                        @endphp
                        <div class="h-2 w-full overflow-hidden rounded-full bg-current/10">
                            <div class="h-full {{ $badgeColors[$status] }} transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <div class="flex gap-2">
                            <a href="{{ route('fleet.show', $fleet->encrypted_id) }}" 
                               class="flex-1 rounded-xl bg-white px-4 py-2.5 text-center text-xs font-bold text-slate-900 shadow-sm transition-all hover:scale-[1.02]">
                                View Profile
                            </a>
                            <button @click="selectedFleetId = @js($fleet->encrypted_id); selectedFleetCode = @js($fleet->fleet_code); currentOdo = {{ $fleet->current_odometer ?? 0 }}; items = []; manualCost = 0; showLogModal = true"
                                    class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-center text-xs font-bold text-white shadow-sm transition-all hover:scale-[1.02]">
                                Log Service
                            </button>
                        </div>
                        <button @click="selectedFleetId = @js($fleet->encrypted_id); selectedFleetCode = @js($fleet->fleet_code); showDeferModal = true"
                                class="w-full rounded-xl border border-current/20 bg-transparent py-2.5 text-center text-xs font-bold transition-all hover:bg-current/5">
                            Defer / Postpone
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">All Clear!</h3>
                    <p class="mt-2 text-sm text-slate-500">All vehicles are currently within their service intervals.</p>
                </div>
            @endforelse
        </div>

        <!-- Log Maintenance Modal (Dynamic) -->
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
                            <h3 class="text-2xl font-black tracking-tight">Log Service: <span x-text="selectedFleetCode" class="text-sky-300"></span></h3>
                            <p class="mt-1 text-sm text-sky-100/80 font-medium">Feeding technical details for lifecycle tracking</p>
                        </div>
                        <button @click="showLogModal = false" class="rounded-full bg-white/10 p-2 hover:bg-white/20 transition-all">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <form :action="'{{ route('fleet.maintenance.log', ['fleetId' => 'ID']) }}'.replace('ID', selectedFleetId)" method="POST" class="p-8">
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
                                <input type="number" name="odometer_reading" :value="currentOdo" required class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 pl-5 pr-12 py-4 text-lg font-black text-slate-900 focus:border-[var(--nmis-primary)] focus:ring-0 transition-all outline-none">
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

        <!-- Defer Maintenance Modal (Dynamic) -->
        <div x-show="showDeferModal" 
             class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/90 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-cloak>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-lg rounded-3xl bg-white p-0 overflow-hidden shadow-2xl" @click.away="showDeferModal = false">
                <div class="bg-amber-500 p-8 text-white">
                    <h3 class="text-2xl font-black tracking-tight uppercase">Defer Service: <span x-text="selectedFleetCode"></span></h3>
                    <p class="mt-1 text-sm text-amber-100 font-medium">Temporarily scaling maintenance window</p>
                </div>

                <form :action="'{{ route('fleet.defer', ['fleetId' => 'ID']) }}'.replace('ID', selectedFleetId)" method="POST" class="p-8 space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Extra Distance Allowance</label>
                        <div class="relative">
                            <input type="number" name="extra_km" value="500" step="100" required class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 px-5 py-4 text-lg font-black text-slate-900 focus:border-amber-500 focus:ring-0 transition-all outline-none">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 uppercase">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-2">Technical Justification</label>
                        <textarea name="reason" required rows="3" class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 px-5 py-4 text-base font-bold text-slate-700 focus:border-amber-500 focus:ring-0 transition-all outline-none resize-none" placeholder="Explain why this vehicle is safe to continue operation..."></textarea>
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button type="button" @click="showDeferModal = false" class="flex-1 rounded-2xl border-2 border-slate-100 py-4 text-base font-black text-slate-500 hover:bg-slate-50 transition-all">Cancel</button>
                        <button type="submit" class="flex-[2] rounded-2xl bg-amber-500 py-4 text-base font-black text-white shadow-xl shadow-amber-500/20 hover:scale-[1.02] transition-all">POSTPONE SERVICE</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
