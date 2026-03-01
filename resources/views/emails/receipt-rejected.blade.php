<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفض إيصال الدفع</title>
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
            color: #1a5f4a;
            font-size: 18px;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #1a5f4a;
            padding-bottom: 10px;
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

        .rejection-box {
            background-color: #fff5f5;
            border-right: 3px solid #c41e3a;
            padding: 15px;
            margin: 20px 0;
        }
        .rejection-box p {
            margin: 5px 0;
            font-size: 14px;
        }

        .note {
            background-color: #f9f9f9;
            border-right: 3px solid #1a5f4a;
            padding: 15px;
            margin: 20px 0;
        }
        .note p {
            margin: 5px 0;
            font-size: 14px;
        }
        .note ol {
            margin: 10px 0;
            padding-right: 20px;
        }
        .note li {
            margin: 5px 0;
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
            <h2>إشعار برفض إيصال الدفع</h2>

            <p>السيد/ة الممثل المحترم،</p>
            <p>تحية طيبة وبعد،</p>

            <p>نأسف لإبلاغكم بأنه تم رفض إيصال الدفع المقدم للفاتورة التالية:</p>

            <table class="info-table">
                <tr>
                    <td>اسم الشركة:</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                <tr>
                    <td>رقم الفاتورة:</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>المبلغ:</td>
                    <td>{{ number_format($invoice->amount, 2) }} دينار ليبي</td>
                </tr>
            </table>

            <div class="rejection-box">
                <p><strong>سبب رفض الإيصال:</strong></p>
                <p>{{ $rejectionReason }}</p>
            </div>

            <div class="note">
                <p><strong>الخطوات المطلوبة:</strong></p>
                <ol>
                    <li>مراجعة سبب الرفض المذكور أعلاه</li>
                    <li>التأكد من صحة معلومات الدفع والإيصال</li>
                    <li>الحصول على إيصال دفع صحيح ومطابق للمبلغ المستحق</li>
                    <li>رفع الإيصال الجديد من خلال لوحة التحكم</li>
                    <li>انتظار المراجعة والموافقة من الإدارة</li>
                </ol>
            </div>

            <p><strong>ملاحظة هامة:</strong> تم تغيير حالة شركتكم إلى "مرفوضة" مؤقتاً. سيتم إعادة المراجعة بعد رفع إيصال صحيح.</p>

            <p style="margin-top: 30px;">مع خالص التقدير والاحترام،</p>
            <p><strong>وزارة الصحة - إدارة الصيدلة </strong></p>
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
