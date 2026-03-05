@php
    /** @var \App\Models\Employee|null $employee */
    $employee = $employee ?? null;
    $mode = $mode ?? 'create';

    $primaryKinModel = $employee?->nextOfKins->firstWhere('is_primary', true);
    $secondaryKinModel = $employee?->nextOfKins->firstWhere('is_primary', false);

    $primaryKin = old('next_of_kin_primary', [
        'first_name' => $primaryKinModel->first_name ?? '',
        'middle_name' => $primaryKinModel->middle_name ?? '',
        'last_name' => $primaryKinModel->last_name ?? '',
        'phone_number' => $primaryKinModel->phone_number ?? '',
        'address' => $primaryKinModel->address ?? '',
    ]);

    $secondaryKin = old('next_of_kin_secondary', [
        'first_name' => $secondaryKinModel->first_name ?? '',
        'middle_name' => $secondaryKinModel->middle_name ?? '',
        'last_name' => $secondaryKinModel->last_name ?? '',
        'phone_number' => $secondaryKinModel->phone_number ?? '',
        'address' => $secondaryKinModel->address ?? '',
    ]);
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-slate-200/70 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Personal Information</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name <span class="text-rose-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $employee?->middle_name) }}"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name <span class="text-rose-500">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Phone Number <span class="text-rose-500">*</span></label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $employee?->phone_number) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $employee?->email) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Date Of Birth <span class="text-rose-500">*</span></label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee?->date_of_birth?->toDateString()) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Gender <span class="text-rose-500">*</span></label>
                <select name="gender" required
                        class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                    <option value="">Select Gender</option>
                    @foreach($genderOptions as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $employee?->gender) === $gender)>{{ ucfirst($gender) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Marital Status <span class="text-rose-500">*</span></label>
                <select name="marital_status" required
                        class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                    <option value="">Select Marital Status</option>
                    @foreach($maritalStatusOptions as $status)
                        <option value="{{ $status }}" @selected(old('marital_status', $employee?->marital_status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Address <span class="text-rose-500">*</span></label>
                <textarea name="address" rows="3" required
                          class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">{{ old('address', $employee?->address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-emerald-200/70 bg-emerald-50/40 p-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Next Of Kin 1 (Mandatory)</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-700">First Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="next_of_kin_primary[first_name]" value="{{ $primaryKin['first_name'] ?? '' }}" required
                           class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-700">Middle Name</label>
                    <input type="text" name="next_of_kin_primary[middle_name]" value="{{ $primaryKin['middle_name'] ?? '' }}"
                           class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-700">Last Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="next_of_kin_primary[last_name]" value="{{ $primaryKin['last_name'] ?? '' }}" required
                           class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-700">Phone <span class="text-rose-500">*</span></label>
                    <input type="text" name="next_of_kin_primary[phone_number]" value="{{ $primaryKin['phone_number'] ?? '' }}" required
                           class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-emerald-700">Address <span class="text-rose-500">*</span></label>
                    <textarea name="next_of_kin_primary[address]" rows="2" required
                              class="w-full rounded-xl border-emerald-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">{{ $primaryKin['address'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/70 bg-slate-50 p-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Next Of Kin 2 (Optional)</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                    <input type="text" name="next_of_kin_secondary[first_name]" value="{{ $secondaryKin['first_name'] ?? '' }}"
                           class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                    <input type="text" name="next_of_kin_secondary[middle_name]" value="{{ $secondaryKin['middle_name'] ?? '' }}"
                           class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                    <input type="text" name="next_of_kin_secondary[last_name]" value="{{ $secondaryKin['last_name'] ?? '' }}"
                           class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Phone</label>
                    <input type="text" name="next_of_kin_secondary[phone_number]" value="{{ $secondaryKin['phone_number'] ?? '' }}"
                           class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Address</label>
                    <textarea name="next_of_kin_secondary[address]" rows="2"
                              class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">{{ $secondaryKin['address'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/70 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Employment And Financial Information</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Position Employed For <span class="text-rose-500">*</span></label>
                <input type="text" name="position_title" value="{{ old('position_title', $employee?->position_title) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Date Employed <span class="text-rose-500">*</span></label>
                <input type="date" name="date_employed" value="{{ old('date_employed', $employee?->date_employed?->toDateString()) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Contract Duration (Months)</label>
                <input type="number" min="1" name="contract_duration_months" value="{{ old('contract_duration_months', $employee?->contract_duration_months) }}"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20"
                       placeholder="Leave blank for open-ended contract">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Employment Status <span class="text-rose-500">*</span></label>
                <select name="employment_status" required
                        class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
                    @foreach($employmentStatusOptions as $status)
                        <option value="{{ $status }}" @selected(old('employment_status', $employee?->employment_status ?? 'active') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Status Effective Date</label>
                <input type="date" name="status_effective_date" value="{{ old('status_effective_date', $employee?->status_effective_date?->toDateString() ?? $employee?->date_employed?->toDateString()) }}"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Salary Net Negotiated <span class="text-rose-500">*</span></label>
                <input type="number" min="0" step="0.01" name="salary_net" value="{{ old('salary_net', $employee?->salary_net) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Bank Account Name <span class="text-rose-500">*</span></label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $employee?->bank_account_name) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Bank Account Number <span class="text-rose-500">*</span></label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee?->bank_account_number) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Bank Branch <span class="text-rose-500">*</span></label>
                <input type="text" name="bank_branch" value="{{ old('bank_branch', $employee?->bank_branch) }}" required
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">TIN Number</label>
                <input type="text" name="tin_number" value="{{ old('tin_number', $employee?->tin_number) }}"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">NSSF Number</label>
                <input type="text" name="nssf_number" value="{{ old('nssf_number', $employee?->nssf_number) }}"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">
            </div>
            <div class="md:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Status Notes</label>
                <textarea name="status_note" rows="2"
                          class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20">{{ old('status_note', $employee?->status_note) }}</textarea>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/70 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Documents And Images</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Passport Photo {{ $mode === 'create' ? '(Required)' : '(Optional Replacement)' }}</label>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" {{ $mode === 'create' ? 'required' : '' }}
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                @if($employee?->photo_path)
                    <p class="mt-1 text-xs text-slate-500">Current photo is available in employee details page.</p>
                @endif
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">CV {{ $mode === 'create' ? '(Required)' : '(Optional Replacement)' }}</label>
                <input type="file" name="cv" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" {{ $mode === 'create' ? 'required' : '' }}
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                @if($employee?->cv_path)
                    <p class="mt-1 text-xs text-slate-500">Current CV is available in employee details page.</p>
                @endif
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Certificates (Optional, Multiple Allowed)</label>
                <input type="file" name="certificates[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                <p class="mt-1 text-xs text-slate-500">Upload one or more certificates. Each file max 10MB.</p>
            </div>
        </div>
    </div>
</div>
