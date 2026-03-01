<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفض الصنف الدوائي</title>
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
            color: #c62828;
            font-size: 18px;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #c62828;
            padding-bottom: 10px;
        }

        .rejection-banner {
            background-color: #ffebee;
            border: 2px solid #e53935;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .rejection-banner .icon {
            font-size: 48px;
            color: #e53935;
            margin-bottom: 10px;
        }
        .rejection-banner p {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
            color: #c62828;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
        }

        .rejection-reason {
            background-color: #fff3cd;
            border-right: 3px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
        }
        .rejection-reason p {
            margin: 5px 0;
            font-size: 14px;
        }
        .rejection-reason .reason-text {
            background-color: #ffffff;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ffe082;
            color: #333;
            font-weight: normal;
        }

        .note {
            background-color: #e3f2fd;
            border-right: 3px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
        }
        .note p {
            margin: 5px 0;
            font-size: 14px;
        }

        .action-button {
            text-align: center;
            margin: 25px 0;
        }
        .action-button a {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1a5f4a;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
            <h2>إشعار برفض طلب تسجيل الصنف الدوائي</h2>

            <div class="rejection-banner">
                <div class="icon">✗</div>
                <p>تم رفض طلب التسجيل</p>
            </div>

            <p>السيد/ة {{ $representative->name }} المحترم/ة،</p>
            <p>تحية طيبة وبعد،</p>

            <p>نأسف لإبلاغكم بأنه تم رفض طلب تسجيل الصنف الدوائي التالي:</p>

            <table class="info-table">
                <tr>
                    <td>اسم الصنف الدوائي:</td>
                    <td><strong>{{ $product->product_name }}</strong></td>
                </tr>
                <tr>
                    <td>الشكل الصيدلاني:</td>
                    <td>{{ $product->pharmaceutical_form }}</td>
                </tr>
                <tr>
                    <td>التركيز:</td>
                    <td>{{ $product->concentration }}</td>
                </tr>
                <tr>
                    <td>الشركة المنتجة:</td>
                    <td>{{ $product->foreignCompany->company_name }}</td>
                </tr>
                <tr>
                    <td>تاريخ الرفض:</td>
                    <td>{{ now()->format('Y-m-d') }}</td>
                </tr>
            </table>

            <div class="rejection-reason">
                <p><strong>سبب الرفض:</strong></p>
                <div class="reason-text">
                    {{ $rejectionReason }}
                </div>
            </div>

            <div class="note">
                <p><strong>ملاحظة:</strong></p>
                <p>يمكنكم تقديم طلب جديد بعد استيفاء المتطلبات والتعديلات المطلوبة وفقاً لسبب الرفض المذكور أعلاه.</p>
            </div>

            <div class="action-button">
                <a href="{{ route('representative.pharmaceutical-products.show', $product->id) }}">
                    عرض تفاصيل الطلب
                </a>
            </div>

            <p style="margin-top: 30px;">للاستفسار أو الحصول على المزيد من المعلومات، يرجى التواصل مع الإدارة المختصة.</p>

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
