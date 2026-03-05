<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee File - {{ $employee->employee_number }}</title>
    <style>
        @page {
            margin: 120px 38px 62px 38px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0f172a;
            line-height: 1.45;
        }

        header {
            position: fixed;
            top: -102px;
            left: 0;
            right: 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
        }

        footer {
            position: fixed;
            bottom: -44px;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            color: #475569;
            font-size: 9px;
            padding-top: 6px;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 14%;
            font-size: 56px;
            color: rgba(15, 23, 42, 0.05);
            transform: rotate(-29deg);
            z-index: -1000;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: top;
        }

        .logo-box {
            width: 78px;
        }

        .logo {
            width: 70px;
            height: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px;
            background: #ffffff;
        }

        .company-name {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }

        .company-line {
            margin: 2px 0;
            color: #334155;
            font-size: 10px;
        }

        .document-title {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .confidential-flag {
            float: right;
            display: inline-block;
            border: 1px solid #b91c1c;
            color: #991b1b;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .meta-strip {
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 6px;
        }

        .meta-strip span {
            display: inline-block;
            margin-right: 12px;
            margin-bottom: 4px;
            font-size: 10px;
            color: #334155;
        }

        .intro-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .intro-table td {
            border: 1px solid #e2e8f0;
            vertical-align: top;
            padding: 10px;
        }

        .intro-left {
            width: 76%;
            background: #ffffff;
        }

        .intro-right {
            width: 24%;
            background: #f8fafc;
            text-align: center;
        }

        .employee-name {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 700;
        }

        .employee-role {
            margin: 0;
            font-size: 11px;
            color: #334155;
        }

        .passport-frame {
            width: 132px;
            height: 176px;
            margin: 0 auto 6px auto;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            overflow: hidden;
            border-radius: 6px;
        }

        .passport-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
        }

        .passport-empty {
            width: 100%;
            height: 100%;
            display: table;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }

        .passport-empty span {
            display: table-cell;
            vertical-align: middle;
            padding: 0 8px;
        }

        .section {
            margin-bottom: 12px;
        }

        .section h3 {
            margin: 0 0 6px 0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            color: #1e293b;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 7px;
            vertical-align: top;
        }

        .info-table th {
            width: 24%;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #dbe3ee;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .table th {
            background: #f8fafc;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .muted {
            color: #475569;
        }

        .page-break {
            page-break-before: always;
        }

        .attachment-preview {
            margin-top: 10px;
            border: 1px solid #dbe3ee;
            border-radius: 6px;
            padding: 8px;
            background: #f8fafc;
            text-align: center;
        }

        .attachment-preview img {
            max-width: 100%;
            max-height: 610px;
            height: auto;
        }

        .attachment-note {
            margin-top: 10px;
            padding: 8px 10px;
            border: 1px solid #dbe3ee;
            background: #f8fafc;
            border-radius: 6px;
            color: #334155;
            font-size: 10px;
        }

        .small {
            font-size: 10px;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="watermark">CONFIDENTIAL</div>

    <header>
        <span class="confidential-flag">Confidential Document</span>
        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if(!empty($company['logo_data_uri']))
                        <img src="{{ $company['logo_data_uri'] }}" alt="NexusFlow Logo" class="logo">
                    @endif
                </td>
                <td>
                    <h1 class="company-name">{{ strtoupper($company['name']) }}</h1>
                    <p class="company-line">{{ $company['address'] ?: '-' }}</p>
                    <p class="company-line">
                        Phone: {{ $company['phone'] ?: '-' }} |
                        Email: {{ $company['email'] ?: '-' }} |
                        Website: {{ $company['website'] ?: '-' }}
                    </p>
                    <p class="document-title">Employee Master File</p>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div>
            Confidential HR record - Unauthorized sharing, copying, or disclosure is prohibited.
            Generated on {{ $generatedAt->format('d M Y H:i:s') }}.
        </div>
    </footer>

    <div class="meta-strip">
        <span><strong>Employee Number:</strong> {{ $employee->employee_number }}</span>
        <span><strong>Status:</strong> {{ $employee->employment_status_label }}</span>
        <span><strong>Date Employed:</strong> {{ $employee->date_employed?->format('d M Y') ?? '-' }}</span>
        <span><strong>Prepared By:</strong> {{ auth()->user()?->name ?? 'System' }}</span>
    </div>

    <table class="intro-table">
        <tr>
            <td class="intro-left">
                <p class="employee-name">{{ $employee->full_name }}</p>
                <p class="employee-role">{{ $employee->position_title }}</p>
                <p class="small">
                    Contact: {{ $employee->phone_number }} | {{ $employee->email }}<br>
                    Address: {{ $employee->address }}
                </p>
            </td>
            <td class="intro-right">
                <div class="passport-frame">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="{{ $employee->full_name }}">
                    @else
                        <div class="passport-empty"><span>No passport photo uploaded</span></div>
                    @endif
                </div>
                <div class="small"><strong>Passport Photo</strong></div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h3>Personal Information</h3>
        <table class="info-table">
            <tr>
                <th>First Name</th>
                <td>{{ $employee->first_name }}</td>
                <th>Middle Name</th>
                <td>{{ $employee->middle_name ?: '-' }}</td>
            </tr>
            <tr>
                <th>Last Name</th>
                <td>{{ $employee->last_name }}</td>
                <th>Gender</th>
                <td>{{ ucfirst($employee->gender) }}</td>
            </tr>
            <tr>
                <th>Date Of Birth</th>
                <td>{{ $employee->date_of_birth?->format('d M Y') ?? '-' }}</td>
                <th>Marital Status</th>
                <td>{{ ucwords(str_replace('_', ' ', $employee->marital_status)) }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $employee->phone_number }}</td>
                <th>Email</th>
                <td>{{ $employee->email }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $employee->address }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Employment And Banking</h3>
        <table class="info-table">
            <tr>
                <th>Position</th>
                <td>{{ $employee->position_title }}</td>
                <th>Date Employed</th>
                <td>{{ $employee->date_employed?->format('d M Y') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Contract Duration</th>
                <td>{{ $employee->contract_duration_months ? $employee->contract_duration_months.' month(s)' : 'Open-ended' }}</td>
                <th>Contract End Date</th>
                <td>{{ $employee->contract_end_date?->format('d M Y') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Salary Net</th>
                <td>{{ number_format((float) $employee->salary_net, 2) }}</td>
                <th>Status Effective Date</th>
                <td>{{ $employee->status_effective_date?->format('d M Y') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Bank Account Name</th>
                <td>{{ $employee->bank_account_name }}</td>
                <th>Bank Account Number</th>
                <td>{{ $employee->bank_account_number }}</td>
            </tr>
            <tr>
                <th>Bank Branch</th>
                <td>{{ $employee->bank_branch }}</td>
                <th>TIN / NSSF</th>
                <td>TIN: {{ $employee->tin_number ?: '-' }}<br>NSSF: {{ $employee->nssf_number ?: '-' }}</td>
            </tr>
            <tr>
                <th>Status Note</th>
                <td colspan="3">{{ $employee->status_note ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Next Of Kin</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->nextOfKins as $kin)
                    <tr>
                        <td>{{ $kin->is_primary ? 'Primary' : 'Secondary' }}</td>
                        <td>{{ $kin->full_name }}</td>
                        <td>{{ $kin->phone_number }}</td>
                        <td>{{ $kin->address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No next-of-kin records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Status History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Effective Date</th>
                    <th>Changed By</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->statusHistories as $history)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $history->from_status ?? 'new')) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</td>
                        <td>{{ $history->effective_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $history->changedBy?->name ?? 'System' }}</td>
                        <td>{{ $history->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No status history available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(empty($attachments))
        <div class="section">
            <h3>Attachments</h3>
            <div class="attachment-note">No CV or certificate attachments were found in this employee record.</div>
        </div>
    @else
        @foreach($attachments as $attachment)
            <div class="page-break"></div>

            <div class="section">
                <h3>{{ $attachment['category'] }} Attachment</h3>
                <table class="info-table">
                    <tr>
                        <th>Title</th>
                        <td colspan="3">{{ $attachment['title'] }}</td>
                    </tr>
                    <tr>
                        <th>File Name</th>
                        <td>{{ $attachment['file_name'] }}</td>
                        <th>File Type</th>
                        <td>{{ $attachment['file_extension'] }} / {{ $attachment['mime_type'] }}</td>
                    </tr>
                    <tr>
                        <th>Size</th>
                        <td>{{ $attachment['file_size'] }}</td>
                        <th>Availability</th>
                        <td>{{ $attachment['exists'] ? 'Available' : 'Missing' }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td colspan="3">{{ $attachment['notes'] }}</td>
                    </tr>
                </table>

                @if($attachment['exists'] && !empty($attachment['image_data_uri']))
                    <div class="attachment-preview">
                        <img src="{{ $attachment['image_data_uri'] }}" alt="{{ $attachment['title'] }}">
                    </div>
                @elseif($attachment['exists'])
                    <div class="attachment-note">
                        This attachment is part of the confidential employee file and is stored in the system.
                        The content is non-image (for example PDF/DOC), therefore a direct visual render is not embedded in this report.
                    </div>
                @else
                    <div class="attachment-note">
                        This attachment record exists, but the physical file was not found in storage.
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</body>
</html>
