<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Edit Fleet {{ $fleet->fleet_code }}</h2>
                <p class="mt-2 text-sm text-slate-500">Update fleet details including trailer number.</p>
            </div>
            <a href="{{ route('fleet.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Fleet
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('fleet.update', $fleet->encrypted_id) }}" class="grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fleet Code</label>
                    <input type="text"
                           name="fleet_code"
                           value="{{ old('fleet_code', $fleet->fleet_code) }}"
                           required
                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Plate Number</label>
                    <input type="text"
                           name="plate_number"
                           value="{{ old('plate_number', $fleet->plate_number) }}"
                           required
                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Trailer Number</label>
                    <input type="text"
                           name="trailer_number"
                           value="{{ old('trailer_number', $fleet->trailer_number) }}"
                           required
                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Capacity (tons)</label>
                    <input type="number"
                           step="0.01"
                           name="capacity_tons"
                           value="{{ old('capacity_tons', $fleet->capacity_tons) }}"
                           required
                           class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                        <option value="available" {{ old('status', $fleet->status) === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ old('status', $fleet->status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        <option value="maintenance" {{ old('status', $fleet->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3 border-t border-slate-200/70 pt-4">
                    <a href="{{ route('fleet.index') }}"
                       class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl transition-all">
                        Save Changes
                    </button>
                </div>
            </form>
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
