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
                                    @if(!empty($log->context))
                                        <div x-data="{ open: false }" class="relative">
                                            <!-- Preview -->
                                            <div class="flex items-center gap-2 cursor-pointer" @click="open = !open">
                                                <span class="text-sm text-slate-600 truncate max-w-xs">
                                                    {{ Str::limit(json_encode($log->context), 50) }}
                                                </span>
                                                <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" 
                                                     :class="{ 'rotate-180': open }" 
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                            
                                            <!-- Expanded Context -->
                                            <div x-show="open" 
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute z-10 mt-2 w-96 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
                                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Context Details</h4>
                                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                                    @foreach(collect($log->context)->toArray() as $key => $value)
                                                        <div class="border-b border-slate-100 pb-2 last:border-0">
                                                            <span class="text-xs font-medium text-slate-500 block mb-1">{{ $key }}:</span>
                                                            @if(is_array($value) || is_object($value))
                                                                <pre class="text-xs bg-slate-50 p-2 rounded-lg overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                            @else
                                                                <span class="text-sm text-slate-900">{{ $value }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">No additional context</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
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
    </style>
</x-app-layout>
