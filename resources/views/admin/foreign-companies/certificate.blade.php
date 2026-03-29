<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Certificate - {{ $foreignCompany->company_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: #e0e0e0;
            margin: 0;
            padding: 0;
        }

        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-image: url('{{ asset('certificates/drug.png') }}');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
            overflow: hidden;
        }

        .certificate-title {
            position: absolute;
            top: 17%;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 10;
        }

        .certificate-title .line4 {
            font-size: 20pt;
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .content {
            position: absolute;
            top: 28%;
            left: 12%;
            right: 15%;
            bottom: 20%;
            z-index: 10;
            color: #000;
            font-size: 11pt;
            line-height: 1.6;
            text-align: justify;
            display: flex;
            flex-direction: column;
        }

        .approval-text {
            text-align: center;
            font-size: 11pt;
            line-height: 1.7;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .reg-numbers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 6px 0;
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
            font-size: 9pt;
        }

        .reg-num-value {
            font-weight: bold;
            color: #1a1a1a;
            font-size: 11pt;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px 10px;
            font-size: 11pt;
            vertical-align: top;
        }

        .info-label {
            width: 30%;
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
            margin-top: 10px;
            padding-top: 8px;
        }

        .validity-notice span {
            font-weight: bold;
        }

        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: auto;
        }

        .signature-block {
            width: 30%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 35px;
            padding-top: 5px;
        }

        .signature-title {
            font-weight: bold;
            font-size: 9pt;
        }

        .qr-block {
            width: 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .prepared-by {
            font-size: 10pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .qr-code {
            width: 55px;
            height: 55px;
        }

        .qr-label {
            font-size: 7pt;
            color: #666;
            font-weight: bold;
            margin-top: 3px;
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

        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }

            .certificate-container {
                page-break-after: avoid;
                page-break-inside: avoid;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="actions-bar no-print">
        <button class="action-button print" onclick="window.print()">Print Certificate</button>
        <button class="action-button download" id="downloadBtn" onclick="downloadPDF()">Download PDF</button>
    </div>

    <div class="certificate-container">
        <div class="certificate-title">
            <div class="line1">State of Libya</div>
            <div class="line2">Ministry of Health</div>
            <div class="line3">Pharmacy Department</div>
            <div class="line4">Registration Certificate of Manufacturing Site</div>
        </div>

        <div class="content">
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

            <div class="bottom-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-title">DIRECTOR OF PHARMACY<br>DEPARTMENT</div>
                </div>

                <div class="qr-block">
                    <div class="prepared-by">PREPARED BY:</div>
                    <div class="qr-code" id="qrcode"></div>
                    <span class="qr-label">Verify Here</span>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-title">HEAD OF REGISTRATION<br>SECTION</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    new QRCode(document.getElementById('qrcode'), {
        text: '{{ route('verify.foreign-company', $foreignCompany->id) }}',
        width: 55,
        height: 55,
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
            var pdf = new jspdf.jsPDF('l', 'mm', 'a4');
            pdf.addImage(imgData, 'JPEG', 0, 0, 297, 210);
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
