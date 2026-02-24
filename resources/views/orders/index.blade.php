<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Orders</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-2 sm:grid-cols-3">
            <input id="order-search" class="rounded-md border-slate-300" placeholder="Search order/cargo..." />
            <select id="order-status" class="rounded-md border-slate-300">
                <option value="">All statuses</option>
                <option value="created">Created</option>
                <option value="processing">Processing</option>
                <option value="assigned">Assigned</option>
                <option value="completed">Completed</option>
            </select>
            <button type="button" id="order-filter-btn" class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Apply Filters</button>
        </div>

        @can('orders.create')
            <form method="POST" action="{{ route('orders.store') }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2">
                @csrf
                <select name="trip_id" class="rounded-md border-slate-300" required>
                    <option value="">Select open trip</option>
                    @foreach ($trips as $trip)
                        <option value="{{ $trip->encrypted_id }}">{{ $trip->trip_number }}</option>
                    @endforeach
                </select>
                <select name="customer_id" class="rounded-md border-slate-300" required>
                    <option value="">Select customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->encrypted_id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <input name="cargo_type" class="rounded-md border-slate-300" placeholder="Cargo type" required />
                <input name="cargo_description" class="rounded-md border-slate-300" placeholder="Cargo description" />
                <input name="weight_tons" class="rounded-md border-slate-300" placeholder="Weight tons" type="number" step="0.01" required />
                <input name="agreed_price" class="rounded-md border-slate-300" placeholder="Agreed price" type="number" step="0.01" required />
                <textarea name="origin_address" class="rounded-md border-slate-300" placeholder="Origin address" required></textarea>
                <textarea name="destination_address" class="rounded-md border-slate-300" placeholder="Destination address" required></textarea>
                <input name="expected_loading_date" class="rounded-md border-slate-300" type="date" />
                <input name="expected_leaving_date" class="rounded-md border-slate-300" type="date" />
                <textarea name="remarks" class="rounded-md border-slate-300 sm:col-span-2" placeholder="Remarks"></textarea>
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Create order</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Order</th>
                        <th class="px-3 py-2 text-left">Trip</th>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Distance (km)</th>
                        <th class="px-3 py-2 text-left">Legs</th>
                    </tr>
                </thead>
                <tbody id="order-table" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            async function loadOrders() {
                const search = encodeURIComponent(document.getElementById('order-search').value || '');
                const status = encodeURIComponent(document.getElementById('order-status').value || '');
                const response = await fetch(`{{ route('orders.index') }}?skip=0&take=50&search=${search}&status=${status}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const payload = await response.json();
                const table = document.getElementById('order-table');
                table.innerHTML = '';

                payload.data.forEach((order) => {
                    table.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="px-3 py-2">${order.order_number}</td>
                            <td class="px-3 py-2">${order.trip_number ?? '-'}</td>
                            <td class="px-3 py-2">${order.customer ?? '-'}</td>
                            <td class="px-3 py-2">${order.status}</td>
                            <td class="px-3 py-2">${order.distance_km ?? 'hidden'}</td>
                            <td class="px-3 py-2">${order.can_manage_legs ? `<a class="text-sky-700 hover:text-sky-800" href="${order.legs_url}">Manage legs</a>` : '-'}</td>
                        </tr>
                    `);
                });
            }
            loadOrders();
            document.getElementById('order-filter-btn')?.addEventListener('click', loadOrders);
        </script>
    @endpush
</x-app-layout>
