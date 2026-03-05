<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">{{ $employee->full_name }}</h2>
                <p class="mt-2 text-sm text-slate-500">Employee profile, contract records, status history, and documents.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('hr.employees.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>
                <a href="{{ route('hr.employees.document.download', $employee->encrypted_id) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition-all hover:bg-emerald-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-8m0 8-3-3m3 3 3-3M5 20h14"></path>
                    </svg>
                    Download Full Document
                </a>
                @if($canManage)
                    <a href="{{ route('hr.employees.edit', $employee->encrypted_id) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[var(--nmis-primary)] px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--nmis-secondary)]">
                        Edit Profile
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ openStatusModal: false }">
        @php
            $statusClass = match($employee->employment_status) {
                'active' => 'bg-emerald-100 text-emerald-700',
                'probation' => 'bg-blue-100 text-blue-700',
                'on_leave' => 'bg-indigo-100 text-indigo-700',
                'suspended' => 'bg-amber-100 text-amber-700',
                'terminated', 'resigned', 'contract_expired' => 'bg-rose-100 text-rose-700',
                default => 'bg-slate-100 text-slate-700',
            };
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee Number</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $employee->employee_number }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Position</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $employee->position_title }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date Employed</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $employee->date_employed?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employment Status</p>
                <p class="mt-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ $employee->employment_status_label }}
                    </span>
                </p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Salary Net</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ number_format((float) $employee->salary_net, 2) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Personal Details</h3>
                    <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Full Name</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->full_name }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Gender</p>
                            <p class="mt-1 font-medium text-slate-900">{{ ucfirst($employee->gender) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date Of Birth</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->date_of_birth?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Marital Status</p>
                            <p class="mt-1 font-medium text-slate-900">{{ ucwords(str_replace('_', ' ', $employee->marital_status)) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->phone_number }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                            <p class="mt-1 font-medium text-slate-900 break-all">{{ $employee->email }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 md:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->address }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Employment And Banking</h3>
                    <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Position</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->position_title }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contract Duration</p>
                            <p class="mt-1 font-medium text-slate-900">
                                @if($employee->contract_duration_months)
                                    {{ $employee->contract_duration_months }} month(s)
                                @else
                                    Open-ended
                                @endif
                            </p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contract End Date</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->contract_end_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Effective Date</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->status_effective_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bank Account Name</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->bank_account_name }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bank Account Number</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->bank_account_number }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bank Branch</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $employee->bank_branch }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">TIN / NSSF</p>
                            <p class="mt-1 font-medium text-slate-900">
                                TIN: {{ $employee->tin_number ?: '-' }}<br>
                                NSSF: {{ $employee->nssf_number ?: '-' }}
                            </p>
                        </div>
                        @if($employee->status_note)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 md:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Note</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $employee->status_note }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Next Of Kin</h3>
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($employee->nextOfKins as $kin)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $kin->is_primary ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ $kin->is_primary ? 'Primary' : 'Secondary' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $kin->full_name }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $kin->phone_number }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $kin->address }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No next-of-kin data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Status History</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($employee->statusHistories as $history)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">
                                <p class="font-semibold text-slate-900">
                                    {{ ucfirst(str_replace('_', ' ', $history->from_status ?? 'new')) }}
                                    to
                                    {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $history->changedBy?->name ?? 'System' }} on {{ $history->created_at?->format('d M Y, h:i A') ?? '-' }}
                                    @if($history->effective_date)
                                        , effective {{ $history->effective_date->format('d M Y') }}
                                    @endif
                                </p>
                                @if($history->remarks)
                                    <p class="mt-2 text-slate-700">{{ $history->remarks }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No status history recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Passport Photo</h3>
                    <div class="mt-4 flex justify-center">
                        <div class="employee-passport-frame">
                            @if($photoAvailable)
                                <img src="{{ route('hr.employees.photo', $employee->encrypted_id) }}"
                                     alt="{{ $employee->full_name }}"
                                     class="employee-passport-image">
                            @else
                                <div class="employee-passport-empty">No photo uploaded</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">CV Document</h3>
                    @if($employee->cv_path)
                        <div class="mt-4 space-y-2">
                            @if($cvPreviewable)
                                <a href="{{ route('hr.employees.cv.preview', $employee->encrypted_id) }}"
                                   target="_blank"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 transition-all hover:bg-indigo-100">
                                    Preview CV
                                </a>
                            @endif
                            <a href="{{ route('hr.employees.cv.download', $employee->encrypted_id) }}"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                Download CV
                            </a>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-500">No CV uploaded.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Certificates</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($employee->certificates as $certificate)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                <p class="text-sm font-semibold text-slate-900">{{ $certificate->certificate_name ?: 'Certificate '.$certificate->id }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ strtoupper(pathinfo($certificate->file_path, PATHINFO_EXTENSION)) }}{{ is_numeric($certificate->file_size) ? ' | '.number_format($certificate->file_size / 1024, 1).' KB' : '' }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if($certificate->is_previewable)
                                        <a href="{{ route('hr.employees.certificates.preview', [$employee->encrypted_id, $certificate->encrypted_id]) }}"
                                           target="_blank"
                                           class="rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 transition-all hover:bg-indigo-100">
                                            Preview
                                        </a>
                                    @endif
                                    <a href="{{ route('hr.employees.certificates.download', [$employee->encrypted_id, $certificate->encrypted_id]) }}"
                                       class="rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                        Download
                                    </a>
                                    @if($canManage)
                                        <form action="{{ route('hr.employees.certificates.destroy', [$employee->encrypted_id, $certificate->encrypted_id]) }}" method="POST" onsubmit="return confirm('Delete this certificate?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 transition-all hover:bg-rose-100">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No certificates uploaded.</p>
                        @endforelse
                    </div>
                </div>

                @if($canManage)
                    <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Status Management</h3>
                                <p class="mt-1 text-xs text-slate-500">Update status for suspension, termination, contract end, and reactivation.</p>
                            </div>
                            <button type="button"
                                    @click="openStatusModal = true"
                                    class="rounded-lg bg-[var(--nmis-primary)] px-3 py-1.5 text-xs font-semibold text-white transition-all hover:bg-[var(--nmis-secondary)]">
                                Change Status
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($canManage)
            <div x-cloak
                 x-show="openStatusModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="openStatusModal = false"></div>
                <div class="relative z-10 w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h4 class="text-sm font-semibold text-slate-900">Update Employee Status</h4>
                        <button type="button" @click="openStatusModal = false" class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Close</button>
                    </div>
                    <form action="{{ route('hr.employees.status.update', $employee->encrypted_id) }}" method="POST" class="space-y-4 p-5" onsubmit="return confirm('Save this status change?')">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Status <span class="text-rose-500">*</span></label>
                            <select name="employment_status" required
                                    class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                                @foreach($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}" @selected($employee->employment_status === $statusOption)>{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Effective Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="status_effective_date" required value="{{ now()->toDateString() }}"
                                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Reason / Note</label>
                            <textarea name="status_note" rows="3"
                                      class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20"
                                      placeholder="e.g. Contract terminated due to completion period"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-slate-200 pt-3">
                            <button type="button" @click="openStatusModal = false"
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-[var(--nmis-primary)] px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-[var(--nmis-secondary)]">
                                Save Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .employee-passport-frame {
            width: min(100%, 240px);
            aspect-ratio: 3 / 4;
            overflow: hidden;
            border: 1px solid rgb(226 232 240);
            border-radius: 0.75rem;
            background: rgb(248 250 252);
            box-shadow: 0 10px 25px -15px rgb(15 23 42 / 0.45);
        }

        .employee-passport-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
        }

        .employee-passport-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 0.875rem;
            color: rgb(100 116 139);
            padding: 1rem;
        }
    </style>
</x-app-layout>
