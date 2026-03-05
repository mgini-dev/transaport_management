<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Register Employee</h2>
                <p class="mt-2 text-sm text-slate-500">Capture full employee profile, next of kin, contract, and documents.</p>
            </div>
            <a href="{{ route('hr.employees.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Employees
            </a>
        </div>
    </x-slot>

    <form action="{{ route('hr.employees.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('hr.employees._form', [
            'mode' => 'create',
            'employee' => null,
            'genderOptions' => $genderOptions,
            'maritalStatusOptions' => $maritalStatusOptions,
            'employmentStatusOptions' => $employmentStatusOptions,
        ])

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('hr.employees.index') }}"
               class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--nmis-primary)] to-[var(--nmis-secondary)] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[var(--nmis-primary)]/20 transition-all hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Save Employee
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

