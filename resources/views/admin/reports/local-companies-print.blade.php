<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الشركات المحلية</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            direction: rtl;
            padding: 20mm;
            color: #000;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 180px;
            height: 180px;
            margin: 0 auto 15px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ministry-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .department-name {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
        }

        .report-date {
            font-size: 14px;
            margin-top: 10px;
        }

        .filters {
            margin-bottom: 30px;
            padding: 15px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .filters h3 {
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .filters p {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .summary h3 {
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .summary-item {
            text-align: center;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .summary-label {
            font-size: 12px;
            margin-bottom: 5px;
            color: #666;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            text-align: right;
            border: 1px solid #000;
            font-size: 12px;
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
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 10px;
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
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ asset('logo-v.png') }}" width="300" alt="وزارة الصحة">
        </div>
        <div class="ministry-name">وزارة الصحة - دولة ليبيا</div>
        <div class="department-name">إدارة الصيدلة والرقابة الدوائية</div>
        <div class="report-title">تقرير الشركات المحلية</div>
        <div class="report-date">تاريخ التقرير: {{ date('Y-m-d') }}</div>
    </div>

    @if(request()->hasAny(['status', 'from_date', 'to_date']))
    <div class="filters">
        <h3>معايير البحث:</h3>
        @if(request('status'))
            <p><strong>الحالة:</strong> {{ ['pending' => 'قيد المراجعة', 'uploading_documents' => 'قيد رفع المستندات', 'approved' => 'معتمدة', 'active' => 'مفعلة', 'rejected' => 'مرفوضة'][request('status')] ?? request('status') }}</p>
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
                <div class="summary-label">إجمالي الشركات</div>
                <div class="summary-value">{{ $stats['total'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">مفعلة</div>
                <div class="summary-value">{{ $stats['active'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">قيد المراجعة</div>
                <div class="summary-value">{{ $stats['pending'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">معتمدة</div>
                <div class="summary-value">{{ $stats['approved'] }}</div>
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
                <th width="5%">#</th>
                <th width="35%">اسم الشركة</th>
                <th width="25%">الممثل</th>
                <th width="20%">الحالة</th>
                <th width="15%">تاريخ التسجيل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $company->company_name }}</td>
                <td>{{ $company->representative?->name ?? '-' }}</td>
                <td>{{ $company->status_name }}</td>
                <td>{{ $company->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">إجمالي الشركات</td>
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
