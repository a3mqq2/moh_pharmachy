<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الأصناف الدوائية</title>
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
            <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة">
        </div>
        <div class="ministry-name">وزارة الصحة - دولة ليبيا</div>
        <div class="department-name">إدارة الصيدلة والرقابة الدوائية</div>
        <div class="report-title">تقرير الأصناف الدوائية</div>
        <div class="report-date">تاريخ التقرير: {{ date('Y-m-d') }}</div>
    </div>

    @if(request()->hasAny(['status', 'from_date', 'to_date']))
    <div class="filters">
        <h3>معايير البحث:</h3>
        @if(request('status'))
            @php
                $statuses = [
                    'uploading_documents' => 'قيد رفع المستندات',
                    'pending_review' => 'قيد المراجعة',
                    'preliminary_approved' => 'موافقة مبدئية',
                    'pending_final_approval' => 'قيد الموافقة النهائية',
                    'pending_payment' => 'قيد السداد',
                    'payment_review' => 'قيد مراجعة السداد',
                    'rejected' => 'مرفوضة',
                    'active' => 'معتمدة'
                ];
            @endphp
            <p><strong>الحالة:</strong> {{ $statuses[request('status')] ?? request('status') }}</p>
        @endif
        @if(request('from_date'))
            <p><strong>من تاريخ:</strong> {{ request('from_date') }}</p>
        @endif
        @if(request('to_date'))
            <p><strong>إلى تاريخ:</strong> {{ request('to_date') }}</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <h3>الإحصائيات الإجمالية</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">إجمالي الأصناف</div>
                <div class="summary-value">{{ $stats['total'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">معتمدة</div>
                <div class="summary-value">{{ $stats['active'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">قيد المراجعة</div>
                <div class="summary-value">{{ $stats['pending_review'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">موافقة مبدئية</div>
                <div class="summary-value">{{ $stats['preliminary_approved'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">قيد الموافقة النهائية</div>
                <div class="summary-value">{{ $stats['pending_final_approval'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">قيد السداد</div>
                <div class="summary-value">{{ $stats['pending_payment'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">قيد مراجعة السداد</div>
                <div class="summary-value">{{ $stats['payment_review'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">مرفوضة</div>
                <div class="summary-value">{{ $stats['rejected'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="13%">الاسم التجاري</th>
                <th width="8%">رقم القيد</th>
                <th width="11%">الاسم العلمي</th>
                <th width="9%">الشكل الصيدلاني</th>
                <th width="10%">التركيز</th>
                <th width="18%">الشركة الأجنبية</th>
                <th width="13%">الممثل</th>
                <th width="8%">الحالة</th>
                <th width="7%">تاريخ التسجيل</th>
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
                <td colspan="8">إجمالي الأصناف</td>
                <td>{{ $stats['total'] }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} وزارة الصحة - دولة ليبيا. جميع الحقوق محفوظة.</p>
        <p>تم إنشاء هذا التقرير تلقائياً بواسطة نظام إدارة الصيدلة والرقابة الدوائية</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
