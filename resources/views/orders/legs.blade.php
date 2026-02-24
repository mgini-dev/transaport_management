<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Order Legs - {{ $order->order_number }}</h2>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-lg border border-slate-200 p-4">
            <h3 class="mb-3 font-semibold">Add Fleet Assignment Leg</h3>
            <form method="POST" action="{{ route('orders.legs.store', $order->encrypted_id) }}" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <select name="fleet_id" class="rounded-md border-slate-300" required>
                    <option value="">Select available fleet</option>
                    @foreach ($fleets as $fleet)
                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}</option>
                    @endforeach
                </select>
                <select name="driver_id" class="rounded-md border-slate-300">
                    <option value="">Select driver (optional)</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->encrypted_id }}">{{ $driver->name }} ({{ $driver->license_number }})</option>
                    @endforeach
                </select>
                <textarea name="origin_address" class="rounded-md border-slate-300" placeholder="Origin address" required></textarea>
                <textarea name="destination_address" class="rounded-md border-slate-300" placeholder="Destination address" required></textarea>
                <input name="distance_km" class="rounded-md border-slate-300 sm:col-span-2" placeholder="Distance km (optional)" type="number" step="0.01" />
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Assign leg</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Seq</th>
                        <th class="px-3 py-2 text-left">Fleet</th>
                        <th class="px-3 py-2 text-left">Driver</th>
                        <th class="px-3 py-2 text-left">Route</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($legs as $leg)
                        <tr>
                            <td class="px-3 py-2">{{ $leg->leg_sequence }}</td>
                            <td class="px-3 py-2">{{ $leg->fleet?->fleet_code }} - {{ $leg->fleet?->plate_number }}</td>
                            <td class="px-3 py-2">{{ $leg->driver?->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $leg->origin_address }} -> {{ $leg->destination_address }}</td>
                            <td class="px-3 py-2">{{ $leg->status }}</td>
                            <td class="px-3 py-2">
                                @if ($leg->status === 'active')
                                    <form method="POST" action="{{ route('orders.legs.complete', $leg->encrypted_id) }}">
                                        @csrf
                                        <button class="text-emerald-700 hover:text-emerald-800">Mark complete</button>
                                    </form>
                                @else
                                    <span class="text-slate-500">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
</x-app-layout>
