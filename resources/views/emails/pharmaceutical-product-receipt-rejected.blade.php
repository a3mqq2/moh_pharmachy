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
            border: 2px solid #c41e3a;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .rejection-box .title {
            font-weight: bold;
            color: #c41e3a;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .rejection-box .reason {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 3px;
            border-right: 3px solid #c41e3a;
            font-size: 14px;
            line-height: 1.6;
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
            <h2>إشعار برفض إيصال الدفع</h2>

            <p>السيد/ة {{ $representative->name }} المحترم/ة،</p>
            <p>تحية طيبة وبعد،</p>

            <p>نأسف لإبلاغكم بأنه تم رفض إيصال الدفع المقدم للصنف الدوائي التالي:</p>

            <table class="info-table">
                <tr>
                    <td>اسم الصنف الدوائي:</td>
                    <td><strong>{{ $product->product_name }}</strong></td>
                </tr>
                <tr>
                    <td>الاسم العلمي:</td>
                    <td>{{ $product->scientific_name }}</td>
                </tr>
                <tr>
                    <td>رقم الفاتورة:</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>المبلغ المستحق:</td>
                    <td><strong>{{ number_format($invoice->amount, 2) }} دينار ليبي</strong></td>
                </tr>
            </table>

            <div class="rejection-box">
                <div class="title">سبب رفض الإيصال:</div>
                <div class="reason">{{ $rejectionReason }}</div>
            </div>

            <div class="note">
                <p><strong>الخطوات المطلوبة:</strong></p>
                <ol>
                    <li>مراجعة سبب الرفض المذكور أعلاه بعناية</li>
                    <li>التأكد من صحة معلومات الدفع والإيصال</li>
                    <li>التحقق من مطابقة المبلغ المدفوع للمبلغ المستحق</li>
                    <li>التأكد من وضوح جميع البيانات في الإيصال (التاريخ، المبلغ، الجهة المستفيدة)</li>
                    <li>الحصول على إيصال دفع صحيح ومطابق للمواصفات</li>
                    <li>رفع الإيصال الجديد من خلال لوحة التحكم</li>
                    <li>انتظار المراجعة والموافقة من الإدارة</li>
                </ol>
            </div>

            <div class="action-button">
                <a href="{{ route('representative.pharmaceutical-products.show', $product->id) }}">
                    رفع إيصال جديد
                </a>
            </div>

            <p><strong>ملاحظة هامة:</strong> تم إعادة حالة الفاتورة إلى "غير مدفوعة". يرجى رفع إيصال صحيح في أقرب وقت ممكن لاستكمال عملية تسجيل الصنف الدوائي.</p>

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
