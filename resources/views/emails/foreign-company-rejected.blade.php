<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار بخصوص طلب تسجيل شركة أجنبية</title>
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

        .reason-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #000;
        }

        .reason-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .reason-text {
            font-size: 13px;
            line-height: 1.8;
        }

        .instructions-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #000;
        }

        .instructions-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .instructions-list {
            margin: 0;
            padding-right: 25px;
            font-size: 13px;
        }

        .instructions-list li {
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
            <div class="document-title">إشعار بخصوص طلب تسجيل شركة أجنبية</div>

            <div class="recipient">
                <div>السيد / {{ $company->representative->name ?? 'الممثل المحترم' }}</div>
                <div>ممثل الشركة - {{ $company->company_name }}</div>
                <div>المحترم</div>
            </div>

            <div class="message-text">
                <p>السلام عليكم ورحمة الله وبركاته،</p>
                <p>
                    إشارة إلى طلبكم المقدم لتسجيل شركتكم في سجل الشركات الأجنبية بإدارة الصيدلة - وزارة الصحة،
                    نفيدكم بأنه تعذر قبول الطلب في الوقت الحالي وذلك للأسباب الموضحة أدناه.
                </p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">اسم الشركة</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                <tr>
                    <td class="label">الدولة</td>
                    <td>{{ $company->country }}</td>
                </tr>
                <tr>
                    <td class="label">نوع النشاط</td>
                    <td>{{ $company->activity_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">نوع الكيان</td>
                    <td>{{ $company->entity_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">تاريخ تقديم الطلب</td>
                    <td>{{ $company->created_at->format('Y-m-d') }}</td>
                </tr>
            </table>

            <div class="reason-section">
                <div class="reason-title">سبب عدم القبول:</div>
                <div class="reason-text">{{ $company->rejection_reason }}</div>
            </div>

            <div class="instructions-section">
                <div class="instructions-title">الإجراءات المطلوبة:</div>
                <ul class="instructions-list">
                    <li>مراجعة الملاحظات المذكورة أعلاه واستيفاء المتطلبات الناقصة.</li>
                    <li>التواصل مع إدارة الصيدلة للحصول على مزيد من التوضيحات إن لزم الأمر.</li>
                    <li>تعديل البيانات أو المستندات حسب الملاحظات المذكورة.</li>
                    <li>التأكد من صحة واكتمال جميع المستندات المطلوبة.</li>
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
