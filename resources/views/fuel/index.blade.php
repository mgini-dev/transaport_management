<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Fuel Requisitions</h2>
    </x-slot>

    <div class="space-y-6">
        @can('fuel.create')
            <form method="POST" action="{{ route('fuel.store') }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2">
                @csrf
                <select name="order_id" class="rounded-md border-slate-300" required>
                    <option value="">Order</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->encrypted_id }}">{{ $order->order_number }}</option>
                    @endforeach
                </select>
                <select name="fleet_id" class="rounded-md border-slate-300" required>
                    <option value="">Fleet</option>
                    @foreach ($fleets as $fleet)
                        <option value="{{ $fleet->encrypted_id }}">{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}</option>
                    @endforeach
                </select>
                <input name="fuel_station" class="rounded-md border-slate-300" placeholder="Fuel station" required />
                <input name="additional_litres" class="rounded-md border-slate-300" placeholder="Additional litres" type="number" step="0.01" required />
                <input name="fuel_price" class="rounded-md border-slate-300" placeholder="Fuel price" type="number" step="0.01" required />
                <input name="discount" class="rounded-md border-slate-300" placeholder="Discount" type="number" step="0.01" value="0" />
                <input name="payment_channel" class="rounded-md border-slate-300" placeholder="Payment channel" required />
                <input name="payment_account" class="rounded-md border-slate-300" placeholder="Account / phone number" required />
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Submit requisition</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Order</th>
                        <th class="px-3 py-2 text-left">Fleet</th>
                        <th class="px-3 py-2 text-left">Amount</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Timeline</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($requisitions as $item)
                        <tr>
                            <td class="px-3 py-2">#{{ $item->id }}</td>
                            <td class="px-3 py-2">{{ $item->order?->order_number }}</td>
                            <td class="px-3 py-2">{{ $item->fleet?->fleet_code }}</td>
                            <td class="px-3 py-2">{{ number_format($item->total_amount, 2) }}</td>
                            <td class="px-3 py-2">{{ $item->status }}</td>
                            <td class="px-3 py-2 text-xs text-slate-600">
                                <p>Requested: {{ $item->requester?->name }}</p>
                                <p>Supervisor: {{ $item->supervisor?->name ?? '-' }}</p>
                                <p>Accountant: {{ $item->accountant?->name ?? '-' }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <div class="space-y-2">
                                    @can('fuel.approve.supervisor')
                                        @if ($item->status === 'submitted')
                                            <form method="POST" action="{{ route('fuel.supervisor.decision', $item->encrypted_id) }}" class="flex gap-2">
                                                @csrf
                                                <input type="hidden" name="approved" value="1" />
                                                <input name="remarks" class="w-full rounded-md border-slate-300 text-xs" placeholder="Supervisor remarks" />
                                                <button class="text-xs text-emerald-700">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('fuel.supervisor.decision', $item->encrypted_id) }}" class="flex gap-2">
                                                @csrf
                                                <input type="hidden" name="approved" value="0" />
                                                <input name="remarks" class="w-full rounded-md border-slate-300 text-xs" placeholder="Reason for reject" />
                                                <button class="text-xs text-rose-700">Reject</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('fuel.approve.accounting')
                                        @if ($item->status === 'supervisor_approved')
                                            <form method="POST" action="{{ route('fuel.accountant.decision', $item->encrypted_id) }}" class="flex gap-2">
                                                @csrf
                                                <input type="hidden" name="approved" value="1" />
                                                <input name="remarks" class="w-full rounded-md border-slate-300 text-xs" placeholder="Accounting remarks" />
                                                <button class="text-xs text-emerald-700">Finalize</button>
                                            </form>
                                            <form method="POST" action="{{ route('fuel.accountant.decision', $item->encrypted_id) }}" class="flex gap-2">
                                                @csrf
                                                <input type="hidden" name="approved" value="0" />
                                                <input name="remarks" class="w-full rounded-md border-slate-300 text-xs" placeholder="Reason for reject" />
                                                <button class="text-xs text-rose-700">Reject</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $requisitions->links() }}
    </div>
</x-app-layout>
