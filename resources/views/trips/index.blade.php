<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Trip Management</h2>
    </x-slot>

    <div class="space-y-4">
        @can('trips.create')
            <form method="POST" action="{{ route('trips.store') }}">
                @csrf
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create New Trip</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Trip Number</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="trip-table" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            async function loadTrips() {
                const response = await fetch('{{ route('trips.index') }}?skip=0&take=50', { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const payload = await response.json();
                const table = document.getElementById('trip-table');
                table.innerHTML = '';

                payload.data.forEach((trip) => {
                    table.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="px-3 py-2">${trip.trip_number}</td>
                            <td class="px-3 py-2">${trip.status}</td>
                            <td class="px-3 py-2">${trip.created_at}</td>
                            <td class="px-3 py-2">
                                ${trip.status === 'open' ? `<form method="POST" action="/trips/${trip.id}/close">@csrf<button class="text-rose-600">Close</button></form>` : '-'}
                            </td>
                        </tr>
                    `);
                });
            }
            loadTrips();
        </script>
    @endpush
</x-app-layout>

