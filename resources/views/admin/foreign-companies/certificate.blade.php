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
            top: 340px;
            left: 50px;
            right: 50px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            padding: 10px 30px;
        }

        .certificate-body {
            text-align: center;
            line-height: 1.8;
            font-size: 14pt;
            color: #1a1a1a;
        }

        .certificate-body p {
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #b88f5c;
            margin: 15px 0;
        }

        .info-row {
            text-align: center;
            margin: 12px 0;
            font-size: 13pt;
        }

        .registration-info {
            text-align: center;
            margin: 15px 0;
            font-size: 12pt;
        }

        .registration-date-row {
            text-align: center;
            margin: 15px 0;
            font-size: 12pt;
        }

        .registration-date-row p {
            margin: 5px 0;
        }

        .validity-notice {
            font-size: 11pt;
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
            color: #b88f5c;
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
    <button class="print-button no-print" onclick="window.print()">Print Certificate</button>

    <div class="certificate-container">
        <div class="certificate-content">
            <div class="certificate-body">
                <p>
                    This is to certify that the manufacturing site documents submitted for registration
                    <br>
                    to the Pharmacy Department, Ministry of Health have been reviewed and approved for:
                </p>

                <p><span class="label">Company:</span></p>
                <p class="company-name">{{ $foreignCompany->company_name }}</p>

                <div class="info-row">
                    <p>
                        <span class="label">Address:</span>
                        <span class="value">{{ $foreignCompany->address }}</span>
                    </p>
                </div>

                <div class="info-row">
                    <p>
                        <span class="label">Country:</span>
                        <span class="value">{{ $foreignCompany->country_en }}</span>
                    </p>
                </div>

                <div class="registration-info">
                    <p>
                        <span class="label">Entity Type:</span>
                        <span class="value">{{ ucfirst($foreignCompany->entity_type) }}</span>
                        <span style="margin: 0 20px;">-</span>
                        <span class="label">Activity Type:</span>
                        <span class="value">{{ ucfirst(str_replace('_', ' ', $foreignCompany->activity_type)) }}</span>
                    </p>
                </div>

                <div class="registration-date-row">
                    <p>
                        <span class="label">Registration Date:</span>
                        <span class="value">{{ $foreignCompany->approved_at?->format('Y-m-d') }}</span>
                    </p>
                    <p class="validity-notice">
                        This certificate is valid for one year from the date of issue.
                    </p>
                </div>
            </div>

            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-title">Prepared By</div>
                    <div class="signature-line"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-title">Head of Registration & Inspection</div>
                    <div class="signature-line"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-title">Director of Pharmacy Department</div>
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
