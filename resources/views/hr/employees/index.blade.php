<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">HR Employee Management</h2>
                <p class="mt-2 text-sm text-slate-500">Register, track, and manage complete employee records and status lifecycle.</p>
            </div>
            @can('hr.employees.manage')
                <a href="{{ route('hr.employees.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 transition-all hover:shadow-xl">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register Employee
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Employees</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Active</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Contracts Ending Soon</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['contracts_ending_soon'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Exited / Terminated</p>
                <p class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['terminated'] }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
            <form method="GET" class="grid gap-4 md:grid-cols-4">
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Search Employees</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, employee number, email, phone, position..."
                           class="w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                        <option value="">All Statuses</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected($activeStatus === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('hr.employees.index') }}"
                       class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition-all hover:bg-slate-50">
                        Clear
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-[var(--nmis-primary)] px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white transition-all hover:bg-[var(--nmis-secondary)]">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contacts</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Employment</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Next Of Kin</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Documents</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($employees as $employee)
                            @php
                                $statusClass = match($employee->employment_status) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'probation' => 'bg-blue-100 text-blue-700',
                                    'on_leave' => 'bg-indigo-100 text-indigo-700',
                                    'suspended' => 'bg-amber-100 text-amber-700',
                                    'terminated', 'resigned', 'contract_expired' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                                $primaryKin = $employee->nextOfKins->firstWhere('is_primary', true);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-11 w-11 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
                                            @if($employee->photo_path)
                                                <img src="{{ route('hr.employees.photo', $employee->encrypted_id) }}"
                                                     alt="{{ $employee->full_name }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-500">
                                                    {{ strtoupper(substr($employee->first_name, 0, 1).substr($employee->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $employee->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
                                            <p class="text-xs text-slate-500">{{ $employee->position_title }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <p>{{ $employee->phone_number }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $employee->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <p class="text-xs text-slate-500">Employed: {{ $employee->date_employed?->format('d M Y') ?? '-' }}</p>
                                    <p class="mt-1">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $employee->employment_status_label }}
                                        </span>
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    @if($primaryKin)
                                        <p class="font-medium text-slate-900">{{ $primaryKin->full_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $primaryKin->phone_number }}</p>
                                    @else
                                        <span class="text-xs text-slate-400">Not available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <p>{{ $employee->cv_path ? 'CV uploaded' : 'No CV' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $employee->certificates_count }} certificate(s)</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('hr.employees.show', $employee->encrypted_id) }}"
                                           class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                            View
                                        </a>
                                        @can('hr.employees.manage')
                                            <a href="{{ route('hr.employees.edit', $employee->encrypted_id) }}"
                                               class="rounded-lg border border-[var(--nmis-primary)]/30 bg-[var(--nmis-primary)]/5 px-3 py-1.5 text-xs font-semibold text-[var(--nmis-primary)] transition-all hover:bg-[var(--nmis-primary)]/10">
                                                Edit
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <h3 class="text-sm font-semibold text-slate-900">No employee records found</h3>
                                        <p class="mt-1 text-sm text-slate-500">Start by registering employees in the HR module.</p>
                                        @can('hr.employees.manage')
                                            <a href="{{ route('hr.employees.create') }}"
                                               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-[var(--nmis-secondary)]">
                                                Register First Employee
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $employees->links() }}
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

