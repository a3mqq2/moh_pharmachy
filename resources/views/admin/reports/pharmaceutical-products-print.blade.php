<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('reports.products_report') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            direction: rtl;
            padding: 15mm;
            color: #000;
            background: #fff;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 10px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ministry-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .department-name {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .report-date {
            font-size: 12px;
            margin-top: 8px;
        }

        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .filters h3 {
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .filters p {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .summary h3 {
            font-size: 14px;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .summary-item {
            text-align: center;
            padding: 8px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .summary-label {
            font-size: 10px;
            margin-bottom: 4px;
            color: #666;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 8px 5px;
            text-align: right;
            border: 1px solid #000;
            font-size: 10px;
        }

        th {
            background: #e0e0e0;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tfoot {
            background: #e0e0e0;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 12mm;
                size: A4 landscape;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ asset('logo-v.png') }}" alt="{{ __('general.ministry_of_health') }}">
        </div>
        <div class="ministry-name">{{ __('general.ministry_of_health_libya') }}</div>
        <div class="department-name">{{ __('general.pharmacy_drug_control') }}</div>
        <div class="report-title">{{ __('reports.products_report') }}</div>
        <div class="report-date">{{ __('general.report_date') }}: {{ date('Y-m-d') }}</div>
    </div>

    @if(request()->hasAny(['status', 'from_date', 'to_date']))
    <div class="filters">
        <h3>{{ __('general.search_criteria') }}</h3>
        @if(request('status'))
            @php
                $statuses = [
                    'uploading_documents' => __('products.status_uploading_docs'),
                    'pending_review' => __('products.status_pending_review'),
                    'preliminary_approved' => __('products.status_preliminary_approved'),
                    'pending_final_approval' => __('products.status_pending_final'),
                    'pending_payment' => __('products.status_pending_payment'),
                    'payment_review' => __('products.status_payment_review'),
                    'rejected' => __('products.status_rejected'),
                    'active' => __('products.status_approved')
                ];
            @endphp
            <p><strong>{{ __('general.status') }}:</strong> {{ $statuses[request('status')] ?? request('status') }}</p>
        @endif
        @if(request('from_date'))
            <p><strong>{{ __('general.from_date') }}:</strong> {{ request('from_date') }}</p>
        @endif
        @if(request('to_date'))
            <p><strong>{{ __('general.to_date') }}:</strong> {{ request('to_date') }}</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <h3>{{ __('general.overall_statistics') }}</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">{{ __('reports.total_products') }}</div>
                <div class="summary-value">{{ $stats['total'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_approved') }}</div>
                <div class="summary-value">{{ $stats['active'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_pending_review') }}</div>
                <div class="summary-value">{{ $stats['pending_review'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_preliminary_approved') }}</div>
                <div class="summary-value">{{ $stats['preliminary_approved'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_pending_final') }}</div>
                <div class="summary-value">{{ $stats['pending_final_approval'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_pending_payment') }}</div>
                <div class="summary-value">{{ $stats['pending_payment'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_payment_review') }}</div>
                <div class="summary-value">{{ $stats['payment_review'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('products.status_rejected') }}</div>
                <div class="summary-value">{{ $stats['rejected'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="13%">{{ __('products.trade_name') }}</th>
                <th width="8%">{{ __('general.registration_number') }}</th>
                <th width="11%">{{ __('products.scientific_name') }}</th>
                <th width="9%">{{ __('products.dosage_form') }}</th>
                <th width="10%">{{ __('products.concentration_short') }}</th>
                <th width="18%">{{ __('products.foreign_company') }}</th>
                <th width="13%">{{ __('companies.representative') }}</th>
                <th width="8%">{{ __('general.status') }}</th>
                <th width="7%">{{ __('general.registration_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->registration_number ?? '-' }}</td>
                <td>{{ $product->scientific_name }}</td>
                <td>{{ $product->pharmaceutical_form }}</td>
                <td>{{ $product->concentration }}</td>
                <td>{{ $product->foreignCompany->company_name }}</td>
                <td>{{ $product->representative->name }}</td>
                <td>{{ $product->status_name }}</td>
                <td>{{ $product->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">{{ __('reports.total_products') }}</td>
                <td>{{ $stats['total'] }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ __('general.copyright_ministry') }}</p>
        <p>{{ __('general.auto_generated_report_v2') }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
