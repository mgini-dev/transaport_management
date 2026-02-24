<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Fleet</h2>
    </x-slot>

    <div class="space-y-4">
        @can('fleet.create')
            <form method="POST" action="{{ route('fleet.store') }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2">
                @csrf
                <input name="fleet_code" class="rounded-md border-slate-300" placeholder="Fleet code" required />
                <input name="plate_number" class="rounded-md border-slate-300" placeholder="Plate number" required />
                <input name="capacity_tons" class="rounded-md border-slate-300" placeholder="Capacity (tons)" type="number" step="0.01" required />
                <select name="status" class="rounded-md border-slate-300" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Register fleet</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Plate</th>
                        <th class="px-3 py-2 text-left">Capacity</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($fleets as $fleet)
                        <tr>
                            <td class="px-3 py-2">{{ $fleet->fleet_code }}</td>
                            <td class="px-3 py-2">{{ $fleet->plate_number }}</td>
                            <td class="px-3 py-2">{{ $fleet->capacity_tons }}</td>
                            <td class="px-3 py-2">{{ $fleet->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $fleets->links() }}
    </div>
</x-app-layout>

