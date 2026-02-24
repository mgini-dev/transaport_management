<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Driver Management</h2>
    </x-slot>

    <div class="space-y-6">
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="px-3 py-2">{{ $driver->name }}</td>
                            <td class="px-3 py-2">{{ $driver->license_number }}</td>
                            <td class="px-3 py-2">{{ $driver->mobile_number }}</td>
                            <td class="px-3 py-2">{{ $driver->fleet?->fleet_code ? $driver->fleet->fleet_code.' - '.$driver->fleet->plate_number : '-' }}</td>
                            <td class="px-3 py-2">{{ $driver->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-slate-500">No drivers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
