<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Operational Dashboard</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Permission-based dashboard view. Each user sees only allowed modules, stats, and visualizations.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                    Preset: {{ strtoupper($preset) }}
                </span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                    Permissions: {{ $roleInfo['permissions_total'] }}
                </span>
                @foreach($roleInfo['roles'] as $roleName)
                    <span class="inline-flex items-center rounded-full border border-[var(--nmis-primary)]/20 bg-[var(--nmis-primary)]/10 px-3 py-1.5 text-xs font-semibold text-[var(--nmis-primary)]">
                        {{ $roleName }}
                    </span>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">KPI Overview</h3>
            </div>
            @if(empty($statsCards))
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500">
                    No dashboard KPI cards are available for your current permissions.
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
                    @foreach($statsCards as $card)
                        @php
                            $toneClasses = match($card['tone']) {
                                'primary' => 'text-[var(--nmis-primary)] bg-[var(--nmis-primary)]/10 border-[var(--nmis-primary)]/20',
                                'secondary' => 'text-[var(--nmis-secondary)] bg-[var(--nmis-secondary)]/10 border-[var(--nmis-secondary)]/20',
                                'accent' => 'text-[var(--nmis-accent)] bg-[var(--nmis-accent)]/10 border-[var(--nmis-accent)]/20',
                                'warning' => 'text-amber-700 bg-amber-100 border-amber-200',
                                default => 'text-slate-700 bg-slate-100 border-slate-200',
                            };
                        @endphp
                        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border {{ $toneClasses }}">
                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                </span>
                            </div>
                            <p class="mt-3 text-3xl font-bold text-slate-900">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Allowed Modules</h3>
                <span class="text-xs text-slate-400">{{ count($quickLinks) }} modules</span>
            </div>
            @if(empty($quickLinks))
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500">
                    No module access is configured for this account.
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($quickLinks as $link)
                        <a href="{{ $link['route'] ?: '#' }}"
                           class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-[var(--nmis-primary)]/30 hover:shadow-lg">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-base font-semibold text-slate-900">{{ $link['label'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $link['description'] }}</p>
                                </div>
                                <svg class="h-5 w-5 text-slate-400 transition-transform group-hover:translate-x-0.5 group-hover:text-[var(--nmis-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="mt-4 border-t border-slate-100 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ $link['metric'] }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            @if($widgets['trip_trend'])
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Trips Created (Last 7 Days)</h3>
                    <div class="mt-4 h-64">
                        <canvas id="tripTrendChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['order_status'])
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Order Status Distribution</h3>
                    <div class="mt-4 h-64">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['fuel_spend'])
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Approved Fuel Spend (Last 6 Months)</h3>
                    <div class="mt-4 h-64">
                        <canvas id="fuelSpendChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['approval_pipeline'])
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Fuel Approval Pipeline</h3>
                    <div class="mt-4 h-64">
                        <canvas id="approvalPipelineChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['users_by_role'])
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm xl:col-span-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Users By Role</h3>
                    <div class="mt-4 h-72">
                        <canvas id="usersByRoleChart"></canvas>
                    </div>
                </div>
            @endif
        </section>

        @if($adminOverview)
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Super Admin Overview</h3>
                    <span class="text-xs font-semibold text-[var(--nmis-primary)]">Full System Scope</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-6">
                    @foreach($adminOverview['totals'] as $label => $value)
                        <div class="rounded-xl border border-slate-200/70 bg-white px-4 py-3 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format((int) $value) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm">
                    <div class="border-b border-slate-200/70 px-5 py-3">
                        <h4 class="text-sm font-semibold text-slate-800">Recent Audit Activity</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($adminOverview['recent_logs'] as $log)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-700">{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $log->user?->name ?? 'System' }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $log->action }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $log->ip_address ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No audit records available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-200/70 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200/70 px-5 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Latest Notifications</h3>
                <button id="refresh-notifications"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
            <div id="notification-list" class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    <div class="px-5 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ data_get($notification->data, 'title', 'Notification') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ data_get($notification->data, 'message', '-') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-500">
                        No notifications available.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        const dashboardColors = {
            primary: '#1b3b86',
            secondary: '#2a9d8f',
            accent: '#6cb63f',
            warning: '#f59e0b',
            slate: '#64748b'
        };

        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.color = '#64748b';

        document.addEventListener('DOMContentLoaded', function () {
            const tripCanvas = document.getElementById('tripTrendChart');
            if (tripCanvas) {
                new Chart(tripCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($charts['trip_trend_labels']),
                        datasets: [{
                            data: @json($charts['trip_trend_values']),
                            borderColor: dashboardColors.primary,
                            backgroundColor: 'rgba(27,59,134,0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#e2e8f0' }, border: { display: false } },
                            x: { grid: { display: false }, border: { display: false } },
                        }
                    }
                });
            }

            const orderCanvas = document.getElementById('orderStatusChart');
            if (orderCanvas) {
                new Chart(orderCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($charts['order_status_labels']),
                        datasets: [{
                            data: @json($charts['order_status_values']),
                            backgroundColor: [
                                'rgba(27,59,134,0.85)',
                                'rgba(42,157,143,0.85)',
                                'rgba(108,182,63,0.85)',
                                'rgba(245,158,11,0.85)',
                                'rgba(239,68,68,0.85)',
                                'rgba(15,23,42,0.75)',
                            ],
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#e2e8f0' }, border: { display: false } },
                            x: { grid: { display: false }, border: { display: false } },
                        }
                    }
                });
            }

            const fuelCanvas = document.getElementById('fuelSpendChart');
            if (fuelCanvas) {
                new Chart(fuelCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($charts['fuel_spend_labels']),
                        datasets: [{
                            data: @json($charts['fuel_spend_values']),
                            backgroundColor: 'rgba(42,157,143,0.82)',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#e2e8f0' }, border: { display: false } },
                            x: { grid: { display: false }, border: { display: false } },
                        }
                    }
                });
            }

            const approvalCanvas = document.getElementById('approvalPipelineChart');
            if (approvalCanvas) {
                new Chart(approvalCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($charts['approval_pipeline_labels']),
                        datasets: [{
                            data: @json($charts['approval_pipeline_values']),
                            backgroundColor: [
                                'rgba(27,59,134,0.85)',
                                'rgba(42,157,143,0.85)',
                                'rgba(108,182,63,0.85)',
                                'rgba(239,68,68,0.85)',
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
                        }
                    }
                });
            }

            const usersByRoleCanvas = document.getElementById('usersByRoleChart');
            if (usersByRoleCanvas) {
                new Chart(usersByRoleCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($charts['users_by_role_labels']),
                        datasets: [{
                            data: @json($charts['users_by_role_values']),
                            backgroundColor: 'rgba(27,59,134,0.82)',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#e2e8f0' }, border: { display: false } },
                            y: { grid: { display: false }, border: { display: false } },
                        }
                    }
                });
            }
        });

        document.getElementById('refresh-notifications')?.addEventListener('click', async function () {
            const button = this;
            button.disabled = true;

            try {
                const response = await fetch('{{ route('notifications.index') }}?skip=0&take=5', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                const holder = document.getElementById('notification-list');
                const items = Array.isArray(payload.data) ? payload.data : [];

                if (items.length === 0) {
                    holder.innerHTML = '<div class="px-5 py-8 text-center text-sm text-slate-500">No notifications available.</div>';
                    return;
                }

                holder.innerHTML = items.map(function (item) {
                    const title = (item.data && item.data.title) ? item.data.title : 'Notification';
                    const message = (item.data && item.data.message) ? item.data.message : '-';
                    const createdAt = item.created_at ? item.created_at : 'Just now';

                    return `
                        <div class="px-5 py-4">
                            <p class="text-sm font-semibold text-slate-900">${title}</p>
                            <p class="mt-1 text-sm text-slate-600">${message}</p>
                            <p class="mt-1 text-xs text-slate-400">${createdAt}</p>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Failed to refresh notifications:', error);
            } finally {
                button.disabled = false;
            }
        });
    </script>
    @endpush
</x-app-layout>

