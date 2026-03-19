<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Certificate - {{ $foreignCompany->company_name }}</title>
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
            direction: ltr;
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: white;
        }

        .certificate-container {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url('{{ asset('certificates/foreign-registration.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .certificate-content {
            position: absolute;
            top: 350px;
            left: 50px;
            right: 50px;
            bottom: 80px;
            display: flex;
            flex-direction: column;
            padding: 10px 30px;
        }

        .approval-text {
            text-align: center;
            font-size: 12pt;
            line-height: 1.8;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .reg-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .reg-table td {
            padding: 4px 10px;
            font-size: 11pt;
            vertical-align: top;
        }

        .reg-table .label-cell {
            width: 35%;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        .reg-table .sep-cell {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }

        .reg-table .value-cell {
            width: 62%;
            color: #1a1a1a;
        }

        .reg-numbers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 8px 0;
            border-top: 1px solid #999;
            border-bottom: 1px solid #999;
        }

        .reg-num-item {
            text-align: center;
            font-size: 10pt;
        }

        .reg-num-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 2px;
        }

        .reg-num-value {
            font-weight: bold;
            color: #1a1a1a;
            font-size: 11pt;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 6px 10px;
            font-size: 11.5pt;
            vertical-align: top;
        }

        .info-label {
            width: 38%;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        .info-sep {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }

        .info-value {
            text-transform: uppercase;
            color: #1a1a1a;
        }

        .validity-notice {
            text-align: left;
            font-size: 10.5pt;
            margin-top: 15px;
            padding-top: 10px;
        }

        .validity-notice span {
            font-weight: bold;
        }

        .signatures {
            position: absolute;
            bottom: 60px;
            left: 50px;
            right: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            color: #333;
        }

        .signature-block {
            text-align: center;
            width: 45%;
        }

        .signature-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 45px;
            padding-top: 5px;
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

        .prepared-by {
            position: absolute;
            bottom: 155px;
            left: 50px;
            right: 50px;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #333;
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
        <button class="action-button print" onclick="window.print()">Print Certificate</button>
        <button class="action-button download" id="downloadBtn" onclick="downloadPDF()">Download PDF</button>
    </div>

    <div class="certificate-container">
        <div class="certificate-content">
            <div class="approval-text">
                The High Supreme Committee for Registration of Pharmaceutical Companies
                and Products has approved
                @if($foreignCompany->meeting_number)
                    in its meeting No. (<strong>{{ $foreignCompany->meeting_number }}</strong>)
                @endif
                @if($foreignCompany->meeting_date)
                    on (<strong>{{ $foreignCompany->meeting_date->format('d/m/Y') }}</strong>),
                @endif
                the registration of the following company:
            </div>

            <div class="reg-numbers">
                <div class="reg-num-item">
                    <span class="reg-num-label">REGISTRATION NO.</span>
                    <span class="reg-num-value">{{ $foreignCompany->registration_number ?? '-' }}</span>
                </div>
                <div class="reg-num-item">
                    <span class="reg-num-label">REFERENCE NO.</span>
                    <span class="reg-num-value">{{ $foreignCompany->reference_number }}</span>
                </div>
                <div class="reg-num-item">
                    <span class="reg-num-label">DATE OF ISSUE</span>
                    <span class="reg-num-value">{{ $foreignCompany->approved_at?->format('d/m/Y') ?? '-' }}</span>
                </div>
            </div>

            <table class="info-table">
                <tr>
                    <td class="info-label">MANUFACTURING NAME</td>
                    <td class="info-sep">:</td>
                    <td class="info-value">{{ $foreignCompany->company_name }}</td>
                </tr>
                <tr>
                    <td class="info-label">COUNTRY OF ORIGIN</td>
                    <td class="info-sep">:</td>
                    <td class="info-value">{{ $foreignCompany->country_en }}</td>
                </tr>
                <tr>
                    <td class="info-label">MANUFACTURER ADDRESS</td>
                    <td class="info-sep">:</td>
                    <td class="info-value">{{ $foreignCompany->address }}</td>
                </tr>
                <tr>
                    <td class="info-label">PRODUCTION LINE(S)</td>
                    <td class="info-sep">:</td>
                    <td class="info-value">{{ $foreignCompany->activity_type_en }}</td>
                </tr>
                <tr>
                    <td class="info-label">LOCAL AGENT</td>
                    <td class="info-sep">:</td>
                    <td class="info-value">{{ $foreignCompany->localCompany?->company_name ?? '-' }}</td>
                </tr>
            </table>

            <div class="validity-notice">
                &#9734; <span>VALID FOR FIVE YEARS FROM DATE OF ISSUE.</span>
            </div>

            <div class="prepared-by">PREPARED BY:</div>

            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-title">DIRECTOR OF PHARMACY<br>DEPARTMENT</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-title">HEAD OF REGISTRATION<br>SECTION</div>
                </div>
            </div>

            <div class="qr-section">
                <div class="qr-code" id="qrcode"></div>
                <span class="qr-label">Verify Here</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    new QRCode(document.getElementById('qrcode'), {
        text: '{{ route('verify.foreign-company', $foreignCompany->id) }}',
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
        btn.textContent = 'Downloading...';
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
            pdf.save('Registration_Certificate_{{ $foreignCompany->company_name }}.pdf');
            btn.disabled = false;
            btn.textContent = 'Download PDF';
        }).catch(function() {
            btn.disabled = false;
            btn.textContent = 'Download PDF';
        });
    }
    </script>
</body>
</html>
