<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->invoice_number }}</title>
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
            <img src="{{ asset('logo-v.png') }}" alt="شعار وزارة الصحة">
            <h1>وزارة الصحة الليبية</h1>
            <h2>إدارة الصيدلة</h2>
        </div>

        <div class="invoice-title">فاتورة</div>

        <div class="invoice-number">
            رقم الفاتورة: {{ $invoice->invoice_number }}
            <span style="float: left;">التاريخ: {{ $invoice->created_at->format('Y-m-d') }}</span>
        </div>

        <div class="two-column">
            <div>
                <table>
                    <tr>
                        <th>اسم الشركة</th>
                        <td>{{ $invoice->foreignCompany->company_name }}</td>
                    </tr>
                    <tr>
                        <th>ممثل الشركة</th>
                        <td>{{ $invoice->foreignCompany->representative->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>{{ $invoice->foreignCompany->representative->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>رقم الهاتف</th>
                        <td>{{ $invoice->foreignCompany->representative->phone ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table>
            <tr>
                <th>الوصف</th>
                <td>{{ $invoice->description ?? 'رسوم تسجيل شركة أجنبية' }}</td>
            </tr>
        </table>

        <div class="amount-section">
            <h3>المبلغ الإجمالي المستحق</h3>
            <div class="amount">{{ number_format($invoice->amount, 2) }} د.ل</div>
        </div>

        <table>
            <tr>
                <th>تاريخ الإصدار</th>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>حالة الفاتورة</th>
                <td>
                    @if($invoice->status == 'pending')
                        قيد الانتظار
                    @elseif($invoice->status == 'paid')
                        مدفوعة
                    @elseif($invoice->status == 'cancelled')
                        ملغاة
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>وزارة الصحة الليبية - إدارة الصيدلة</p>
            <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
