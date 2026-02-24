<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Smart Operations Dashboard</h2>
        <p class="mt-1 text-sm text-slate-500">Live logistics visibility for trips, orders, fleet, and fuel workflow.</p>
    </x-slot>

    @php
        $maxTrend = max($charts['trip_trend_values']) ?: 1;
        $statusTotal = array_sum($charts['order_status']);
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Open Trips</p>
                    <span class="rounded-lg bg-[color:rgba(27,59,134,0.12)] p-2 text-[var(--nmis-primary)]">🧭</span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-primary)]">{{ $stats['open_trips'] }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Closed Trips</p>
                    <span class="rounded-lg bg-[color:rgba(42,157,143,0.12)] p-2 text-[var(--nmis-secondary)]">✅</span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $stats['closed_trips'] }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Orders Active</p>
                    <span class="rounded-lg bg-[color:rgba(108,182,63,0.12)] p-2 text-[var(--nmis-accent)]">📦</span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $stats['orders_in_progress'] }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Fuel Pending</p>
                    <span class="rounded-lg bg-[color:rgba(27,59,134,0.12)] p-2 text-[var(--nmis-primary)]">⛽</span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-primary)]">{{ $stats['fuel_pending'] }}</p>
            </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Trip Creation Trend (Last 7 Days)</h3>
                </div>
                <div class="flex h-64 items-end gap-3 rounded-lg bg-slate-50 p-4">
                    @foreach ($charts['trip_trend_values'] as $index => $value)
                        <div class="flex flex-1 flex-col items-center justify-end gap-2">
                            <div class="w-full rounded-md bg-gradient-to-t from-[var(--nmis-primary)] to-[var(--nmis-secondary)]" style="height: {{ max(8, ($value / $maxTrend) * 160) }}px;"></div>
                            <p class="text-xs text-slate-500">{{ $charts['trip_trend_labels'][$index] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-base font-semibold text-slate-900">Order Status Mix</h3>
                <div class="mx-auto mb-4 h-40 w-40 rounded-full" style="background: conic-gradient(
                    #1b3b86 0% {{ $statusTotal ? ($charts['order_status']['created'] / $statusTotal) * 100 : 0 }}%,
                    #2a9d8f {{ $statusTotal ? ($charts['order_status']['created'] / $statusTotal) * 100 : 0 }}% {{ $statusTotal ? (($charts['order_status']['created'] + $charts['order_status']['processing']) / $statusTotal) * 100 : 0 }}%,
                    #6cb63f {{ $statusTotal ? (($charts['order_status']['created'] + $charts['order_status']['processing']) / $statusTotal) * 100 : 0 }}% {{ $statusTotal ? (($charts['order_status']['created'] + $charts['order_status']['processing'] + $charts['order_status']['assigned']) / $statusTotal) * 100 : 0 }}%,
                    #94a3b8 {{ $statusTotal ? (($charts['order_status']['created'] + $charts['order_status']['processing'] + $charts['order_status']['assigned']) / $statusTotal) * 100 : 0 }}% 100%
                );">
                    <div class="m-6 flex h-28 w-28 items-center justify-center rounded-full bg-white text-sm font-semibold text-slate-700">{{ $statusTotal }}</div>
                </div>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center justify-between"><span class="text-slate-600">Created</span><span class="font-semibold text-[var(--nmis-primary)]">{{ $charts['order_status']['created'] }}</span></li>
                    <li class="flex items-center justify-between"><span class="text-slate-600">Processing</span><span class="font-semibold text-[var(--nmis-secondary)]">{{ $charts['order_status']['processing'] }}</span></li>
                    <li class="flex items-center justify-between"><span class="text-slate-600">Assigned</span><span class="font-semibold text-[var(--nmis-accent)]">{{ $charts['order_status']['assigned'] }}</span></li>
                    <li class="flex items-center justify-between"><span class="text-slate-600">Completed</span><span class="font-semibold text-slate-500">{{ $charts['order_status']['completed'] }}</span></li>
                </ul>
            </section>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="font-semibold text-slate-900">Live Notification Feed</h3>
                <button id="refresh-notifications" type="button" class="rounded-md bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200">Refresh</button>
            </div>
            <div id="notification-list" class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    <article class="p-4 text-sm">
                        <p class="font-medium text-slate-900">{{ data_get($notification->data, 'title') }}</p>
                        <p class="text-slate-600">{{ data_get($notification->data, 'message') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </article>
                @empty
                    <p class="p-4 text-sm text-slate-500">No notifications yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.getElementById('refresh-notifications')?.addEventListener('click', async () => {
                const response = await fetch('{{ route('notifications.index') }}?skip=0&take=10', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const payload = await response.json();
                const holder = document.getElementById('notification-list');
                holder.innerHTML = '';

                if (!payload.data?.length) {
                    holder.innerHTML = '<p class="p-4 text-sm text-slate-500">No notifications yet.</p>';
                    return;
                }

                payload.data.forEach((notification) => {
                    holder.insertAdjacentHTML('beforeend', `
                        <article class="p-4 text-sm">
                            <p class="font-medium text-slate-900">${notification.data?.title ?? ''}</p>
                            <p class="text-slate-600">${notification.data?.message ?? ''}</p>
                        </article>
                    `);
                });
            });

            if (window.Echo) {
                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                    .notification((notification) => {
                        const holder = document.getElementById('notification-list');
                        holder.insertAdjacentHTML('afterbegin', `
                            <article class="p-4 text-sm">
                                <p class="font-medium text-slate-900">${notification.title ?? ''}</p>
                                <p class="text-slate-600">${notification.message ?? ''}</p>
                            </article>
                        `);
                    });
            }
        </script>
    @endpush
</x-app-layout>
