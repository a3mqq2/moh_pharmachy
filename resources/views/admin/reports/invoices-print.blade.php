<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('reports.invoices_report') }}</title>
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
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .summary-item {
            text-align: center;
            padding: 8px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .summary-label {
            font-size: 11px;
            margin-bottom: 4px;
            color: #666;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 8px;
            background: #e0e0e0;
            border-right: 3px solid #000;
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
        <div class="report-title">{{ __('reports.invoices_report') }}</div>
        <div class="report-date">{{ __('general.report_date') }}: {{ date('Y-m-d') }}</div>
    </div>

    @if(request()->hasAny(['type', 'status', 'from_date', 'to_date']))
    <div class="filters">
        <h3>{{ __('general.search_criteria') }}</h3>
        @if(request('type') && request('type') != 'all')
            <p><strong>{{ __('invoices.invoice_type') }}:</strong> {{ request('type') == 'local' ? __('invoices.local_companies') : __('invoices.pharmaceutical_products') }}</p>
        @endif
        @if(request('status'))
            <p><strong>{{ __('general.status') }}:</strong> {{ request('status') == 'paid' ? __('invoices.status_paid') : __('invoices.status_unpaid') }}</p>
        @endif
        @if(request('from_date'))
            <p><strong>{{ __('general.from_date') }}:</strong> {{ request('from_date') }}</p>
        @endif
        @if(request('to_date'))
            <p><strong>{{ __('general.to_date') }}:</strong> {{ request('to_date') }}</p>
        @endif
    </div>
    @endif

    @if($type == 'all')
    <div class="summary">
        <h3>{{ __('general.overall_statistics') }}</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.total_invoices') }}</div>
                <div class="summary-value">{{ $stats['total_invoices'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">{{ __('invoices.total_revenue') }}</div>
                <div class="summary-value">{{ number_format($stats['total_revenue'], 2) }} {{ __('general.currency') }}</div>
            </div>
        </div>
    </div>
    @endif

    @if($type == 'all' || $type == 'local')
    <div class="section-title">{{ __('invoices.local_invoices') }}</div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">{{ __('invoices.invoice_number') }}</th>
                <th width="40%">{{ __('companies.company_name') }}</th>
                <th width="15%">{{ __('general.amount') }} ({{ __('general.currency') }})</th>
                <th width="10%">{{ __('general.status') }}</th>
                <th width="10%">{{ __('general.issue_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($localInvoices as $invoice)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->localCompany->company_name }}</td>
                <td>{{ number_format($invoice->amount, 2) }}</td>
                <td>{{ $invoice->status_name }}</td>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">{{ __('invoices.no_invoices_yet') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ __('invoices.total_invoices') }}</td>
                <td colspan="3">{{ $stats['local_total'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.status_paid') }}</td>
                <td colspan="3">{{ $stats['local_paid'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.status_unpaid') }}</td>
                <td colspan="3">{{ $stats['local_unpaid'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.total_revenue') }}</td>
                <td colspan="3">{{ number_format($stats['local_revenue'], 2) }} {{ __('general.currency') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($type == 'all' || $type == 'pharmaceutical')
    <div class="section-title">{{ __('invoices.pharma_invoices') }}</div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">{{ __('invoices.invoice_number') }}</th>
                <th width="40%">{{ __('invoices.pharmaceutical_product') }}</th>
                <th width="15%">{{ __('general.amount') }} ({{ __('general.currency') }})</th>
                <th width="10%">{{ __('general.status') }}</th>
                <th width="10%">{{ __('general.issue_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pharmaInvoices as $invoice)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->pharmaceuticalProduct->product_name }}</td>
                <td>{{ number_format($invoice->amount, 2) }}</td>
                <td>{{ $invoice->status_name }}</td>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">{{ __('invoices.no_invoices_yet') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ __('invoices.total_invoices') }}</td>
                <td colspan="3">{{ $stats['pharma_total'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.status_paid') }}</td>
                <td colspan="3">{{ $stats['pharma_paid'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.status_unpaid') }}</td>
                <td colspan="3">{{ $stats['pharma_unpaid'] }}</td>
            </tr>
            <tr>
                <td colspan="3">{{ __('invoices.total_revenue') }}</td>
                <td colspan="3">{{ number_format($stats['pharma_revenue'], 2) }} {{ __('general.currency') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

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
