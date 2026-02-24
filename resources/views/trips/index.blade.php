<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Trip Management</h2>
                <p class="mt-2 text-sm text-slate-500">Track, manage and monitor all your trips in one place</p>
            </div>
            
            @can('trips.create')
                <form method="POST" action="{{ route('trips.store') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <svg class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create New Trip
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Success Message -->
        @if(session('success'))
            <div class="animate-slide-down rounded-xl border border-emerald-200 bg-emerald-50/90 backdrop-blur-sm px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-emerald-100 p-1">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Trips</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="total-trips">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Open Trips</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]" id="open-trips">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Closed Trips</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]" id="closed-trips">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Completion Rate</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="completion-rate">0%</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       id="trip-search"
                       placeholder="Search by trip number, origin, destination..." 
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
            </div>
            <div class="flex items-center gap-3">
                <select id="trip-status" 
                        class="rounded-xl border-slate-200 bg-white px-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all min-w-[150px]">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
                <button type="button" 
                        id="trip-filter-btn"
                        class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all shadow-lg shadow-[var(--nmis-primary)]/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Trips Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Trip Number</th>
                      
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Created</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody id="trip-table" class="divide-y divide-slate-100 bg-white">
                        <!-- Data will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Loading State -->
            <div id="loading-state" class="py-12 text-center hidden">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 mb-4">
                    <svg class="h-5 w-5 text-slate-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-500">Loading trips...</p>
            </div>
            
            <!-- Empty State -->
            <div id="empty-state" class="py-12 text-center hidden">
                <div class="flex flex-col items-center justify-center">
                    <div class="rounded-full bg-slate-100 p-3 mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.053-1.764L9 2m0 18l5.447-2.724A2 2 0 0016 15.382V5.618a2 2 0 00-1.053-1.764L9 2m0 18V2"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-slate-900 mb-1">No trips found</h3>
                    <p class="text-sm text-slate-500 mb-4">Get started by creating your first trip</p>
                    @can('trips.create')
                        <form method="POST" action="{{ route('trips.store') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--nmis-secondary)] transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create New Trip
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div id="close-trip-modal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeTripModal()"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl border border-slate-200/60 bg-white shadow-2xl">
                    <div class="border-b border-slate-200/70 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">Close Trip</h3>
                        <p class="mt-1 text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                    <div class="px-6 py-5">
                        <p class="text-sm text-slate-700">
                            Are you sure you want to close
                            <span id="close-trip-number" class="font-semibold text-slate-900"></span>?
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200/70 px-6 py-4">
                        <button type="button"
                                onclick="closeTripModal()"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <form id="close-trip-form" method="POST">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition-all">
                                Yes, Close Trip
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500" id="pagination-info"></div>
            <div class="flex items-center gap-2" id="pagination-controls"></div>
        </div>
    </div>

    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .status-dot {
            display: inline-block;
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 9999px;
        }
    </style>

    @push('scripts')
    <script>
        let currentPage = 0;
        const pageSize = 10;

        async function loadTrips(resetPage = true) {
            if (resetPage) currentPage = 0;
            
            const search = encodeURIComponent(document.getElementById('trip-search').value || '');
            const status = encodeURIComponent(document.getElementById('trip-status').value || '');
            
            // Show loading state
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('trip-table').innerHTML = '';
            document.getElementById('empty-state').classList.add('hidden');
            
            try {
                const response = await fetch(`{{ route('trips.index') }}?skip=${currentPage * pageSize}&take=${pageSize}&search=${search}&status=${status}`, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await response.json();
                
                const table = document.getElementById('trip-table');
                table.innerHTML = '';

                // Update stats
                updateStats(payload.stats || payload.meta || {});
                
                if (!payload.data?.length) {
                    document.getElementById('loading-state').classList.add('hidden');
                    document.getElementById('empty-state').classList.remove('hidden');
                    document.getElementById('pagination-info').innerHTML = '';
                    document.getElementById('pagination-controls').innerHTML = '';
                    return;
                }

                payload.data.forEach((trip) => {
                    const statusText = trip.status.charAt(0).toUpperCase() + trip.status.slice(1);
                    const statusBadge = trip.status === 'open'
                        ? `<span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                <span class="status-dot bg-emerald-500"></span>${statusText}
                           </span>`
                        : `<span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                <span class="status-dot bg-slate-500"></span>${statusText}
                           </span>`;
                    
                    table.insertAdjacentHTML('beforeend', `
                        <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">${trip.trip_number}</div>
                            </td>
                    
                            <td class="whitespace-nowrap px-6 py-4">
                                ${statusBadge}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-slate-500">${new Date(trip.created_at).toLocaleDateString()}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="${trip.show_url}"
                                       class="rounded-lg bg-slate-100 p-2 text-[var(--nmis-primary)] hover:bg-[var(--nmis-primary)] hover:text-white transition-all"
                                       title="View Trip">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    ${trip.status === 'open' && trip.can_close ? `
                                        <button type="button"
                                                onclick="openCloseTripModal('${trip.close_url}', '${trip.trip_number}')"
                                                class="rounded-lg bg-slate-100 p-2 text-amber-600 hover:bg-amber-600 hover:text-white transition-all"
                                                title="Close Trip">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    ` : '-'}
                                </div>
                            </td>
                        </tr>
                    `);
                });

                // Update pagination
                updatePagination(payload.meta || payload);
                
            } catch (error) {
                console.error('Failed to load trips:', error);
                document.getElementById('trip-table').innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-sm text-rose-600">Error loading trips. Please try again.</div>
                        </td>
                    </tr>
                `;
            } finally {
                document.getElementById('loading-state').classList.add('hidden');
            }
        }

        function updateStats(stats) {
            document.getElementById('total-trips').textContent = stats.total || 0;
            document.getElementById('open-trips').textContent = stats.open || 0;
            document.getElementById('closed-trips').textContent = stats.closed || 0;
            
            const total = stats.total || 0;
            const closed = stats.closed || 0;
            const rate = total > 0 ? Math.round((closed / total) * 100) : 0;
            document.getElementById('completion-rate').textContent = rate + '%';
        }

        function updatePagination(meta) {
            const total = meta.total || 0;
            const lastPage = Math.ceil(total / pageSize) - 1;

            if (total === 0) {
                document.getElementById('pagination-info').innerHTML = 'No records found';
                document.getElementById('pagination-controls').innerHTML = '';
                return;
            }
            
            document.getElementById('pagination-info').innerHTML = 
                `Showing ${currentPage * pageSize + 1} to ${Math.min((currentPage + 1) * pageSize, total)} of ${total} trips`;

            let controls = '';
            
            // Previous button
            controls += `
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage === 0 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage === 0 ? 'disabled' : `onclick="changePage(${currentPage - 1})"`}>
                    Previous
                </button>
            `;
            
            // Next button
            controls += `
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage >= lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage >= lastPage ? 'disabled' : `onclick="changePage(${currentPage + 1})"`}>
                    Next
                </button>
            `;
            
            document.getElementById('pagination-controls').innerHTML = controls;
        }

        function changePage(page) {
            currentPage = page;
            loadTrips(false);
        }

        function openCloseTripModal(closeUrl, tripNumber) {
            const modal = document.getElementById('close-trip-modal');
            const form = document.getElementById('close-trip-form');
            const numberLabel = document.getElementById('close-trip-number');
            if (!modal || !form || !numberLabel) return;

            form.action = closeUrl;
            numberLabel.textContent = tripNumber;
            modal.classList.remove('hidden');
        }

        function closeTripModal() {
            const modal = document.getElementById('close-trip-modal');
            if (!modal) return;
            modal.classList.add('hidden');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadTrips();
            
            document.getElementById('trip-filter-btn')?.addEventListener('click', () => loadTrips(true));
            
            // Search on enter key
            document.getElementById('trip-search')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadTrips(true);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
