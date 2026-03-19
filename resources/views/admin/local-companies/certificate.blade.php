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

        .qr-section {
            position: absolute;
            bottom: -29px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qr-code {
            width: 60px;
            height: 60px;
        }

        .qr-label {
            font-size: 8pt;
            color: #666;
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

        .actions-bar {
            position: fixed;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .action-button {
            padding: 10px 30px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .action-button.print { background: #0d47a1; }
        .action-button.print:hover { background: #1565c0; }
        .action-button.download { background: #2e7d32; }
        .action-button.download:hover { background: #388e3c; }
        .action-button:disabled { opacity: 0.6; cursor: wait; }
    </style>
</head>
<body>
    <div class="actions-bar no-print">
        <button class="action-button print" onclick="window.print()">طباعة الشهادة</button>
        <button class="action-button download" id="downloadBtn" onclick="downloadPDF()">تنزيل PDF</button>
    </div>

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

            <div class="qr-section">
                <div class="qr-code" id="qrcode"></div>
                <span class="qr-label">تحقق من هنا</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    new QRCode(document.getElementById('qrcode'), {
        text: '{{ route('verify.local-company', $localCompany->id) }}',
        width: 60,
        height: 60,
        colorDark: '#0d47a1',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
    function downloadPDF() {
        var btn = document.getElementById('downloadBtn');
        btn.disabled = true;
        btn.textContent = 'جاري التحميل...';
        var element = document.querySelector('.certificate-container');
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff'
        }).then(function(canvas) {
            var imgData = canvas.toDataURL('image/jpeg', 0.95);
            var pdf = new jspdf.jsPDF('p', 'mm', 'a4');
            pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);
            pdf.save('شهادة_تسجيل_{{ $localCompany->company_name }}.pdf');
            btn.disabled = false;
            btn.textContent = 'تنزيل PDF';
        }).catch(function() {
            btn.disabled = false;
            btn.textContent = 'تنزيل PDF';
        });
    }
    </script>
</body>
</html>
