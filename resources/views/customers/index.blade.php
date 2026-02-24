<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Customers</h2>
    </x-slot>

    <div class="space-y-6">
        @can('customers.create')
            <form method="POST" action="{{ route('customers.store') }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2">
                @csrf
                <input name="name" class="rounded-md border-slate-300" placeholder="Customer name" required />
                <input name="contact_person" class="rounded-md border-slate-300" placeholder="Contact person" />
                <input name="phone" class="rounded-md border-slate-300" placeholder="Phone" />
                <input name="email" class="rounded-md border-slate-300" placeholder="Email" type="email" />
                <textarea name="address" class="rounded-md border-slate-300 sm:col-span-2" placeholder="Address" required></textarea>
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Create customer</button>
            </form>
        @endcan

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Contact</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($customers as $customer)
                        <tr>
                            <td class="px-3 py-2">{{ $customer->name }}</td>
                            <td class="px-3 py-2">{{ $customer->contact_person ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $customer->phone ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $customer->address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</x-app-layout>

