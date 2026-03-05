<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeCertificate;
use App\Models\EmployeeNextOfKin;
use App\Models\EmployeeStatusHistory;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());

        $employees = Employee::query()
            ->with([
                'nextOfKins' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
            ])
            ->withCount('certificates')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%");
                });
            })
            ->when($status !== '' && in_array($status, Employee::EMPLOYMENT_STATUSES, true), fn ($query) => $query->where('employment_status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = Employee::query();
        $today = now()->toDateString();
        $contractsEndingSoon = (clone $statsQuery)
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [$today, now()->addDays(45)->toDateString()])
            ->count();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('employment_status', 'active')->count(),
            'terminated' => (clone $statsQuery)->whereIn('employment_status', ['terminated', 'resigned', 'contract_expired'])->count(),
            'contracts_ending_soon' => $contractsEndingSoon,
        ];

        return view('hr.employees.index', [
            'employees' => $employees,
            'stats' => $stats,
            'search' => $search,
            'activeStatus' => $status,
            'statusOptions' => Employee::EMPLOYMENT_STATUSES,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('hr.employees.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $this->validateEmployee($request);
        $actor = $request->user();
        $storedPaths = [];
        $employee = null;

        try {
            DB::transaction(function () use ($request, $data, $actor, &$employee, &$storedPaths): void {
                $payload = $this->employeePayload($data);
                $payload['employee_number'] = $this->generateEmployeeNumber();
                $payload['created_by'] = $actor?->id;
                $payload['updated_by'] = $actor?->id;

                $employee = Employee::query()->create($payload);

                if ($request->hasFile('photo')) {
                    $storedPaths[] = $this->storeFile($request->file('photo'), "employees/{$employee->id}/photo");
                    $employee->photo_path = end($storedPaths);
                }

                if ($request->hasFile('cv')) {
                    $storedPaths[] = $this->storeFile($request->file('cv'), "employees/{$employee->id}/cv");
                    $employee->cv_path = end($storedPaths);
                }

                $employee->save();

                $this->syncNextOfKins($employee, $data);
                $this->storeCertificates($employee, $request, $storedPaths);

                EmployeeStatusHistory::query()->create([
                    'employee_id' => $employee->id,
                    'from_status' => null,
                    'to_status' => $employee->employment_status,
                    'remarks' => $employee->status_note ?: 'Employee registered.',
                    'effective_date' => $employee->status_effective_date ?: $employee->date_employed,
                    'changed_by' => $actor?->id,
                ]);
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }

        if (! $employee) {
            throw new RuntimeException('Employee could not be created.');
        }

        $this->auditLogService->record(
            action: 'employee.created',
            user: $actor,
            loggable: $employee,
            context: [
                'employee_number' => $employee->employee_number,
                'employment_status' => $employee->employment_status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('hr.employees.show', $employee->encrypted_id)
            ->with('status', "Employee {$employee->employee_number} registered successfully.");
    }

    public function show(string $employeeId): View
    {
        $employee = $this->resolveEmployee($employeeId)
            ->load([
                'nextOfKins',
                'certificates' => fn ($query) => $query->latest('id'),
                'statusHistories.changedBy:id,name',
                'creator:id,name',
                'updater:id,name',
            ]);

        $this->authorize('view', $employee);

        return view('hr.employees.show', [
            'employee' => $employee,
            'statusOptions' => Employee::EMPLOYMENT_STATUSES,
            'canManage' => auth()->user()?->can('update', $employee) ?? false,
            'cvPreviewable' => $this->isPreviewableFile($employee->cv_path),
            'photoAvailable' => $employee->photo_path && Storage::disk('private')->exists($employee->photo_path),
        ]);
    }

    public function downloadFullDocument(Request $request, string $employeeId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId)
            ->load([
                'nextOfKins' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
                'certificates' => fn ($query) => $query->latest('id'),
                'statusHistories.changedBy:id,name',
                'creator:id,name',
                'updater:id,name',
            ]);
        $this->authorize('view', $employee);

        $company = $this->companyProfile();
        $attachments = $this->documentAttachments($employee);

        $html = view('hr.employees.full_document_pdf', [
            'employee' => $employee,
            'company' => $company,
            'generatedAt' => now(),
            'photoDataUri' => $this->imageDataUriFromPrivateDisk($employee->photo_path, 3_200_000),
            'attachments' => $attachments,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $this->auditLogService->record(
            action: 'employee.document.downloaded',
            user: $request->user(),
            loggable: $employee,
            context: [
                'employee_number' => $employee->employee_number,
                'attachments_count' => count($attachments),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $filename = 'employee-file-'.$employee->employee_number.'.pdf';

        return response()->streamDownload(
            static function () use ($dompdf): void {
                echo $dompdf->output();
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function edit(string $employeeId): View
    {
        $employee = $this->resolveEmployee($employeeId)->load(['nextOfKins', 'certificates' => fn ($query) => $query->latest('id')]);
        $this->authorize('update', $employee);

        return view('hr.employees.edit', [
            'employee' => $employee,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, string $employeeId): RedirectResponse
    {
        $employee = $this->resolveEmployee($employeeId)->load('nextOfKins');
        $this->authorize('update', $employee);

        $data = $this->validateEmployee($request, $employee);
        $actor = $request->user();
        $storedPaths = [];
        $oldPhotoPath = $employee->photo_path;
        $oldCvPath = $employee->cv_path;
        $fromStatus = $employee->employment_status;

        try {
            DB::transaction(function () use ($request, $employee, $data, $actor, &$storedPaths, $fromStatus): void {
                $payload = $this->employeePayload($data);
                $payload['updated_by'] = $actor?->id;

                if ($request->hasFile('photo')) {
                    $storedPaths[] = $this->storeFile($request->file('photo'), "employees/{$employee->id}/photo");
                    $payload['photo_path'] = end($storedPaths);
                }

                if ($request->hasFile('cv')) {
                    $storedPaths[] = $this->storeFile($request->file('cv'), "employees/{$employee->id}/cv");
                    $payload['cv_path'] = end($storedPaths);
                }

                $employee->update($payload);
                $this->syncNextOfKins($employee, $data);
                $this->storeCertificates($employee, $request, $storedPaths);

                if ($fromStatus !== $employee->employment_status) {
                    EmployeeStatusHistory::query()->create([
                        'employee_id' => $employee->id,
                        'from_status' => $fromStatus,
                        'to_status' => $employee->employment_status,
                        'remarks' => $employee->status_note ?: 'Status updated during profile edit.',
                        'effective_date' => $employee->status_effective_date ?: now()->toDateString(),
                        'changed_by' => $actor?->id,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }

        if ($oldPhotoPath && $employee->photo_path && $oldPhotoPath !== $employee->photo_path) {
            Storage::disk('private')->delete($oldPhotoPath);
        }

        if ($oldCvPath && $employee->cv_path && $oldCvPath !== $employee->cv_path) {
            Storage::disk('private')->delete($oldCvPath);
        }

        $this->auditLogService->record(
            action: 'employee.updated',
            user: $actor,
            loggable: $employee->refresh(),
            context: [
                'employee_number' => $employee->employee_number,
                'employment_status' => $employee->employment_status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('hr.employees.show', $employee->encrypted_id)
            ->with('status', "Employee {$employee->employee_number} updated successfully.");
    }

    public function updateStatus(Request $request, string $employeeId): RedirectResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('updateStatus', $employee);

        $data = $request->validate([
            'employment_status' => ['required', Rule::in(Employee::EMPLOYMENT_STATUSES)],
            'status_effective_date' => ['required', 'date'],
            'status_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $fromStatus = $employee->employment_status;
        $toStatus = (string) $data['employment_status'];
        $effectiveDate = Carbon::parse((string) $data['status_effective_date'])->toDateString();

        if ($fromStatus === $toStatus && $employee->status_effective_date?->toDateString() === $effectiveDate) {
            return back()->with('status', 'Employee status remains unchanged.');
        }

        $employee->update([
            'employment_status' => $toStatus,
            'status_effective_date' => $effectiveDate,
            'status_note' => $data['status_note'] ?? null,
            'terminated_at' => in_array($toStatus, ['terminated', 'resigned', 'contract_expired'], true) ? now() : null,
            'updated_by' => $request->user()?->id,
        ]);

        EmployeeStatusHistory::query()->create([
            'employee_id' => $employee->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'remarks' => $data['status_note'] ?? null,
            'effective_date' => $effectiveDate,
            'changed_by' => $request->user()?->id,
        ]);

        $this->auditLogService->record(
            action: 'employee.status.updated',
            user: $request->user(),
            loggable: $employee,
            context: [
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'status_effective_date' => $effectiveDate,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', "Employee status updated to {$employee->employment_status_label}.");
    }

    public function photo(string $employeeId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('view', $employee);
        abort_unless($employee->photo_path, 404, 'Employee photo not found.');

        return $this->inlineFileResponse($employee->photo_path, $employee->employee_number.'-photo');
    }

    public function previewCv(string $employeeId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('view', $employee);
        abort_unless($employee->cv_path, 404, 'Employee CV not found.');

        return $this->inlineFileResponse($employee->cv_path, $employee->employee_number.'-cv', true);
    }

    public function downloadCv(string $employeeId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('view', $employee);
        abort_unless($employee->cv_path, 404, 'Employee CV not found.');

        return $this->downloadFileResponse($employee->cv_path, $employee->employee_number.'-cv');
    }

    public function previewCertificate(string $employeeId, string $certificateId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('view', $employee);
        $certificate = $this->resolveCertificate($employee, $certificateId);

        return $this->inlineFileResponse($certificate->file_path, $this->certificateBaseName($employee, $certificate), true);
    }

    public function downloadCertificate(string $employeeId, string $certificateId): StreamedResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('view', $employee);
        $certificate = $this->resolveCertificate($employee, $certificateId);

        return $this->downloadFileResponse($certificate->file_path, $this->certificateBaseName($employee, $certificate));
    }

    public function destroyCertificate(Request $request, string $employeeId, string $certificateId): RedirectResponse
    {
        $employee = $this->resolveEmployee($employeeId);
        $this->authorize('update', $employee);
        $certificate = $this->resolveCertificate($employee, $certificateId);

        Storage::disk('private')->delete($certificate->file_path);
        $certificate->delete();

        $this->auditLogService->record(
            action: 'employee.certificate.deleted',
            user: $request->user(),
            loggable: $employee,
            context: [
                'employee_number' => $employee->employee_number,
                'certificate_id' => $certificate->id,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Certificate removed successfully.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $employeeId = $employee?->id;
        $photoPresenceRule = $employee?->photo_path ? 'nullable' : 'required';
        $cvPresenceRule = $employee?->cv_path ? 'nullable' : 'required';

        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employeeId)],
            'address' => ['required', 'string', 'max:2000'],
            'gender' => ['required', Rule::in(Employee::GENDERS)],
            'marital_status' => ['required', Rule::in(Employee::MARITAL_STATUSES)],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'position_title' => ['required', 'string', 'max:150'],
            'date_employed' => ['required', 'date'],
            'contract_duration_months' => ['nullable', 'integer', 'min:1', 'max:600'],
            'bank_account_name' => ['required', 'string', 'max:150'],
            'bank_account_number' => ['required', 'string', 'max:80'],
            'bank_branch' => ['required', 'string', 'max:150'],
            'salary_net' => ['required', 'numeric', 'min:0'],
            'tin_number' => ['nullable', 'string', 'max:80'],
            'nssf_number' => ['nullable', 'string', 'max:80'],
            'employment_status' => ['required', Rule::in(Employee::EMPLOYMENT_STATUSES)],
            'status_effective_date' => ['nullable', 'date'],
            'status_note' => ['nullable', 'string', 'max:2000'],
            'next_of_kin_primary.first_name' => ['required', 'string', 'max:100'],
            'next_of_kin_primary.middle_name' => ['nullable', 'string', 'max:100'],
            'next_of_kin_primary.last_name' => ['required', 'string', 'max:100'],
            'next_of_kin_primary.phone_number' => ['required', 'string', 'max:30'],
            'next_of_kin_primary.address' => ['required', 'string', 'max:2000'],
            'next_of_kin_secondary.first_name' => ['nullable', 'string', 'max:100', 'required_with:next_of_kin_secondary.last_name,next_of_kin_secondary.phone_number,next_of_kin_secondary.address'],
            'next_of_kin_secondary.middle_name' => ['nullable', 'string', 'max:100'],
            'next_of_kin_secondary.last_name' => ['nullable', 'string', 'max:100', 'required_with:next_of_kin_secondary.first_name,next_of_kin_secondary.phone_number,next_of_kin_secondary.address'],
            'next_of_kin_secondary.phone_number' => ['nullable', 'string', 'max:30', 'required_with:next_of_kin_secondary.first_name,next_of_kin_secondary.last_name,next_of_kin_secondary.address'],
            'next_of_kin_secondary.address' => ['nullable', 'string', 'max:2000', 'required_with:next_of_kin_secondary.first_name,next_of_kin_secondary.last_name,next_of_kin_secondary.phone_number'],
            'photo' => [$photoPresenceRule, 'file', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'cv' => [$cvPresenceRule, 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'certificates' => ['nullable', 'array', 'max:15'],
            'certificates.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function employeePayload(array $data): array
    {
        $dateEmployed = Carbon::parse((string) $data['date_employed']);
        $contractDurationMonths = filled($data['contract_duration_months'] ?? null)
            ? (int) $data['contract_duration_months']
            : null;
        $contractEndDate = $contractDurationMonths
            ? $dateEmployed->copy()->addMonthsNoOverflow($contractDurationMonths)->toDateString()
            : null;

        $status = (string) $data['employment_status'];

        return [
            'first_name' => trim((string) $data['first_name']),
            'middle_name' => filled($data['middle_name'] ?? null) ? trim((string) $data['middle_name']) : null,
            'last_name' => trim((string) $data['last_name']),
            'phone_number' => trim((string) $data['phone_number']),
            'email' => strtolower(trim((string) $data['email'])),
            'address' => trim((string) $data['address']),
            'gender' => $data['gender'],
            'marital_status' => $data['marital_status'],
            'date_of_birth' => Carbon::parse((string) $data['date_of_birth'])->toDateString(),
            'position_title' => trim((string) $data['position_title']),
            'date_employed' => $dateEmployed->toDateString(),
            'contract_duration_months' => $contractDurationMonths,
            'contract_end_date' => $contractEndDate,
            'bank_account_name' => trim((string) $data['bank_account_name']),
            'bank_account_number' => trim((string) $data['bank_account_number']),
            'bank_branch' => trim((string) $data['bank_branch']),
            'salary_net' => (float) $data['salary_net'],
            'tin_number' => filled($data['tin_number'] ?? null) ? trim((string) $data['tin_number']) : null,
            'nssf_number' => filled($data['nssf_number'] ?? null) ? trim((string) $data['nssf_number']) : null,
            'employment_status' => $status,
            'status_effective_date' => filled($data['status_effective_date'] ?? null)
                ? Carbon::parse((string) $data['status_effective_date'])->toDateString()
                : $dateEmployed->toDateString(),
            'status_note' => filled($data['status_note'] ?? null) ? trim((string) $data['status_note']) : null,
            'terminated_at' => in_array($status, ['terminated', 'resigned', 'contract_expired'], true) ? now() : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncNextOfKins(Employee $employee, array $data): void
    {
        $primary = $data['next_of_kin_primary'] ?? [];
        $secondary = $data['next_of_kin_secondary'] ?? [];

        $rows = [[
            'is_primary' => true,
            'first_name' => trim((string) ($primary['first_name'] ?? '')),
            'middle_name' => filled($primary['middle_name'] ?? null) ? trim((string) $primary['middle_name']) : null,
            'last_name' => trim((string) ($primary['last_name'] ?? '')),
            'phone_number' => trim((string) ($primary['phone_number'] ?? '')),
            'address' => trim((string) ($primary['address'] ?? '')),
        ]];

        $hasSecondary = filled($secondary['first_name'] ?? null)
            || filled($secondary['last_name'] ?? null)
            || filled($secondary['phone_number'] ?? null)
            || filled($secondary['address'] ?? null)
            || filled($secondary['middle_name'] ?? null);

        if ($hasSecondary) {
            $rows[] = [
                'is_primary' => false,
                'first_name' => trim((string) ($secondary['first_name'] ?? '')),
                'middle_name' => filled($secondary['middle_name'] ?? null) ? trim((string) $secondary['middle_name']) : null,
                'last_name' => trim((string) ($secondary['last_name'] ?? '')),
                'phone_number' => trim((string) ($secondary['phone_number'] ?? '')),
                'address' => trim((string) ($secondary['address'] ?? '')),
            ];
        }

        $employee->nextOfKins()->delete();
        foreach ($rows as $row) {
            $employee->nextOfKins()->create($row);
        }
    }

    /**
     * @param  array<int, string>  $storedPaths
     */
    private function storeCertificates(Employee $employee, Request $request, array &$storedPaths): void
    {
        $certificateFiles = $request->file('certificates', []);
        if (! is_array($certificateFiles) || empty($certificateFiles)) {
            return;
        }

        foreach ($certificateFiles as $certificateFile) {
            if (! $certificateFile instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeFile($certificateFile, "employees/{$employee->id}/certificates");
            $storedPaths[] = $path;

            $employee->certificates()->create([
                'certificate_name' => pathinfo($certificateFile->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path' => $path,
                'mime_type' => $certificateFile->getMimeType(),
                'file_size' => $certificateFile->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function generateEmployeeNumber(): string
    {
        $used = [];

        Employee::query()
            ->select('employee_number')
            ->where('employee_number', 'like', 'NEX%')
            ->lockForUpdate()
            ->pluck('employee_number')
            ->each(function (string $employeeNumber) use (&$used): void {
                if (preg_match('/^NEX(\d{4})$/', $employeeNumber, $matches) === 1) {
                    $used[(int) $matches[1]] = true;
                }
            });

        for ($sequence = 1; $sequence <= 9999; $sequence++) {
            if (! isset($used[$sequence])) {
                return sprintf('NEX%04d', $sequence);
            }
        }

        throw new RuntimeException('Employee number range exhausted. No new NEX number is available.');
    }

    private function storeFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'private');
    }

    private function resolveEmployee(string $employeeId): Employee
    {
        $decodedId = EncryptedId::decode($employeeId);
        abort_unless($decodedId, 404, 'Employee not found.');

        return Employee::query()->findOrFail($decodedId);
    }

    private function resolveCertificate(Employee $employee, string $certificateId): EmployeeCertificate
    {
        $decodedId = EncryptedId::decode($certificateId);
        abort_unless($decodedId, 404, 'Certificate not found.');

        return EmployeeCertificate::query()
            ->whereKey($decodedId)
            ->where('employee_id', $employee->id)
            ->firstOrFail();
    }

    private function isPreviewableFile(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $disk = Storage::disk('private');
        if (! $disk->exists($path)) {
            return false;
        }

        $mimeType = $disk->mimeType($path) ?: '';
        return $mimeType === 'application/pdf' || str_starts_with($mimeType, 'image/');
    }

    private function inlineFileResponse(string $path, string $baseName, bool $previewOnly = false): StreamedResponse
    {
        $disk = Storage::disk('private');
        abort_unless($disk->exists($path), 404, 'Requested file is not available.');

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        if ($previewOnly) {
            abort_unless(
                $mimeType === 'application/pdf' || str_starts_with($mimeType, 'image/'),
                415,
                'Preview is available only for PDF and image files.'
            );
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $filename = $baseName.($extension ? '.'.$extension : '');

        return $disk->response(
            $path,
            $filename,
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ],
            'inline'
        );
    }

    private function downloadFileResponse(string $path, string $baseName): StreamedResponse
    {
        $disk = Storage::disk('private');
        abort_unless($disk->exists($path), 404, 'Requested file is not available.');

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $filename = $baseName.($extension ? '.'.$extension : '');

        return $disk->download($path, $filename);
    }

    /**
     * @return array{name: string, address: string, phone: string, email: string, website: string, logo_data_uri: string|null}
     */
    private function companyProfile(): array
    {
        $logoPath = public_path('images/nexus-logo.png');
        $logoDataUri = null;

        if (is_file($logoPath)) {
            $mimeType = mime_content_type($logoPath) ?: 'image/png';
            $contents = @file_get_contents($logoPath);
            if ($contents !== false) {
                $logoDataUri = 'data:'.$mimeType.';base64,'.base64_encode($contents);
            }
        }

        return [
            'name' => (string) config('app.company_name', config('app.name', 'NexusFlow')),
            'address' => (string) config('app.company_address', ''),
            'phone' => (string) config('app.company_phone', ''),
            'email' => (string) config('app.company_email', ''),
            'website' => (string) config('app.company_website', ''),
            'logo_data_uri' => $logoDataUri,
        ];
    }

    /**
     * @return array<int, array{title: string, category: string, file_name: string, file_extension: string, file_size: string, exists: bool, mime_type: string, notes: string, image_data_uri: string|null}>
     */
    private function documentAttachments(Employee $employee): array
    {
        $attachments = [];

        if ($employee->cv_path) {
            $attachments[] = $this->buildAttachmentEntry(
                title: 'Curriculum Vitae (CV)',
                category: 'CV',
                path: (string) $employee->cv_path,
                notes: 'Primary employee CV document.'
            );
        }

        foreach ($employee->certificates as $certificate) {
            $attachments[] = $this->buildAttachmentEntry(
                title: filled($certificate->certificate_name) ? trim((string) $certificate->certificate_name) : 'Certificate '.$certificate->id,
                category: 'Certificate',
                path: (string) $certificate->file_path,
                notes: 'Supporting certificate attachment.'
            );
        }

        return $attachments;
    }

    /**
     * @return array{title: string, category: string, file_name: string, file_extension: string, file_size: string, exists: bool, mime_type: string, notes: string, image_data_uri: string|null}
     */
    private function buildAttachmentEntry(string $title, string $category, string $path, string $notes): array
    {
        $disk = Storage::disk('private');
        $exists = $path !== '' && $disk->exists($path);
        $mimeType = $exists ? (string) ($disk->mimeType($path) ?: 'application/octet-stream') : 'missing/file';
        $sizeBytes = $exists ? $disk->size($path) : null;
        $imageDataUri = $exists ? $this->imageDataUriFromPrivateDisk($path, 3_200_000) : null;

        return [
            'title' => $title,
            'category' => $category,
            'file_name' => basename($path),
            'file_extension' => strtoupper(pathinfo($path, PATHINFO_EXTENSION) ?: '-'),
            'file_size' => is_int($sizeBytes) ? $this->formatBytes($sizeBytes) : 'Unavailable',
            'exists' => $exists,
            'mime_type' => $mimeType,
            'notes' => $notes,
            'image_data_uri' => $imageDataUri,
        ];
    }

    private function imageDataUriFromPrivateDisk(?string $path, int $maxBytes = 3_200_000): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('private');
        if (! $disk->exists($path)) {
            return null;
        }

        $mimeType = $disk->mimeType($path) ?: '';
        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        $size = $disk->size($path);
        if (! is_int($size) || $size < 1 || $size > $maxBytes) {
            return null;
        }

        $contents = $disk->get($path);
        if ($contents === '') {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2).' MB';
        }

        return number_format($bytes / 1024, 1).' KB';
    }

    private function certificateBaseName(Employee $employee, EmployeeCertificate $certificate): string
    {
        $label = filled($certificate->certificate_name)
            ? Str::slug((string) $certificate->certificate_name)
            : 'certificate-'.$certificate->id;

        return $employee->employee_number.'-'.$label;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'genderOptions' => Employee::GENDERS,
            'maritalStatusOptions' => Employee::MARITAL_STATUSES,
            'employmentStatusOptions' => Employee::EMPLOYMENT_STATUSES,
        ];
    }
}
