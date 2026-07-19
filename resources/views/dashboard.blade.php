<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Dashboard</h2>

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

            @if(empty($statsCards))
                <div class="rounded-3xl border border-slate-200 bg-white/50 backdrop-blur-md p-8 text-center text-sm text-slate-500 shadow-sm">
                    No dashboard KPI cards are available for your current permissions.
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @foreach($statsCards as $card)
                        @php
                            $icon = match($card['label']) {
                                'Open Trips' => 'fa-route',
                                'Closed Trips' => 'fa-flag-checkered',
                                'Active Orders' => 'fa-box-open',
                                'Order Completion' => 'fa-percent',
                                'Fuel Pending' => 'fa-gas-pump',
                                'Fuel Approved (Month)' => 'fa-money-bill-trend-up',
                                'Active Drivers' => 'fa-id-card',
                                'Active Employees' => 'fa-users',
                                'System Users' => 'fa-user-gear',
                                'Audit Logs' => 'fa-clipboard-list',
                                default => 'fa-chart-simple',
                            };
                            
                            $toneClasses = match($card['tone']) {
                                'primary' => 'text-blue-600 bg-blue-50 border-blue-100',
                                'secondary' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                'accent' => 'text-indigo-600 bg-indigo-50 border-indigo-100',
                                'warning' => 'text-amber-600 bg-amber-50 border-amber-100',
                                default => 'text-slate-600 bg-slate-50 border-slate-100',
                            };

                            $gradientClasses = match($card['tone']) {
                                'primary' => 'from-blue-600 to-indigo-700',
                                'secondary' => 'from-emerald-500 to-teal-700',
                                'accent' => 'from-violet-500 to-purple-700',
                                'warning' => 'from-amber-400 to-orange-600',
                                default => 'from-slate-500 to-slate-700',
                            };
                        @endphp
                        <div class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-slate-200/50">
                            <div class="relative z-10">
                                <div class="flex items-center justify-between">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $gradientClasses }} text-white shadow-lg shadow-current/20 transition-transform group-hover:scale-110">
                                        <i class="fa-solid {{ $icon }} text-xl"></i>
                                    </div>
                                    <span class="inline-flex items-center rounded-full border {{ $toneClasses }} px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-tight">
                                        {{ $card['tone'] }}
                                    </span>
                                </div>
                                <div class="mt-5">
                                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $card['label'] }}</p>
                                    <h4 class="mt-1 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</h4>
                                    <p class="mt-2 text-[11px] font-medium leading-relaxed text-slate-500 line-clamp-1">
                                        {{ $card['hint'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="absolute -right-4 -bottom-4 h-24 w-24 rounded-full bg-slate-50/50 transition-transform group-hover:scale-150"></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid gap-8 lg:grid-cols-2">
            @if($widgets['trip_trend'])
                <div class="group rounded-3xl border border-slate-200/60 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-slate-200/40">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Trip Performance</h3>
                            <p class="text-xs text-slate-400">Created trips in the last 7 days</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="tripTrendChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['order_status'])
                <div class="group rounded-3xl border border-slate-200/60 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-slate-200/40">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Order Distribution</h3>
                            <p class="text-xs text-slate-400">Current status breakdown</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['fuel_spend'])
                <div class="group rounded-3xl border border-slate-200/60 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-slate-200/40">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Fuel Expenditure</h3>
                            <p class="text-xs text-slate-400">Approved spending (Last 6 Months)</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600">
                            <i class="fa-solid fa-gas-pump"></i>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="fuelSpendChart"></canvas>
                    </div>
                </div>
            @endif

            @if($widgets['approval_pipeline'])
                <div class="group rounded-3xl border border-slate-200/60 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-slate-200/40">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Approval Pipeline</h3>
                            <p class="text-xs text-slate-400">Fuel request status distribution</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-2.5 text-amber-600">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="approvalPipelineChart"></canvas>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-600">Activity Stream</h3>
                    <p class="text-xs text-slate-400">Latest system notifications and updates</p>
                </div>
                <button id="refresh-notifications"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow-md active:scale-95">
                    <i class="fa-solid fa-rotate"></i>
                    Refresh Feed
                </button>
            </div>
            <div id="notification-list" class="divide-y divide-slate-50">
                @forelse($notifications as $notification)
                    <div class="group flex items-start gap-5 px-8 py-6 transition-colors hover:bg-slate-50/50">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 shadow-sm transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-bold text-slate-900 line-clamp-1">{{ data_get($notification->data, 'title', 'Update Available') }}</p>
                                <span class="whitespace-nowrap text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $notification->created_at?->diffForHumans(null, true) }}</span>
                            </div>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ data_get($notification->data, 'message', '-') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                            <i class="fa-solid fa-inbox text-2xl"></i>
                        </div>
                        <p class="mt-4 text-sm font-medium text-slate-400">All caught up! No new notifications.</p>
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
                    holder.innerHTML = '<div class="px-8 py-16 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-300"><i class="fa-solid fa-inbox text-2xl"></i></div><p class="mt-4 text-sm font-medium text-slate-400">All caught up! No new notifications.</p></div>';
                    return;
                }

                holder.innerHTML = items.map(function (item) {
                    const title = (item.data && item.data.title) ? item.data.title : 'Update Available';
                    const message = (item.data && item.data.message) ? item.data.message : '-';
                    const createdAt = item.created_at ? item.created_at : 'Just now';

                    return `
                        <div class="group flex items-start gap-5 px-8 py-6 transition-colors hover:bg-slate-50/50">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 shadow-sm transition-transform group-hover:scale-110">
                                <i class="fa-solid fa-bell text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-1">${title}</p>
                                    <span class="whitespace-nowrap text-[10px] font-bold uppercase tracking-wider text-slate-400">${createdAt}</span>
                                </div>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">${message}</p>
                            </div>
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
