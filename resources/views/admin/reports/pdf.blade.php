<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .header { margin-bottom: 12px; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: top; }
        .logo-wrap { width: 78px; }
        .logo {
            width: 72px;
            height: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px;
            background: #ffffff;
        }
        h1 { font-size: 17px; margin: 0 0 4px 0; }
        .company-line { font-size: 10px; color: #334155; margin: 0 0 2px 0; }
        .meta { font-size: 10px; color: #334155; margin-top: 6px; }
        .summary { margin: 10px 0 12px 0; }
        .summary span { display: inline-block; margin-right: 12px; margin-bottom: 4px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 10px; }
        .section-title { font-size: 12px; font-weight: 700; margin: 10px 0 6px; color: #1e293b; }
        .small { font-size: 10px; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-wrap">
                    @if(!empty($company['logo_data_uri']))
                        <img src="{{ $company['logo_data_uri'] }}" alt="Company Logo" class="logo">
                    @endif
                </td>
                <td>
                    <h1>{{ $company['name'] }}</h1>
                    <p class="company-line">{{ $company['address'] ?: '-' }}</p>
                    <p class="company-line">
                        Phone: {{ $company['phone'] ?: '-' }} |
                        Email: {{ $company['email'] ?: '-' }} |
                        Website: {{ $company['website'] ?: '-' }}
                    </p>
                    <p class="company-line"><strong>{{ $data['title'] }}</strong></p>
                </td>
            </tr>
        </table>
        <div class="meta">
            Period: {{ $filters['from_date'] }} to {{ $filters['to_date'] }} |
            Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}
        </div>
    </div>

    <div class="summary">
        @foreach($data['summary'] as $label => $value)
            <span><strong>{{ strtoupper(str_replace('_', ' ', $label)) }}:</strong> {{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</span>
        @endforeach
    </div>

    @if($filters['report_type'] === 'trips')
        <table>
            <thead>
                <tr>
                    <th>Trip Number</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Completed</th>
                    <th>Incomplete</th>
                    <th>In Progress</th>
                    <th>Fuel (L)</th>
                    <th>Fuel Amount</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $trip)
                    <tr>
                        <td>{{ $trip->trip_number }}</td>
                        <td>{{ ucfirst($trip->status) }}</td>
                        <td>{{ $trip->orders_count }}</td>
                        <td>{{ $trip->orders_completed_count }}</td>
                        <td>{{ $trip->orders_incomplete_count }}</td>
                        <td>{{ $trip->orders_active_count }}</td>
                        <td>{{ number_format((float) ($trip->fuel_consumption_litres ?? 0), 2) }}</td>
                        <td>{{ number_format((float) ($trip->fuel_consumption_amount ?? 0), 2) }}</td>
                        <td>{{ $trip->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($filters['report_type'] === 'orders')
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Trip</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Weight (Tons)</th>
                    <th>Value</th>
                    <th>Distance (KM)</th>
                    <th>Fuel (L)</th>
                    <th>Fuel Amount</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->trip?->trip_number ?? '-' }}</td>
                        <td>{{ $order->customer?->name ?? '-' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ number_format((float) $order->weight_tons, 2) }}</td>
                        <td>{{ number_format((float) $order->agreed_price, 2) }}</td>
                        <td>{{ $order->distance_km ? number_format((float) $order->distance_km, 2) : '-' }}</td>
                        <td>{{ number_format((float) ($order->fuel_consumption_litres ?? 0), 2) }}</td>
                        <td>{{ number_format((float) ($order->fuel_consumption_amount ?? 0), 2) }}</td>
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($filters['report_type'] === 'drivers')
        <div class="section-title">Driver Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Mobile</th>
                    <th>Trips</th>
                    <th>Orders</th>
                    <th>Legs</th>
                    <th>Completed</th>
                    <th>Active</th>
                    <th>Fuel (L)</th>
                    <th>Fuel Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['driver_summary'] as $summary)
                    <tr>
                        <td>{{ $summary['driver_name'] }}</td>
                        <td>{{ $summary['mobile_number'] }}</td>
                        <td>{{ $summary['trips_performed'] }}</td>
                        <td>{{ $summary['orders_handled'] }}</td>
                        <td>{{ $summary['legs_count'] }}</td>
                        <td>{{ $summary['completed_legs'] }}</td>
                        <td>{{ $summary['active_legs'] }}</td>
                        <td>{{ number_format((float) $summary['fuel_consumption_litres'], 2) }}</td>
                        <td>{{ number_format((float) $summary['fuel_consumption_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Driver Trip / Order Fuel Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Trip</th>
                    <th>Orders</th>
                    <th>Legs</th>
                    <th>Completed</th>
                    <th>Active</th>
                    <th>Fuel (L)</th>
                    <th>Fuel Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['driver_trip_summary'] as $summary)
                    <tr>
                        <td>{{ $summary['driver_name'] }}</td>
                        <td>{{ $summary['trip_number'] }}</td>
                        <td>{{ $summary['orders'] }}</td>
                        <td>{{ $summary['legs_count'] }}</td>
                        <td>{{ $summary['completed_legs'] }}</td>
                        <td>{{ $summary['active_legs'] }}</td>
                        <td>{{ number_format((float) $summary['fuel_consumption_litres'], 2) }}</td>
                        <td>{{ number_format((float) $summary['fuel_consumption_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Order</th>
                    <th>Fleet</th>
                    <th>Status</th>
                    <th>Litres</th>
                    <th>Amount</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                    <tr>
                        <td>#{{ $row->id }}</td>
                        <td>{{ $row->requisition_type === 'fleet_only' ? 'Fleet Only' : 'Order Based' }}</td>
                        <td>{{ $row->order?->order_number ?? '-' }}</td>
                        <td>{{ $row->fleet?->fleet_code ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($row->status)) }}</td>
                        <td>{{ number_format((float) $row->additional_litres, 2) }}</td>
                        <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                        <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
