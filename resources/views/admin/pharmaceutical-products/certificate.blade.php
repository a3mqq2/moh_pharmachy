<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Registration Certificate - {{ $product->product_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .certificate-container {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url('{{ asset('certificates/drug.jpg') }}');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            padding: 0;
        }

        .content {
            position: absolute;
            top: 350px;
            left: 70px;
            right: 70px;
            z-index: 10;
            color: #000;
            font-size: 11pt;
            line-height: 1.6;
            text-align: justify;
        }

        .intro-text {
            margin-bottom: 18px;
            text-indent: 25px;
        }

        .field-row {
            margin-bottom: 8px;
            display: flex;
            align-items: baseline;
        }

        .field-label {
            font-weight: 600;
            min-width: 170px;
            flex-shrink: 0;
            font-size: 10.5pt;
        }

        .field-value {
            flex: 1;
            border-bottom: 1px dotted #333;
            padding: 0 8px 2px 8px;
            min-height: 20px;
        }

        .validity-text {
            margin: 18px 0;
            text-indent: 25px;
        }

        .footer-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }

        .footer-left {
            width: 48%;
        }

        .footer-right {
            width: 48%;
        }

        .footer-field {
            margin-bottom: 12px;
            display: flex;
            align-items: baseline;
        }

        .footer-label {
            font-weight: 600;
            min-width: 120px;
            font-size: 10.5pt;
        }

        .footer-value {
            flex: 1;
            border-bottom: 1px solid #333;
            margin-left: 8px;
            padding-bottom: 2px;
            min-height: 20px;
        }

        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 45%;
        }

        .signature-line {
            margin-bottom: 10px;
            font-size: 10pt;
        }

        .signature-line strong {
            font-weight: 600;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .certificate-container {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
@php
    function translate($value, $category, $translations) {
        $value = trim($value);

        if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
            if (in_array($value, ['أخرى', 'اخرى', 'أخري', 'اخري', 'اخري', 'أخرئ'])) {
                return 'Other';
            }
        }

        return $translations[$category][$value] ?? $value;
    }
@endphp
<body>
    <div class="certificate-container">
        <div class="content">
            <p class="intro-text">
                In accordance with section of drug authority law, it is hereby certified that the undermentioned pharmaceutical product has been granted registration with reference to the application made by:
            </p>

            <div class="field-row">
                <span class="field-label">Applicant name:</span>
                <span class="field-value">{{ $product->foreignCompany->company_name }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Address:</span>
                <span class="field-value">{{ $product->foreignCompany->address ?? '' }}@if($product->foreignCompany->address), @endif{{ translate($product->foreignCompany->country, 'countries', $translations) }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Name of product:</span>
                <span class="field-value">{{ $product->trade_name ?? $product->product_name }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Manufacturer:</span>
                <span class="field-value">{{ $product->foreignCompany->company_name }}, {{ translate($product->origin, 'countries', $translations) }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Dosage form:</span>
                <span class="field-value">{{ translate($product->pharmaceutical_form, 'pharmaceutical_forms', $translations) }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Presentation:</span>
                <span class="field-value">{{ translate($product->packaging, 'packaging', $translations) }}, {{ $product->quantity }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Therapeutic Category:</span>
                <span class="field-value">{{ translate($product->usage_methods_text, 'usage_methods', $translations) }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Strength & Composition:</span>
                <span class="field-value">{{ $product->concentration }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Sales Category:</span>
                <span class="field-value">{{ translate($product->free_sale, 'free_sale', $translations) }}</span>
            </div>

            <p class="validity-text">
                This certificate shall be valid until (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) unless earlier suspended or cancelled. No specifications of the product shall be made without the approval of Libyan Authority.
            </p>

            <div class="footer-section">
                <div class="footer-left">
                    <div class="footer-field">
                        <span class="footer-label">Registration no:</span>
                        <span class="footer-value">{{ str_pad($product->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="footer-field">
                        <span class="footer-label">Date of issue:</span>
                        <span class="footer-value">{{ $product->final_approved_at ? $product->final_approved_at->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="footer-right">
                    <div class="footer-field">
                        <span class="footer-label">Ref:</span>
                        <span class="footer-value">DRUG-{{ date('Y') }}-{{ $product->id }}</span>
                    </div>
                </div>
            </div>

            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line">
                        <strong>Signature:</strong> .....................................
                    </div>
                    <div class="signature-line">
                        <strong>Name:</strong> .....................................
                    </div>
                    <div class="signature-line">
                        <strong>Designation:</strong> .....................................
                    </div>
                </div>

                <div class="signature-block">
                    <div class="signature-line">
                        <strong>Signature:</strong> .....................................
                    </div>
                    <div class="signature-line">
                        <strong>Name:</strong> .....................................
                    </div>
                    <div class="signature-line">
                        <strong>Designation:</strong> .....................................
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
