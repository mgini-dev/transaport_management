<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Audit Logs (Admin Only)</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2">
            <input name="action" value="{{ request('action') }}" class="w-full rounded-md border-slate-300" placeholder="Filter by action (e.g. order.created)" />
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm text-white">Filter</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">User</th>
                        <th class="px-3 py-2 text-left">Action</th>
                        <th class="px-3 py-2 text-left">Context</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-3 py-2">{{ $log->created_at }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $log->action }}</td>
                            <td class="px-3 py-2">{{ json_encode($log->context) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</x-app-layout>
