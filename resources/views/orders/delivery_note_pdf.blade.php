<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Delivery Note - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 28px;
        }
        .top {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: table;
            width: 100%;
        }
        .top-left, .top-right {
            display: table-cell;
            vertical-align: top;
        }
        .top-left {
            width: 74px;
        }
        .top-right {
            text-align: right;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }
        .sub {
            margin-top: 4px;
            color: #475569;
            font-size: 11px;
        }
        .logo {
            height: 58px;
            width: auto;
        }
        .company {
            font-size: 11px;
            color: #334155;
            margin-top: 3px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .meta td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
        }
        .label {
            display: block;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 2px;
        }
        .value {
            font-weight: 600;
            color: #0f172a;
        }
        .section-title {
            margin: 16px 0 8px;
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        table.grid {
            width: 100%;
            border-collapse: collapse;
        }
        table.grid th,
        table.grid td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }
        table.grid th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .route-box {
            border: 1px solid #cbd5e1;
            padding: 10px;
            background: #f8fafc;
        }
        .footer {
            margin-top: 28px;
        }
        .sign {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .sign td {
            width: 33.33%;
            padding-top: 28px;
            padding-right: 10px;
            font-size: 11px;
        }
        .line {
            border-top: 1px solid #334155;
            margin-top: 24px;
            padding-top: 4px;
            color: #334155;
        }
    </style>
</head>
<body>
    @php
        $logoData = null;
        $logoPath = public_path('images/nexus-logo.png');
        if (is_file($logoPath)) {
            $ext = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
            $logoData = 'data:image/'.$ext.';base64,'.base64_encode((string) file_get_contents($logoPath));
        }
    @endphp
    <div class="top">
        <div class="top-left">
            @if($logoData)
                <img src="{{ $logoData }}" alt="NexusFlow Logo" class="logo">
            @endif
        </div>
        <div class="top-right">
            <h1 class="title">{{ strtoupper(config('app.company_name', 'NexusFlow Company Limited Tz')) }}</h1>
            <div class="company">{{ config('app.company_address') }}</div>
            <div class="company">
                @if(config('app.company_phone')) Tel: {{ config('app.company_phone') }} @endif
                @if(config('app.company_email')) | Email: {{ config('app.company_email') }} @endif
            </div>
            <div class="company">{{ config('app.company_website', 'https://nexusflow.co.tz/') }}</div>
            <div class="sub">DELIVERY NOTE | Generated on {{ $generatedAt->format('d M Y, h:i A') }} | Ref: DN-{{ $order->order_number }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <span class="label">Delivery Note No.</span>
                <span class="value">DN-{{ $order->order_number }}</span>
            </td>
            <td>
                <span class="label">Order Number</span>
                <span class="value">{{ $order->order_number }}</span>
            </td>
            <td>
                <span class="label">Trip Number</span>
                <span class="value">{{ $order->trip?->trip_number ?? '-' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Customer</span>
                <span class="value">{{ $order->customer?->name ?? '-' }}</span>
            </td>
            <td>
                <span class="label">Cargo Type</span>
                <span class="value">{{ $order->cargo_type }}</span>
            </td>
            <td>
                <span class="label">Cargo Weight</span>
                <span class="value">{{ number_format((float) $order->weight_tons, 2) }} t</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Route</div>
    <div class="route-box">
        <strong>Origin:</strong> {{ $order->origin_address }}<br>
        <strong>Destination:</strong> {{ $order->destination_address }}
    </div>

    <div class="section-title">Fleet Assignment</div>
    <table class="grid">
        <thead>
            <tr>
                <th>Seq</th>
                <th>Fleet</th>
                <th>Plate</th>
                <th>Driver</th>
                <th>Driver Phone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->legs as $leg)
                <tr>
                    <td>{{ $leg->leg_sequence }}</td>
                    <td>{{ $leg->fleet?->fleet_code ?? '-' }}</td>
                    <td>{{ $leg->fleet?->plate_number ?? '-' }}</td>
                    <td>{{ $leg->driver?->name ?? '-' }}</td>
                    <td>{{ $leg->driver?->mobile_number ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No leg assignment found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Delivery Confirmation</div>
    <table class="grid">
        <tbody>
            <tr>
                <td style="width: 25%; font-weight: 700;">Delivered To</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Delivery Date</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Receiver Contact</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Remarks</td>
                <td style="height: 40px;"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="sign">
            <tr>
                <td><div class="line">Prepared By</div></td>
                <td><div class="line">Approved By</div></td>
                <td><div class="line">Received By</div></td>
            </tr>
        </table>
    </div>
</body>
</html>
