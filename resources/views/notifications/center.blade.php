<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Central Notification Center</h2>
                <p class="mt-2 text-sm text-slate-500">Real-time notifications with live updates and AJAX listing.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('notifications.export.csv') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[var(--nmis-primary)] transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
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

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Notifications</p>
                <p class="mt-3 text-3xl font-bold text-slate-900" id="stat-total">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Unread</p>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]" id="stat-unread">0</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Read</p>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]" id="stat-read">0</p>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('notifications.read_all') }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                    <svg class="h-4 w-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark All as Read
                </button>
            </form>

            <div class="relative flex-1 max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       id="notification-search"
                       placeholder="Search notifications..."
                       class="w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Notification</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date & Time</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody id="notification-table" class="divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500" id="pagination-info"></div>
            <div class="flex items-center gap-2" id="pagination-controls"></div>
        </div>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @push('scripts')
    <script>
        let currentPage = 0;
        const pageSize = 10;
        let latestMeta = { total: 0, unread: 0, read: 0 };

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatType(type) {
            const raw = String(type || 'general');
            const part = raw.includes('.') ? raw.split('.')[0] : raw;
            return part.charAt(0).toUpperCase() + part.slice(1);
        }

        function renderStats(meta) {
            document.getElementById('stat-total').textContent = meta.total || 0;
            document.getElementById('stat-unread').textContent = meta.unread || 0;
            document.getElementById('stat-read').textContent = meta.read || 0;
            if (typeof window.updateGlobalNotificationBadge === 'function') {
                window.updateGlobalNotificationBadge(meta.unread || 0);
            }
        }

        async function markNotificationRead(notificationId) {
            const response = await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;
            const payload = await response.json();
            if (typeof window.updateGlobalNotificationBadge === 'function') {
                window.updateGlobalNotificationBadge(payload.unread_count || 0);
            }
            await loadNotifications(false);
        }

        function renderNotifications(items) {
            const table = document.getElementById('notification-table');
            table.innerHTML = '';

            if (!items.length) {
                table.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No notifications found.</td>
                    </tr>
                `;
                return;
            }

            items.forEach((item) => {
                const isUnread = !item.read_at;
                const title = escapeHtml(item.data?.title || 'Untitled Notification');
                const message = escapeHtml(item.data?.message || 'No message content');
                const type = escapeHtml(formatType(item.data?.type));
                const created = new Date(item.created_at);
                const createdDate = created.toLocaleDateString();
                const createdTime = created.toLocaleTimeString();

                table.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-slate-50/80 transition-colors ${isUnread ? 'bg-[var(--nmis-primary)]/[0.02]' : ''}">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">${title}</p>
                            <p class="mt-1 text-sm text-slate-600">${message}</p>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">${type}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-slate-900">${createdDate}</div>
                            <div class="text-xs text-slate-500">${createdTime}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            ${isUnread
                                ? '<span class="inline-flex items-center rounded-full bg-[var(--nmis-secondary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--nmis-secondary)]">Unread</span>'
                                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Read</span>'
                            }
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            ${isUnread
                                ? `<button type="button" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-[var(--nmis-secondary)] hover:text-white transition-all" data-mark-read="${item.id}">Mark Read</button>`
                                : '<span class="text-xs text-slate-400">-</span>'
                            }
                        </td>
                    </tr>
                `);
            });

            table.querySelectorAll('[data-mark-read]').forEach((button) => {
                button.addEventListener('click', () => markNotificationRead(button.getAttribute('data-mark-read')));
            });
        }

        function updatePagination(meta) {
            const total = meta.total || 0;
            const lastPage = Math.max(Math.ceil(total / pageSize) - 1, 0);

            if (total === 0) {
                document.getElementById('pagination-info').textContent = 'No records found';
                document.getElementById('pagination-controls').innerHTML = '';
                return;
            }

            const start = currentPage * pageSize + 1;
            const end = Math.min((currentPage + 1) * pageSize, total);
            document.getElementById('pagination-info').textContent = `Showing ${start} to ${end} of ${total} notifications`;

            document.getElementById('pagination-controls').innerHTML = `
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage === 0 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage === 0 ? 'disabled' : `onclick="changePage(${currentPage - 1})"`}>Previous</button>
                <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ${currentPage >= lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'}"
                        ${currentPage >= lastPage ? 'disabled' : `onclick="changePage(${currentPage + 1})"`}>Next</button>
            `;
        }

        async function loadNotifications(resetPage = true) {
            if (resetPage) currentPage = 0;
            const search = encodeURIComponent(document.getElementById('notification-search').value || '');
            const skip = currentPage * pageSize;

            const response = await fetch(`{{ route('notifications.index') }}?skip=${skip}&take=${pageSize}&search=${search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();

            latestMeta = payload.meta || { total: 0, unread: 0, read: 0 };
            renderStats(latestMeta);
            renderNotifications(payload.data || []);
            updatePagination(latestMeta);
        }

        function changePage(page) {
            currentPage = page;
            loadNotifications(false);
        }

        window.refreshNotificationsCenter = function refreshNotificationsCenter() {
            loadNotifications(false).catch((error) => console.error('Notification refresh failed', error));
        };

        document.addEventListener('DOMContentLoaded', function () {
            loadNotifications();

            document.getElementById('notification-search')?.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    loadNotifications(true);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
