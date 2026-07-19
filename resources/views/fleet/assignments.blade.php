<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-bold gradient-text">Assignment Logs</h2>
            <p class="mt-2 text-sm text-slate-500">Historical timeline of driver-vehicle pairings</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @include('fleet.partials.navigation')

        <!-- Assignments Summary Cards -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Pairings</p>
                    <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($activePairings ?? 0, 0) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Historic Assignments</p>
                    <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($totalAssignments ?? 0, 0) }}</p>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm transition-all hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <h3 class="text-sm font-bold text-slate-900">Assignment Timeline</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Driver Details</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Vehicle Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned Period</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Odometer Range</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($history as $entry)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                            {{ strtoupper(substr($entry->driver->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-bold text-slate-900">{{ $entry->driver->name }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('fleet.show', $entry->fleet->encrypted_id) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-[var(--nmis-primary)] hover:text-[var(--nmis-secondary)] transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h1"></path>
                                        </svg>
                                        {{ $entry->fleet->fleet_code }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <div class="text-xs font-medium text-slate-700">
                                            <span class="text-slate-400">From:</span> {{ $entry->assigned_at->format('M d, Y H:i') }}
                                        </div>
                                        <div class="text-xs font-medium text-slate-700">
                                            <span class="text-slate-400">To:</span> {{ $entry->unassigned_at ? $entry->unassigned_at->format('M d, Y H:i') : 'Present' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($entry->unassigned_at)
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold bg-slate-100 text-slate-600 uppercase border border-slate-200">Completed</span>
                                    @else
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase border border-emerald-200 animate-pulse">Active Pair</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-600 tabular-nums">
                                    <div class="flex flex-col gap-0.5">
                                        <div>{{ number_format($entry->start_odometer, 0) }} km <span class="text-slate-400">→</span> {{ $entry->end_odometer ? number_format($entry->end_odometer, 0) . ' km' : '...' }}</div>
                                        @if($entry->end_odometer)
                                            <div class="font-bold text-slate-900">{{ number_format($entry->end_odometer - $entry->start_odometer, 0) }} km driven</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                    No assignment history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $history->links() }}
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
</x-app-layout>
