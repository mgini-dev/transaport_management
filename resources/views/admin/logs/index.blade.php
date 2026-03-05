<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Audit Logs</h2>
                <p class="mt-2 text-sm text-slate-500">System-wide activity tracking for administrators</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.logs.export.csv', request()->query()) }}" 
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
        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Total Events</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Unique Actions</p>
                    <span class="rounded-lg bg-[var(--nmis-secondary)]/10 p-2 text-[var(--nmis-secondary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-secondary)]">{{ $stats['unique_actions'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Active Users</p>
                    <span class="rounded-lg bg-[var(--nmis-accent)]/10 p-2 text-[var(--nmis-accent)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-[var(--nmis-accent)]">{{ $stats['active_users'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-500">Time Range</p>
                    <span class="rounded-lg bg-[var(--nmis-primary)]/10 p-2 text-[var(--nmis-primary)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-900">
                    @if($logs->isNotEmpty())
                        {{ $logs->first()->created_at->diffForHumans() }}
                    @else
                        No data
                    @endif
                </p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
            <form method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Filter by Action
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="action" 
                               value="{{ request('action') }}" 
                               class="w-full rounded-xl border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                               placeholder="e.g., order.created, user.login, trip.updated">
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Per Page</label>
                        <select name="per_page"
                                class="rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all">
                            <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ (int) $perPage === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ (int) $perPage === 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[var(--nmis-secondary)] transition-all shadow-lg shadow-[var(--nmis-primary)]/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter Logs
                    </button>
                    
                    @if(request('action') || request('per_page'))
                        <a href="{{ route('admin.logs.index') }}" 
                           class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Quick Filter Chips -->
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-xs text-slate-500 self-center mr-2">Quick filters:</span>
                @php
                    $commonActions = [
                        'created' => 'bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)]',
                        'updated' => 'bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]',
                        'deleted' => 'bg-rose-100 text-rose-700',
                        'login' => 'bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]',
                        'approved' => 'bg-emerald-100 text-emerald-700',
                        'rejected' => 'bg-rose-100 text-rose-700',
                    ];
                @endphp
                
                @foreach($commonActions as $action => $class)
                    <a href="{{ route('admin.logs.index', ['action' => $action]) }}" 
                       class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $class }} hover:opacity-80 transition-opacity">
                        {{ ucfirst($action) }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Logs Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Timestamp</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Context / Details</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $log->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-slate-500">{{ $log->created_at->format('H:i:s') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($log->user)
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[var(--nmis-primary)]/10 to-[var(--nmis-secondary)]/10 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-[var(--nmis-primary)]">
                                                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-slate-900">{{ $log->user->name }}</div>
                                                <div class="text-xs text-slate-500">ID: {{ $log->user_id }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">System / Guest</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $actionColor = match(true) {
                                            str_contains($log->action, 'created') => 'bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)]',
                                            str_contains($log->action, 'updated') => 'bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]',
                                            str_contains($log->action, 'deleted') => 'bg-rose-100 text-rose-700',
                                            str_contains($log->action, 'login') => 'bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]',
                                            str_contains($log->action, 'approved') => 'bg-emerald-100 text-emerald-700',
                                            str_contains($log->action, 'rejected') => 'bg-rose-100 text-rose-700',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $actionColor }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $contextData = is_array($log->context) ? $log->context : [];
                                        $contextPreview = collect($contextData)->take(3);
                                    @endphp
                                    @if(!empty($contextData))
                                        <div class="max-w-md space-y-2">
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($contextPreview as $ctxKey => $ctxValue)
                                                    @php
                                                        $contextLabel = ucwords(str_replace(['_', '-'], ' ', (string) $ctxKey));
                                                        if (is_array($ctxValue)) {
                                                            $contextValueLabel = count($ctxValue).' item'.(count($ctxValue) === 1 ? '' : 's');
                                                        } elseif (is_bool($ctxValue)) {
                                                            $contextValueLabel = $ctxValue ? 'Yes' : 'No';
                                                        } elseif (is_null($ctxValue) || $ctxValue === '') {
                                                            $contextValueLabel = 'N/A';
                                                        } else {
                                                            $contextValueLabel = Str::limit((string) $ctxValue, 26);
                                                        }
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                                                        <span class="text-slate-500">{{ $contextLabel }}:</span>
                                                        <span class="font-semibold text-slate-800">{{ $contextValueLabel }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                            @if(count($contextData) > 3)
                                                <p class="text-xs text-slate-400">+{{ count($contextData) - 3 }} more field(s)</p>
                                            @else
                                                <p class="text-xs text-slate-400">{{ count($contextData) }} field(s)</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">No additional context</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <button type="button"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[var(--nmis-primary)] hover:text-[var(--nmis-primary)] transition-all"
                                            data-log-view-url="{{ route('admin.logs.show', $log) }}"
                                            data-loading-text="Loading log details...">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-slate-100 p-3 mb-4">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-1">No audit logs found</h3>
                                        <p class="text-sm text-slate-500">Try adjusting your filters or check back later</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            @if($logs->total() > 0)
                <p class="mb-2 text-sm text-slate-500">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                </p>
            @endif
            {{ $logs->withQueryString()->links() }}
        </div>

        <div id="log-details-modal" class="log-modal hidden" aria-hidden="true">
            <div class="log-modal__backdrop" data-close-log-modal="true"></div>
            <div class="log-modal__panel" role="dialog" aria-modal="true" aria-labelledby="log-details-title">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h3 id="log-details-title" class="text-base font-semibold text-slate-900">Audit Log Details</h3>
                        <p class="mt-1 text-xs text-slate-500">Complete activity information for the selected log.</p>
                    </div>
                    <button type="button"
                            id="close-log-details-modal"
                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-all">
                        Close
                    </button>
                </div>

                <div class="max-h-[78vh] overflow-auto px-5 py-4 space-y-4">
                    <div id="log-details-loading" class="space-y-3">
                        <div class="h-3 w-32 rounded bg-slate-100 animate-pulse"></div>
                        <div class="h-3 w-full rounded bg-slate-100 animate-pulse"></div>
                        <div class="h-3 w-5/6 rounded bg-slate-100 animate-pulse"></div>
                    </div>

                    <div id="log-details-content" class="hidden space-y-4">
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Log ID</p>
                                <p id="log-detail-id" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Action</p>
                                <p id="log-detail-action" class="mt-1 text-sm font-semibold text-slate-900 break-all">-</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Date and Time</p>
                                <p id="log-detail-created-at" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">IP Address</p>
                                <p id="log-detail-ip" class="mt-1 text-sm font-semibold text-slate-900 break-all">-</p>
                            </div>
                        </div>

                        <div class="grid gap-4 xl:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <h4 class="text-sm font-semibold text-slate-800">Actor Information</h4>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">User</dt>
                                        <dd id="log-detail-user-name" class="font-medium text-slate-900 text-right">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">User ID</dt>
                                        <dd id="log-detail-user-id" class="font-medium text-slate-900 text-right">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Email</dt>
                                        <dd id="log-detail-user-email" class="font-medium text-slate-900 text-right break-all">-</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <h4 class="text-sm font-semibold text-slate-800">Affected Resource</h4>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Type</dt>
                                        <dd id="log-detail-resource-type" class="font-medium text-slate-900 text-right break-all">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Model Class</dt>
                                        <dd id="log-detail-resource-class" class="font-medium text-slate-900 text-right break-all">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Resource ID</dt>
                                        <dd id="log-detail-resource-id" class="font-medium text-slate-900 text-right">-</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <h4 class="text-sm font-semibold text-slate-800">User Agent</h4>
                            <p id="log-detail-user-agent" class="mt-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-700 break-all">-</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-slate-800">Context Details</h4>
                                <button type="button" id="log-context-copy"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                    Copy
                                </button>
                            </div>
                            <div id="log-detail-context" class="log-context mt-2 max-h-[20rem] overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-3"></div>
                        </div>
                    </div>

                    <div id="log-details-error" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Failed to load log details. Please try again.
                    </div>
                </div>
            </div>
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
        
        /* Custom scrollbar for context modal */
        .overflow-y-auto::-webkit-scrollbar {
            width: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .log-modal {
            position: fixed;
            inset: 0;
            z-index: 110;
        }

        .log-modal.hidden {
            display: none;
        }

        .log-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
        }

        .log-modal__panel {
            position: relative;
            z-index: 1;
            width: min(72rem, calc(100vw - 1.5rem));
            margin: 6vh auto 0;
            border-radius: 1rem;
            border: 1px solid #dbeafe;
            background: #fff;
            box-shadow: 0 28px 50px rgba(15, 23, 42, 0.35);
        }

        .log-context {
            display: grid;
            gap: 0.75rem;
        }

        .log-context::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .log-context::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
        }

        .log-context__section {
            border: 1px solid #dbeafe;
            border-radius: 0.75rem;
            background: #ffffff;
            overflow: hidden;
        }

        .log-context__section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.55rem 0.75rem;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .log-context__section-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #334155;
        }

        .log-context__section-meta {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
        }

        .log-context__section-body {
            display: grid;
            gap: 0.5rem;
            padding: 0.55rem 0.7rem 0.65rem;
        }

        .log-context__row {
            display: grid;
            grid-template-columns: minmax(8.5rem, 12rem) 1fr;
            gap: 0.5rem;
            align-items: start;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.42rem 0.1rem;
        }

        .log-context__row:last-child {
            border-bottom: 0;
        }

        .log-context__key {
            font-size: 0.68rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.045em;
        }

        .log-context__value {
            font-size: 0.8rem;
            color: #0f172a;
            word-break: break-word;
            line-height: 1.35;
        }

        .log-context__text {
            color: #0f172a;
            font-weight: 500;
        }

        .log-context__pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.72rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #334155;
        }

        .log-context__pill--success {
            background: #dcfce7;
            color: #166534;
        }

        .log-context__pill--muted {
            background: #f1f5f9;
            color: #475569;
        }

        .log-context__nested {
            border: 1px solid #e2e8f0;
            border-radius: 0.65rem;
            background: #f8fafc;
            padding: 0.5rem 0.55rem;
        }

        .log-context__nested-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }

        .log-context__nested-title {
            font-size: 0.72rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .log-context__nested-meta {
            font-size: 0.66rem;
            color: #64748b;
            font-weight: 600;
        }

        .log-context__nested-body {
            border-top: 1px dashed #dbeafe;
            padding-top: 0.35rem;
        }

        .log-context__empty {
            border-radius: 0.6rem;
            border: 1px dashed #cbd5e1;
            background: #fff;
            padding: 0.8rem;
            font-size: 0.78rem;
            color: #64748b;
            text-align: center;
        }

        @media (max-width: 768px) {
            .log-context__row {
                grid-template-columns: 1fr;
                gap: 0.2rem;
            }
        }
    </style>

    @push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('log-details-modal');
            if (!modal) return;

            const loading = document.getElementById('log-details-loading');
            const content = document.getElementById('log-details-content');
            const error = document.getElementById('log-details-error');

            const fields = {
                id: document.getElementById('log-detail-id'),
                action: document.getElementById('log-detail-action'),
                createdAt: document.getElementById('log-detail-created-at'),
                ip: document.getElementById('log-detail-ip'),
                userName: document.getElementById('log-detail-user-name'),
                userId: document.getElementById('log-detail-user-id'),
                userEmail: document.getElementById('log-detail-user-email'),
                resourceType: document.getElementById('log-detail-resource-type'),
                resourceClass: document.getElementById('log-detail-resource-class'),
                resourceId: document.getElementById('log-detail-resource-id'),
                userAgent: document.getElementById('log-detail-user-agent'),
                context: document.getElementById('log-detail-context'),
            };

            const closeBtn = document.getElementById('close-log-details-modal');
            const copyBtn = document.getElementById('log-context-copy');
            let rawContextJson = '{}';

            const setBodyLock = (locked) => {
                document.body.style.overflow = locked ? 'hidden' : '';
            };

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                setBodyLock(true);
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                setBodyLock(false);
            };

            const showLoading = () => {
                loading.classList.remove('hidden');
                content.classList.add('hidden');
                error.classList.add('hidden');
            };

            const showContent = () => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                error.classList.add('hidden');
            };

            const showError = () => {
                loading.classList.add('hidden');
                content.classList.add('hidden');
                error.classList.remove('hidden');
            };

            const MAX_CONTEXT_DEPTH = 4;

            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const humanizeKey = (key) => String(key)
                .replace(/[_-]+/g, ' ')
                .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/\b\w/g, (char) => char.toUpperCase()) || 'Field';

            const pluralize = (count, word) => `${count} ${word}${count === 1 ? '' : 's'}`;

            const tryParseStructuredString = (value) => {
                if (typeof value !== 'string') {
                    return value;
                }

                const trimmed = value.trim();
                if (!trimmed) {
                    return value;
                }

                const looksJsonObject = trimmed.startsWith('{') && trimmed.endsWith('}');
                const looksJsonArray = trimmed.startsWith('[') && trimmed.endsWith(']');

                if (!looksJsonObject && !looksJsonArray) {
                    return value;
                }

                try {
                    return JSON.parse(trimmed);
                } catch (error) {
                    return value;
                }
            };

            const formatPrimitive = (value) => {
                if (value === null || typeof value === 'undefined') {
                    return '<span class="log-context__pill log-context__pill--muted">Not provided</span>';
                }
                if (typeof value === 'boolean') {
                    return `<span class="log-context__pill ${value ? 'log-context__pill--success' : 'log-context__pill--muted'}">${value ? 'Yes' : 'No'}</span>`;
                }
                if (typeof value === 'number') {
                    return `<span class="log-context__pill">${value.toLocaleString()}</span>`;
                }

                const text = String(value).trim();
                if (text === '') {
                    return '<span class="log-context__pill log-context__pill--muted">Empty</span>';
                }

                const looksLikeDate = /^\d{4}-\d{2}-\d{2}(T|\s)/.test(text);
                if (looksLikeDate) {
                    const parsedDate = new Date(text);
                    if (!Number.isNaN(parsedDate.getTime())) {
                        return `<span class="log-context__pill">${escapeHtml(parsedDate.toLocaleString())}</span>`;
                    }
                }

                return `<span class="log-context__text">${escapeHtml(text)}</span>`;
            };

            const isStructured = (value) => Array.isArray(value) || (value !== null && typeof value === 'object');

            const isEmptyStructured = (value) => {
                if (!isStructured(value)) return false;
                return Array.isArray(value) ? value.length === 0 : Object.keys(value).length === 0;
            };

            const toEntries = (value) => Array.isArray(value)
                ? value.map((item, index) => [`Item ${index + 1}`, item])
                : Object.entries(value);

            const renderContextEntries = (entries, depth) => {
                if (!entries.length) {
                    return '<div class="log-context__empty">No context fields available for this log.</div>';
                }

                return entries.map(([key, rawValue]) => {
                    const value = tryParseStructuredString(rawValue);

                    if (!isStructured(value)) {
                        return `
                            <div class="log-context__row">
                                <div class="log-context__key">${escapeHtml(humanizeKey(key))}</div>
                                <div class="log-context__value">${formatPrimitive(value)}</div>
                            </div>
                        `;
                    }

                    const nestedEntries = toEntries(value);

                    if (!nestedEntries.length) {
                        return `
                            <div class="log-context__row">
                                <div class="log-context__key">${escapeHtml(humanizeKey(key))}</div>
                                <div class="log-context__value">
                                    <span class="log-context__pill log-context__pill--muted">Empty</span>
                                </div>
                            </div>
                        `;
                    }

                    if (depth >= MAX_CONTEXT_DEPTH) {
                        return `
                            <div class="log-context__row">
                                <div class="log-context__key">${escapeHtml(humanizeKey(key))}</div>
                                <div class="log-context__value">
                                    <span class="log-context__pill log-context__pill--muted">${pluralize(nestedEntries.length, 'nested field')}</span>
                                </div>
                            </div>
                        `;
                    }

                    return `
                        <div class="log-context__nested">
                            <div class="log-context__nested-head">
                                <span class="log-context__nested-title">${escapeHtml(humanizeKey(key))}</span>
                                <span class="log-context__nested-meta">${pluralize(nestedEntries.length, 'field')}</span>
                            </div>
                            <div class="log-context__nested-body">
                                ${renderContextEntries(nestedEntries, depth + 1)}
                            </div>
                        </div>
                    `;
                }).join('');
            };

            const renderContext = (context) => {
                const normalizedContext = tryParseStructuredString(context ?? {});
                if (!isStructured(normalizedContext) || isEmptyStructured(normalizedContext)) {
                    fields.context.innerHTML = '<div class="log-context__empty">No context fields available for this log.</div>';
                    return;
                }

                const rootEntries = toEntries(normalizedContext);
                fields.context.innerHTML = rootEntries.map(([key, rootValue]) => {
                    const parsedRootValue = tryParseStructuredString(rootValue);
                    if (!isStructured(parsedRootValue)) {
                        return `
                            <section class="log-context__section">
                                <div class="log-context__section-header">
                                    <h5 class="log-context__section-title">${escapeHtml(humanizeKey(key))}</h5>
                                </div>
                                <div class="log-context__section-body">
                                    <div class="log-context__row">
                                        <div class="log-context__key">Value</div>
                                        <div class="log-context__value">${formatPrimitive(parsedRootValue)}</div>
                                    </div>
                                </div>
                            </section>
                        `;
                    }

                    const sectionEntries = toEntries(parsedRootValue);
                    return `
                        <section class="log-context__section">
                            <div class="log-context__section-header">
                                <h5 class="log-context__section-title">${escapeHtml(humanizeKey(key))}</h5>
                                <span class="log-context__section-meta">${pluralize(sectionEntries.length, 'field')}</span>
                            </div>
                            <div class="log-context__section-body">
                                ${sectionEntries.length
                                    ? renderContextEntries(sectionEntries, 1)
                                    : '<div class="log-context__empty">No context fields available for this section.</div>'}
                            </div>
                        </section>
                    `;
                }).join('');
            };

            const fillDetails = (payload) => {
                fields.id.textContent = payload?.id ?? '-';
                fields.action.textContent = payload?.action ?? '-';
                fields.createdAt.textContent = payload?.created_at_label ?? '-';
                fields.ip.textContent = payload?.ip_address ?? '-';
                fields.userName.textContent = payload?.user?.name ?? 'System / Guest';
                fields.userId.textContent = payload?.user?.id ?? '-';
                fields.userEmail.textContent = payload?.user?.email ?? '-';
                fields.resourceType.textContent = payload?.loggable?.type_label ?? '-';
                fields.resourceClass.textContent = payload?.loggable?.type ?? '-';
                fields.resourceId.textContent = payload?.loggable?.id ?? '-';
                fields.userAgent.textContent = payload?.user_agent ?? '-';
                const safeContext = payload?.context ?? {};
                rawContextJson = JSON.stringify(safeContext, null, 2);
                renderContext(safeContext);
            };

            document.querySelectorAll('[data-log-view-url]').forEach((button) => {
                button.addEventListener('click', async () => {
                    showLoading();
                    openModal();

                    try {
                        const response = await fetch(button.dataset.logViewUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch details');
                        }

                        const payload = await response.json();
                        fillDetails(payload);
                        showContent();
                    } catch (exception) {
                        console.error(exception);
                        showError();
                    }
                });
            });

            closeBtn?.addEventListener('click', closeModal);

            modal.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) return;
                if (target.dataset.closeLogModal === 'true') {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            copyBtn?.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(rawContextJson || '{}');
                    copyBtn.textContent = 'Copied';
                    setTimeout(() => {
                        copyBtn.textContent = 'Copy';
                    }, 1000);
                } catch (exception) {
                    console.error(exception);
                }
            });
        })();
    </script>
    @endpush
</x-app-layout>
