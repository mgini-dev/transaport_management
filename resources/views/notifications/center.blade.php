<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Central Notification Center</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="POST" action="{{ route('notifications.read_all') }}">
            @csrf
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Mark all as read</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Title</th>
                        <th class="px-3 py-2 text-left">Message</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        <tr class="{{ $notification->read_at ? 'bg-white' : 'bg-sky-50' }}">
                            <td class="px-3 py-2 font-medium">{{ data_get($notification->data, 'title', '-') }}</td>
                            <td class="px-3 py-2">{{ data_get($notification->data, 'message', '-') }}</td>
                            <td class="px-3 py-2">{{ data_get($notification->data, 'type', '-') }}</td>
                            <td class="px-3 py-2">{{ $notification->created_at }}</td>
                            <td class="px-3 py-2">
                                @if ($notification->read_at)
                                    <span class="text-slate-500">Read</span>
                                @else
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button class="text-sky-700 hover:text-sky-800">Mark read</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $notifications->links() }}
    </div>
</x-app-layout>
