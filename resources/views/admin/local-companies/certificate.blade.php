<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة تسجيل - {{ $localCompany->company_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: white;
        }

        .certificate-container {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url('{{ asset('certificates/local-registration.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .certificate-content {
            position: absolute;
            top: 230px;
            left: 50px;
            right: 50px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            padding: 10px 30px;
        }

        .certificate-body {
            text-align: center;
            line-height: 1.6;
            font-size: 15pt;
            color: #1a1a1a;
            margin-top: 200px;
        }

        .certificate-body p {
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #0d47a1;
            margin: 10px 0;
        }

        .info-row {
            text-align: center;
            margin: 12px 0;
            font-size: 14pt;
        }

        .registration-info {
            text-align: center;
            margin: 15px 0;
            font-size: 13pt;
        }

        .registration-date-row {
            text-align: center;
            margin: 15px 0;
            font-size: 13pt;
        }

        .registration-date-row p {
            margin: 5px 0;
        }

        .validity-notice {
            font-size: 12pt;
            color: #666;
            font-style: italic;
        }

        .signatures {
            position: absolute;
            bottom: 80px;
            left: 50px;
            right: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 11pt;
            color: #333;
        }

        .signature-block {
            text-align: center;
            width: 30%;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 40px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            color: #0d47a1;
            font-weight: bold;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .certificate-container {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 30px;
            background: #0d47a1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #1565c0;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">طباعة الشهادة</button>

    <div class="certificate-container">
        <div class="certificate-content">
            <div class="certificate-body">
                <p>
                    بناءً على إستيفاء الوثائق المقدمة لغرض إعادة التسجيل بإدارة الصيدلة والمستلزمات الطبية
                    <br>
                    بوزارة الصحة منحت هذه الشهادة إلى:
                </p>

                <p><span class="label">شركـــــة :</span></p>
                <p class="company-name">{{ $localCompany->company_name }}</p>

                <div class="info-row">
                    <p>
                        <span class="label">العنوان :</span>
                        <span class="value">{{ $localCompany->company_address }}{{ $localCompany->street ? ' - ' . $localCompany->street : '' }}{{ $localCompany->city ? ' - ' . $localCompany->city : '' }}</span>
                    </p>
                </div>

                <div class="registration-info">
                    <p>
                        <span class="label">المجال :</span>
                        <span class="value">{{ $localCompany->license_specialty_name }}</span>
                        <span style="margin: 0 20px;">-</span>
                        <span class="label">رقم التسجيل :</span>
                        <span class="value">{{ $localCompany->registration_number }}</span>
                    </p>
                </div>

                <div class="registration-date-row">
                    <p>
                        <span class="label">تاريخ التسجيل :</span>
                        <span class="value">{{ $localCompany->registration_date?->format('Y-m-d') }}</span>
                    </p>
                    <p class="validity-notice">
                        صلاحية هذه الشهادة لمدة سنة من تاريخ إصدارها.
                    </p>
                </div>
            </div>

            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-title">إعداد</div>
                    <div class="signature-line"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-title">رئيس قسم التسجيل والتفتيش</div>
                    <div class="signature-line"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-title">مدير إدارة الصيدلة</div>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when ready (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
