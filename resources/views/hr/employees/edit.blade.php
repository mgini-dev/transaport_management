<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Edit Employee</h2>
                <p class="mt-2 text-sm text-slate-500">Update profile, contract details, and upload additional documents.</p>
            </div>
            <a href="{{ route('hr.employees.show', $employee->encrypted_id) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Details
            </a>
        </div>
    </x-slot>

    <form action="{{ route('hr.employees.update', $employee->encrypted_id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('hr.employees._form', [
            'mode' => 'edit',
            'employee' => $employee,
            'genderOptions' => $genderOptions,
            'maritalStatusOptions' => $maritalStatusOptions,
            'employmentStatusOptions' => $employmentStatusOptions,
        ])

        @if($employee->certificates->isNotEmpty())
            <div class="rounded-xl border border-slate-200/70 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Existing Certificates</h3>
                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Certificate</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Uploaded</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($employee->certificates as $certificate)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $certificate->certificate_name ?: 'Certificate '.$certificate->id }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ strtoupper(pathinfo($certificate->file_path, PATHINFO_EXTENSION)) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $certificate->created_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            @if($certificate->is_previewable)
                                                <a href="{{ route('hr.employees.certificates.preview', [$employee->encrypted_id, $certificate->encrypted_id]) }}"
                                                   target="_blank"
                                                   class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition-all hover:bg-indigo-100">
                                                    Preview
                                                </a>
                                            @endif
                                            <a href="{{ route('hr.employees.certificates.download', [$employee->encrypted_id, $certificate->encrypted_id]) }}"
                                               class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                                Download
                                            </a>
                                            <form action="{{ route('hr.employees.certificates.destroy', [$employee->encrypted_id, $certificate->encrypted_id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this certificate?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-100">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('hr.employees.show', $employee->encrypted_id) }}"
               class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 transition-all hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Update Employee
            </button>
        </div>
    </form>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-app-layout>

