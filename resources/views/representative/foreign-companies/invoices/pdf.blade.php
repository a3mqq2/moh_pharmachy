<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('invoices.invoice') }} {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            background: #fff;
            color: #000;
            line-height: 1.5;
        }

        .invoice-container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header img {
            width: 180px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .invoice-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin: 15px 0;
            padding: 10px;
            border: 2px solid #000;
            background: #f5f5f5;
        }

        .invoice-number {
            text-align: left;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: right;
            font-size: 14px;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: 700;
            width: 35%;
        }

        .amount-section {
            margin: 18px 0;
            text-align: center;
            border: 2px solid #000;
            padding: 15px;
        }

        .amount-section h3 {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .amount-section .amount {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }

        .footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid #000;
            font-size: 11px;
            text-align: center;
        }

        .two-column {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .two-column > div {
            flex: 1;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .invoice-container {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <img src="{{ asset('logo-v.png') }}" alt="">
            <h1>{{ __('invoices.ministry_health_libya') }}</h1>
            <h2>{{ __('general.pharmacy_department') }}</h2>
        </div>

        <div class="invoice-title">{{ __('invoices.invoice') }}</div>

        <div class="invoice-number">
            {{ __('invoices.invoice_number') }}: {{ $invoice->invoice_number }}
            <span style="float: left;">{{ __('general.date') }}: {{ $invoice->created_at->format('Y-m-d') }}</span>
        </div>

        <div class="two-column">
            <div>
                <table>
                    <tr>
                        <th>{{ __('companies.company_name') }}</th>
                        <td>{{ $company->company_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('companies.local_company') }}</th>
                        <td>{{ $company->localCompany->company_name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table>
                    <tr>
                        <th>{{ __('general.country') }}</th>
                        <td>{{ $company->country }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('companies.activity_type') }}</th>
                        <td>{{ $company->activity_type_name ?? $company->activity_type }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table>
            <tr>
                <th>{{ __('general.description') }}</th>
                <td>{{ $invoice->description ?? __('invoices.foreign_reg_fees') }}</td>
            </tr>
        </table>

        <div class="amount-section">
            <h3>{{ __('invoices.total_due_amount') }}</h3>
            <div class="amount">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</div>
        </div>

        <table>
            <tr>
                <th>{{ __('general.issue_date') }}</th>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>{{ __('invoices.invoice_status') }}</th>
                <td>
                    @if($invoice->status == 'pending')
                        {{ __('invoices.status_pending') }}
                    @elseif($invoice->status == 'paid')
                        {{ __('invoices.status_paid') }}
                    @elseif($invoice->status == 'cancelled')
                        {{ __('invoices.status_cancelled') }}
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>{{ __('invoices.ministry_pharmacy_footer') }}</p>
            <p>{{ __('general.print_date') }}: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
