<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تفعيل الشركة</title>
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
            <h2>إشعار بتفعيل الشركة</h2>

            <p>السيد/ة الممثل المحترم،</p>
            <p>تحية طيبة وبعد،</p>

            <p>يسرنا إبلاغكم بأنه تم تفعيل شركتكم بشكل رسمي لدى وزارة الصحة - إدارة الصيدلة .</p>

            <table class="info-table">
                <tr>
                    <td>اسم الشركة:</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                @if($company->registration_number)
                <tr>
                    <td>رقم التسجيل:</td>
                    <td>{{ $company->registration_number }}</td>
                </tr>
                @endif
                <tr>
                    <td>نوع الترخيص:</td>
                    <td>
                        @if($company->license_type === 'company') شركة
                        @elseif($company->license_type === 'partnership') شراكة
                        @elseif($company->license_type === 'authorized_agent') وكيل معتمد
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>التخصص:</td>
                    <td>
                        @if($company->license_specialty === 'medicines') أدوية
                        @elseif($company->license_specialty === 'medical_supplies') مستلزمات طبية
                        @elseif($company->license_specialty === 'medical_equipment') أجهزة طبية
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>الحالة:</td>
                    <td><strong style="color: #1a5f4a;">مفعلة</strong></td>
                </tr>
            </table>

            <div class="note">
                <p><strong>ملاحظة:</strong></p>
                <p>يمكنكم الآن الوصول إلى جميع خدمات الوزارة من خلال لوحة التحكم الخاصة بكم.</p>
                <p>يرجى الاحتفاظ بجميع المستندات والإيصالات الخاصة بالتسجيل.</p>
            </div>

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
