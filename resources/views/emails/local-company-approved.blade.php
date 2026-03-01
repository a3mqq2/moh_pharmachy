<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار قبول تسجيل شركة</title>
    <style>
        body {
            font-family: 'Traditional Arabic', 'Almarai', Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 30px 20px;
            direction: rtl;
            color: #000000;
            font-size: 14px;
            line-height: 2;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #000000;
        }

        .header {
            text-align: center;
            padding: 25px 20px;
            border-bottom: 2px solid #000000;
        }

        .logo {
            width: 90px;
            margin-bottom: 10px;
        }

        .header-text {
            font-size: 14px;
            margin: 3px 0;
        }

        .header-text.bold {
            font-weight: bold;
            font-size: 15px;
        }

        .content {
            padding: 30px;
        }

        .document-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 30px;
            text-decoration: underline;
        }

        .recipient {
            margin-bottom: 25px;
        }

        .message-text {
            text-align: justify;
            margin-bottom: 25px;
        }

        .registration-box {
            text-align: center;
            border: 2px solid #000;
            padding: 15px;
            margin: 25px auto;
            width: 250px;
        }

        .registration-label {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .registration-number {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .info-table td {
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 13px;
        }

        .info-table .label {
            width: 35%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .notes-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #000;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .notes-list {
            margin: 0;
            padding-right: 25px;
            font-size: 13px;
        }

        .notes-list li {
            margin-bottom: 8px;
        }

        .closing {
            margin-top: 30px;
            line-height: 1.8;
        }

        .footer {
            border-top: 1px solid #000;
            padding: 15px;
            font-size: 11px;
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo-v.png') }}" alt="شعار الوزارة" class="logo">
            <div class="header-text">دولة ليبيا</div>
            <div class="header-text bold">وزارة الصحة</div>
            <div class="header-text">إدارة الصيدلة</div>
        </div>

        <div class="content">
            <div class="document-title">إشعار قبول تسجيل شركة</div>

            <div class="recipient">
                <div>السيد / {{ $company->manager_name }}</div>
                <div>المدير المسؤول - {{ $company->company_name }}</div>
                <div>المحترم</div>
            </div>

            <div class="message-text">
                <p>السلام عليكم ورحمة الله وبركاته،</p>
                <p>
                    نفيدكم علماً بأنه تمت الموافقة على طلب تسجيل شركتكم في سجل الشركات المحلية
                    بإدارة الصيدلة - وزارة الصحة، وقد تم تخصيص رقم قيد لشركتكم كما هو موضح أدناه.
                </p>
            </div>

            <div class="registration-box">
                <div class="registration-label">رقم القيد</div>
                <div class="registration-number">{{ $company->registration_number }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">اسم الشركة</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                <tr>
                    <td class="label">التصنيف</td>
                    <td>{{ $company->company_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">نوع الترخيص</td>
                    <td>{{ $company->license_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">التخصص</td>
                    <td>{{ $company->license_specialty_name }}</td>
                </tr>
                <tr>
                    <td class="label">تاريخ التسجيل</td>
                    <td>{{ $company->registration_date?->format('Y-m-d') }}</td>
                </tr>
            </table>

            <div class="notes-section">
                <div class="notes-title">ملاحظات هامة:</div>
                <ul class="notes-list">
                    <li>يرجى الاحتفاظ برقم القيد واستخدامه في جميع المعاملات الرسمية.</li>
                    <li>يجب الالتزام بتجديد التسجيل سنوياً قبل انتهاء صلاحيته.</li>
                    <li>يرجى الالتزام بجميع اللوائح والأنظمة المعمول بها.</li>
                </ul>
            </div>

            <div class="closing">
                <p>وتفضلوا بقبول فائق الاحترام والتقدير،</p>
                <p>إدارة الصيدلة - وزارة الصحة</p>
            </div>
        </div>

        <div class="footer">
            إشعار آلي صادر من نظام إدارة الصيدلة - وزارة الصحة - دولة ليبيا | تاريخ الإصدار: {{ now()->format('Y-m-d') }}
        </div>
    </div>
</body>
</html>
