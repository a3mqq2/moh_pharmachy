<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('invoices.invoices_record') }}</title>
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
        <div class="report-title">{{ __('invoices.invoices_record') }}</div>
        <div class="report-date">{{ __('general.report_statement_date') }}: {{ date('Y-m-d') }}</div>
    </div>

    @if(request()->hasAny(['search', 'status', 'type', 'from_date', 'to_date']))
    <div class="filters">
        <h3>{{ __('general.search_criteria') }}</h3>
        @if(request('search'))
            <p><strong>{{ __('general.search') }}:</strong> {{ request('search') }}</p>
        @endif
        @if(request('type') && request('type') != 'all')
            @php
                $types = ['local' => __('invoices.local_companies'), 'foreign' => __('invoices.foreign_companies'), 'pharmaceutical' => __('invoices.pharmaceutical_products')];
            @endphp
            <p><strong>{{ __('general.type') }}:</strong> {{ $types[request('type')] ?? request('type') }}</p>
        @endif
        @if(request('status'))
            @php
                $statuses = ['unpaid' => __('invoices.status_unpaid'), 'pending' => __('invoices.status_pending'), 'pending_review' => __('invoices.status_review'), 'paid' => __('invoices.status_paid'), 'cancelled' => __('invoices.status_cancelled')];
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
        <h3>{{ __('general.statistics') }}</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.total_invoices') }}</div>
                <div class="summary-value">{{ $stats['total'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.status_paid') }}</div>
                <div class="summary-value">{{ $stats['paid'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.status_pending') }}</div>
                <div class="summary-value">{{ $stats['pending'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.total_revenue') }}</div>
                <div class="summary-value">{{ number_format($stats['total_revenue'], 2) }} {{ __('general.currency') }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="12%">{{ __('invoices.invoice_number') }}</th>
                <th width="10%">{{ __('general.type') }}</th>
                <th width="25%">{{ __('companies.company_name') }}</th>
                <th width="15%">{{ __('general.amount') }}</th>
                <th width="15%">{{ __('general.status') }}</th>
                <th width="12%">{{ __('general.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>
                    @if($invoice->company_type == 'local')
                        {{ __('invoices.local_company') }}
                    @elseif($invoice->company_type == 'foreign')
                        {{ __('invoices.foreign_company') }}
                    @else
                        {{ __('invoices.pharmaceutical_product') }}
                    @endif
                </td>
                <td>{{ $invoice->company?->company_name ?? __('general.not_available') }}</td>
                <td>{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</td>
                <td>
                    @if($invoice->status == 'paid')
                        {{ __('invoices.status_paid') }}
                    @elseif($invoice->status == 'unpaid')
                        {{ __('invoices.status_unpaid') }}
                    @elseif($invoice->status == 'pending')
                        {{ __('invoices.status_pending') }}
                    @elseif($invoice->status == 'pending_review')
                        {{ __('invoices.status_review') }}
                    @elseif($invoice->status == 'cancelled')
                        {{ __('invoices.status_cancelled') }}
                    @else
                        {{ $invoice->status }}
                    @endif
                </td>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">{{ __('invoices.total_invoices') }}: {{ $stats['total'] }}</td>
                <td>{{ number_format($stats['total_revenue'], 2) }} {{ __('general.currency') }}</td>
                <td colspan="2">{{ __('invoices.status_paid') }}: {{ $stats['paid'] }}</td>
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
