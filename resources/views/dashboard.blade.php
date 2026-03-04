<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Smart Operations Dashboard</h2>
                <p class="mt-2 text-sm text-slate-500">Interactive analytics for trips, orders, requisitions, and approvals.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm border border-slate-200">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--nmis-accent)] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[var(--nmis-accent)]"></span>
                </span>
                Dashboard Preset: {{ $preset }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Stats Grid -->
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
            @php
                $statsCards = [
                    ['label' => 'Open Trips', 'value' => $stats['open_trips'], 'color' => 'primary', 'icon' => '🧭'],
                    ['label' => 'Closed Trips', 'value' => $stats['closed_trips'], 'color' => 'secondary', 'icon' => '✅'],
                    ['label' => 'Orders Active', 'value' => $stats['orders_in_progress'], 'color' => 'accent', 'icon' => '📦'],
                    ['label' => 'Fuel Pending', 'value' => $stats['fuel_pending'], 'color' => 'primary', 'icon' => '⛽'],
                    ['label' => 'Completion Rate', 'value' => $stats['completion_rate'] . '%', 'color' => 'secondary', 'icon' => '📈'],
                ];
            @endphp

            @foreach($statsCards as $card)
                <div class="group relative">
                    <!-- Gradient Background Effect -->
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-[var(--nmis-{{ $card['color'] }})] to-[var(--nmis-{{ $card['color'] }})] opacity-0 group-hover:opacity-20 rounded-2xl blur transition-all duration-500"></div>
                    
                    <!-- Card -->
                    <div class="relative rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">{{ $card['label'] }}</p>
                            <span class="rounded-xl bg-[color:rgba(27,59,134,0.08)] p-3 text-xl backdrop-blur-sm
                                       {{ $card['color'] == 'primary' ? 'text-[var(--nmis-primary)]' : '' }}
                                       {{ $card['color'] == 'secondary' ? 'text-[var(--nmis-secondary)]' : '' }}
                                       {{ $card['color'] == 'accent' ? 'text-[var(--nmis-accent)]' : '' }}">
                                {{ $card['icon'] }}
                            </span>
                        </div>
                        <p class="mt-4 text-4xl font-bold text-slate-900">{{ $card['value'] }}</p>
                        
                        <!-- Sparkline (mini trend) -->
                        <div class="mt-4 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full w-3/4 bg-gradient-to-r from-[var(--nmis-{{ $card['color'] }})] to-[var(--nmis-{{ $card['color'] }})] rounded-full"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Charts Grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            @if ($widgets['trip_trend'])
            <div class="group rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300">
                <h3 class="mb-6 text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]">📊</span>
                    Trips Created - Last 7 Days
                </h3>
                <div class="h-64">
                    <canvas id="tripTrendChart"></canvas>
                </div>
            </div>
            @endif

            @if ($widgets['order_status'])
            <div class="group rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300">
                <h3 class="mb-6 text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]">🔄</span>
                    Order Status Distribution
                </h3>
                <div class="h-64">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
            @endif

            @if ($widgets['fuel_spend'])
            <div class="group rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300">
                <h3 class="mb-6 text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)]">⛽</span>
                    Approved Fuel Spend - Last 6 Months
                </h3>
                <div class="h-64">
                    <canvas id="fuelSpendChart"></canvas>
                </div>
            </div>
            @endif

            @if ($widgets['approval_pipeline'])
            <div class="group rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300">
                <h3 class="mb-6 text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]">⚡</span>
                    Approval Pipeline
                </h3>
                <div class="h-64">
                    <canvas id="approvalPipelineChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <!-- Notification Feed -->
        <section class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-200/60">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--nmis-accent)] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-[var(--nmis-accent)]"></span>
                    </span>
                    Live Notification Feed
                </h3>
                <button id="refresh-notifications" 
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-all duration-200 group">
                    <svg class="h-4 w-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
            
            <div id="notification-list" class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    <div class="p-6 hover:bg-slate-50/80 transition-colors duration-200 group/item">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                    <span class="text-lg">📢</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900">{{ data_get($notification->data, 'title') }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ data_get($notification->data, 'message') }}</p>
                                <p class="mt-2 text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="opacity-0 group-hover/item:opacity-100 transition-opacity duration-200">
                                <button class="text-slate-400 hover:text-slate-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500">No notifications yet.</p>
                        <p class="text-xs text-slate-400 mt-1">We'll notify you when something arrives</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        // Enhanced chart configurations with your existing color codes
        const chartConfigs = {
            primary: '#1b3b86',
            secondary: '#2a9d8f', 
            accent: '#6cb63f',
            background: '#f8fafc'
        };

        // Chart defaults with your colors
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#64748b';

        // Initialize charts with your existing data
        document.addEventListener('DOMContentLoaded', function() {
            // Trip Trend Chart (Line)
            if (document.getElementById('tripTrendChart')) {
                new Chart(document.getElementById('tripTrendChart'), {
                    type: 'line',
                    data: {
                        labels: @json($charts['trip_trend_labels']),
                        datasets: [{
                            label: 'Trips',
                            data: @json($charts['trip_trend_values']),
                            borderColor: chartConfigs.primary,
                            backgroundColor: 'rgba(27,59,134,0.08)',
                            borderWidth: 3,
                            pointBackgroundColor: chartConfigs.primary,
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.3,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                padding: 12,
                                cornerRadius: 12,
                                displayColors: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#e2e8f0', drawBorder: false },
                                border: { display: false }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            // Order Status Chart (Doughnut)
            if (document.getElementById('orderStatusChart')) {
                new Chart(document.getElementById('orderStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Created', 'Processing', 'Assigned', 'Completed'],
                        datasets: [{
                            data: [
                                {{ $charts['order_status']['created'] }},
                                {{ $charts['order_status']['processing'] }},
                                {{ $charts['order_status']['assigned'] }},
                                {{ $charts['order_status']['completed'] }},
                            ],
                            backgroundColor: [
                                chartConfigs.primary,
                                chartConfigs.secondary,
                                chartConfigs.accent,
                                '#94a3b8'
                            ],
                            borderWidth: 0,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            }
                        }
                    }
                });
            }

            // Fuel Spend Chart (Bar)
            if (document.getElementById('fuelSpendChart')) {
                new Chart(document.getElementById('fuelSpendChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($charts['fuel_spend_labels']),
                        datasets: [{
                            label: 'Amount',
                            data: @json($charts['fuel_spend_values']),
                            backgroundColor: chartConfigs.secondary,
                            borderRadius: 8,
                            barPercentage: 0.6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#e2e8f0' },
                                border: { display: false }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            // Approval Pipeline Chart (Polar Area)
            if (document.getElementById('approvalPipelineChart')) {
                new Chart(document.getElementById('approvalPipelineChart'), {
                    type: 'polarArea',
                    data: {
                        labels: ['Submitted', 'Supervisor Approved', 'Accountant Approved', 'Rejected'],
                        datasets: [{
                            data: [
                                {{ $charts['approval_pipeline']['submitted'] }},
                                {{ $charts['approval_pipeline']['supervisor_approved'] }},
                                {{ $charts['approval_pipeline']['accountant_approved'] }},
                                {{ $charts['approval_pipeline']['rejected'] }},
                            ],
                            backgroundColor: [
                                'rgba(27,59,134,0.85)',
                                'rgba(42,157,143,0.85)',
                                'rgba(108,182,63,0.85)',
                                'rgba(239,68,68,0.85)'
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true }
                            }
                        }
                    }
                });
            }
        });

        // Enhanced notification refresh with animation
        document.getElementById('refresh-notifications')?.addEventListener('click', async function(e) {
            const button = this;
            const icon = button.querySelector('svg');
            icon.classList.add('animate-spin');
            button.disabled = true;

            try {
                const response = await fetch('{{ route('notifications.index') }}?skip=0&take=5', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                const holder = document.getElementById('notification-list');

                // Fade out
                holder.style.opacity = '0';
                
                setTimeout(() => {
                    if (!payload.data?.length) {
                        holder.innerHTML = `
                            <div class="p-12 text-center">
                                <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500">No notifications yet.</p>
                                <p class="text-xs text-slate-400 mt-1">We'll notify you when something arrives</p>
                            </div>
                        `;
                    } else {
                        holder.innerHTML = payload.data.map(notification => `
                            <div class="p-6 hover:bg-slate-50/80 transition-colors duration-200 group/item">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                            <span class="text-lg">📢</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-slate-900">${notification.data?.title ?? ''}</p>
                                        <p class="mt-1 text-sm text-slate-600">${notification.data?.message ?? ''}</p>
                                        <p class="mt-2 text-xs text-slate-400">Just now</p>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    // Fade in
                    holder.style.opacity = '1';
                }, 300);
                
            } catch (error) {
                console.error('Refresh failed:', error);
            } finally {
                setTimeout(() => {
                    icon.classList.remove('animate-spin');
                    button.disabled = false;
                }, 500);
            }
        });
    </script>
    @endpush
</x-app-layout>
