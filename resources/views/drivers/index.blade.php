<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Driver Management</h2>
        <p class="mt-1 text-sm text-slate-500">Register, update, and manage driver activation with fleet mapping.</p>
    </x-slot>

    <div class="space-y-6" x-data="driverManager()">
        <form method="GET" class="grid gap-2 sm:grid-cols-3">
            <input name="search" value="{{ request('search') }}" class="rounded-md border-slate-300" placeholder="Search driver/license/mobile..." />
            <select name="active" class="rounded-md border-slate-300">
                <option value="">All statuses</option>
                <option value="1" @selected(request('active') === '1')>Active</option>
                <option value="0" @selected(request('active') === '0')>Inactive</option>
            </select>
            <button class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Apply Filters</button>
        </form>

        @can('drivers.create')
            <form method="POST" action="{{ route('drivers.store') }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2">
                @csrf
                <select name="fleet_id" class="rounded-md border-slate-300">
                    <option value="">No fleet assigned</option>
                    @foreach ($fleets as $fleet)
                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}</option>
                    @endforeach
                </select>
                <input name="name" class="rounded-md border-slate-300" placeholder="Driver name" required />
                <input name="license_number" class="rounded-md border-slate-300" placeholder="License number" required />
                <input name="mobile_number" class="rounded-md border-slate-300" placeholder="Mobile number" required />
                <select name="is_active" class="rounded-md border-slate-300 sm:col-span-2">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="rounded-md bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white sm:col-span-2">Register driver</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">License</th>
                        <th class="px-3 py-2 text-left">Mobile</th>
                        <th class="px-3 py-2 text-left">Fleet</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="px-3 py-2">{{ $driver->name }}</td>
                            <td class="px-3 py-2">{{ $driver->license_number }}</td>
                            <td class="px-3 py-2">{{ $driver->mobile_number }}</td>
                            <td class="px-3 py-2">{{ $driver->fleet?->fleet_code ? $driver->fleet->fleet_code.' - '.$driver->fleet->plate_number : '-' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $driver->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $driver->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                @can('drivers.update')
                                    <button type="button"
                                            class="rounded-md bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200"
                                            @click="open({
                                                id: '{{ $driver->encrypted_id }}',
                                                name: @js($driver->name),
                                                license_number: @js($driver->license_number),
                                                mobile_number: @js($driver->mobile_number),
                                                is_active: '{{ $driver->is_active ? 1 : 0 }}',
                                                fleet_id: '{{ $driver->fleet?->encrypted_id }}'
                                            })">
                                        Edit
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-slate-500">No drivers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="show" class="fixed inset-0 z-40 bg-slate-900/50" @click="show = false"></div>
        <div x-show="show" x-transition class="fixed left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl border border-slate-200 bg-white p-5 shadow-2xl">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Edit Driver</h3>
            <form :action="`{{ url('/drivers') }}/${form.id}`" method="POST" class="grid gap-3 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <select name="fleet_id" class="rounded-md border-slate-300 sm:col-span-2" x-model="form.fleet_id">
                    <option value="">No fleet assigned</option>
                    @foreach ($fleets as $fleet)
                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}</option>
                    @endforeach
                </select>
                <input name="name" class="rounded-md border-slate-300" placeholder="Driver name" x-model="form.name" required />
                <input name="license_number" class="rounded-md border-slate-300" placeholder="License number" x-model="form.license_number" required />
                <input name="mobile_number" class="rounded-md border-slate-300" placeholder="Mobile number" x-model="form.mobile_number" required />
                <select name="is_active" class="rounded-md border-slate-300" x-model="form.is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="sm:col-span-2 flex justify-end gap-2">
                    <button type="button" class="rounded-md bg-slate-100 px-3 py-2 text-sm text-slate-700 hover:bg-slate-200" @click="show = false">Cancel</button>
                    <button class="rounded-md bg-[var(--nmis-primary)] px-3 py-2 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)]">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function driverManager() {
                return {
                    show: false,
                    form: { id: '', name: '', license_number: '', mobile_number: '', is_active: '1', fleet_id: '' },
                    open(payload) {
                        this.form = { ...payload };
                        this.show = true;
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
