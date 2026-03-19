<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنبيه بقرب انتهاء الصلاحية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', 'Arial', 'Tahoma', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .email-container {
            max-width: 650px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
        }
        .header {
            padding: 30px 30px 20px 30px;
            text-align: center;
        }
        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: 700;
            color: #333333;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            color: #666666;
        }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.8;
        }
        .content h2 {
            color: #f59e0b;
            font-size: 18px;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 10px;
        }
        .expiry-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .expiry-table th {
            background-color: #fef3c7;
            padding: 10px;
            text-align: right;
            font-weight: 700;
            color: #92400e;
            border-bottom: 2px solid #fbbf24;
        }
        .expiry-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .expiry-table tr:hover {
            background-color: #fffbeb;
        }
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .type-local { background: #d1fae5; color: #065f46; }
        .type-foreign { background: #dbeafe; color: #1e40af; }
        .warning-box {
            background-color: #fef3c7;
            border-right: 3px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #777;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>وزارة الصحة - دولة ليبيا</h1>
            <p>إدارة الصيدلة والرقابة الدوائية</p>
            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0 0 0;">
        </div>

        <div class="content">
            <h2>تنبيه بقرب انتهاء الصلاحية</h2>

            <p>السيد/ة {{ $recipientName }} المحترم،</p>
            <p>تحية طيبة وبعد،</p>

            <p>نود إبلاغكم بأن الصلاحية ستنتهي خلال الثلاثة أشهر القادمة للعناصر التالية:</p>

            <table class="expiry-table">
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>الاسم</th>
                        <th>تاريخ الانتهاء</th>
                        <th>المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiringItems as $item)
                        <tr>
                            <td>
                                @if($item['type'] === 'local_company')
                                    <span class="type-badge type-local">شركة محلية</span>
                                @else
                                    <span class="type-badge type-foreign">شركة أجنبية</span>
                                @endif
                            </td>
                            <td><strong>{{ $item['name'] }}</strong></td>
                            <td>{{ $item['expires_at'] }}</td>
                            <td>{{ $item['days_remaining'] }} يوم</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="warning-box">
                <p><strong>يرجى اتخاذ الإجراءات التالية:</strong></p>
                <p>1. الدخول إلى لوحة التحكم الخاصة بكم</p>
                <p>2. رفع إيصال تحويل رسوم التجديد قبل انتهاء الصلاحية</p>
                <p>3. انتظار مراجعة الإيصال والموافقة عليه من قبل الإدارة</p>
            </div>

            <p><strong>ملاحظة:</strong> في حال عدم التجديد قبل تاريخ الانتهاء، سيتم تعليق الحساب تلقائياً.</p>

            <p style="margin-top: 30px;">مع خالص التقدير والاحترام،</p>
            <p><strong>وزارة الصحة - إدارة الصيدلة</strong></p>
        </div>

        <div class="footer">
            <p><strong>وزارة الصحة - دولة ليبيا</strong></p>
            <p>إدارة الصيدلة والرقابة الدوائية</p>
            <p>البريد الإلكتروني: pharmacy@health.gov.ly | الهاتف: 218-21-XXXXXXX</p>
            <p style="margin-top: 10px;">© {{ date('Y') }} وزارة الصحة. جميع الحقوق محفوظة.</p>
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً، يرجى عدم الرد عليه.</p>
        </div>
    </div>
</body>
</html>
