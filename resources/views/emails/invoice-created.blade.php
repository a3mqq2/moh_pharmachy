<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.invoice_created_title') }}</title>
    <style>
        body {
            font-family: 'Traditional Arabic', 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            line-height: 1.8;
        }
        .email-container {
            max-width: 750px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
        }

        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #1a5f4a;
        }
        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1a5f4a;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 15px;
            color: #555;
        }

        .content {
            padding: 40px 35px;
        }

        .doc-number {
            text-align: left;
            color: #666;
            font-size: 13px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .greeting {
            margin-bottom: 30px;
        }
        .greeting p {
            color: #333;
            font-size: 15px;
            margin: 10px 0;
        }

        .company-info {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin: 25px 0;
        }
        .company-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .company-info td {
            padding: 8px;
            font-size: 14px;
            color: #444;
        }
        .company-info td:first-child {
            font-weight: 600;
            width: 35%;
        }

        .invoice-card {
            background-color: #fff;
            border: 2px solid #1a5f4a;
            padding: 25px;
            margin: 30px 0;
        }
        .invoice-header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a5f4a;
        }
        .invoice-number {
            font-size: 20px;
            font-weight: 700;
            color: #1a5f4a;
            margin-bottom: 8px;
        }
        .invoice-title {
            color: #555;
            font-size: 15px;
        }

        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .invoice-details td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        .invoice-details td:first-child {
            background-color: #f8f9fa;
            font-weight: 600;
            width: 40%;
            color: #555;
        }
        .invoice-details td:last-child {
            color: #333;
        }
        .invoice-details .total-row td {
            background-color: #1a5f4a;
            color: #ffffff;
            font-weight: 700;
            font-size: 16px;
            border-color: #1a5f4a;
        }

        .instructions {
            background-color: #fffbf0;
            border: 1px solid #e6d9b8;
            padding: 20px;
            margin: 25px 0;
        }
        .instructions h3 {
            color: #856404;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 700;
        }
        .instructions ol {
            margin: 0;
            padding-right: 20px;
            color: #856404;
        }
        .instructions li {
            margin: 10px 0;
            line-height: 1.7;
        }

        .action-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .action-section p {
            color: #555;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #1a5f4a;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 35px;
            font-weight: 600;
            font-size: 15px;
            border: 2px solid #1a5f4a;
        }
        .button:hover {
            background-color: #145239;
        }

        .note-box {
            background-color: #f8f9fa;
            border-right: 4px solid #1a5f4a;
            padding: 18px;
            margin: 25px 0;
        }
        .note-box p {
            margin: 0;
            color: #555;
            font-size: 14px;
            line-height: 1.8;
        }

        .signature {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #ddd;
        }
        .signature p {
            color: #555;
            font-size: 14px;
            margin: 8px 0;
        }
        .signature .dept {
            color: #1a5f4a;
            font-weight: 700;
            font-size: 15px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 25px;
            text-align: center;
            border-top: 3px solid #1a5f4a;
        }
        .footer p {
            margin: 6px 0;
            color: #666;
            font-size: 13px;
            line-height: 1.7;
        }
        .footer .copyright {
            color: #888;
            font-size: 12px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            .content {
                padding: 25px 20px;
            }
            .invoice-details td {
                font-size: 13px;
                padding: 10px;
            }
            .company-info td {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo-v.png') }}" alt="{{ __('emails.ministry_of_health') }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>{{ __('emails.invoice_created_ministry') }}</h1>
            <p>{{ __('emails.pharmacy_and_drug_control') }}</p>
        </div>

        <div class="content">
            <div class="doc-number">
                {{ __('emails.invoice_created_date', ['date' => now()->format('Y/m/d')]) }}
            </div>

            <div class="greeting">
                <p>{{ __('emails.dear_representative') }}</p>
                <p><strong>{{ __('emails.greeting') }}</strong></p>
                <p>{!! __('emails.invoice_created_body', ['company' => '<strong>' . $company->company_name . '</strong>']) !!}</p>
            </div>

            <div class="company-info">
                <table>
                    <tr>
                        <td>{{ __('emails.company_name_label') }}</td>
                        <td>{{ $company->company_name }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('emails.invoice_email') }}</td>
                        <td>{{ $company->email }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('emails.invoice_phone') }}</td>
                        <td>{{ $company->phone }}</td>
                    </tr>
                </table>
            </div>

            <div class="invoice-card">
                <div class="invoice-header">
                    <div class="invoice-number">{{ __('emails.invoice_number_prefix', ['number' => $invoice->invoice_number]) }}</div>
                    <p class="invoice-title">{{ $invoice->type_name }}</p>
                </div>

                <div class="invoice-details">
                    <table>
                        <tr>
                            <td>{{ __('emails.invoice_description') }}</td>
                            <td>{{ $invoice->description }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('emails.invoice_issue_date') }}</td>
                            <td>{{ $invoice->created_at->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('emails.invoice_due_date') }}</td>
                            <td>{{ $invoice->due_date->format('Y/m/d') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>{{ __('emails.invoice_total_amount') }}</td>
                            <td>{{ number_format($invoice->amount, 2) }} {{ __('emails.lyd') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="instructions">
                <h3>{{ __('emails.payment_instructions') }}</h3>
                <ol>
                    <li>{{ __('emails.invoice_step_1') }}</li>
                    <li>{{ __('emails.invoice_step_2') }}</li>
                    <li>{{ __('emails.invoice_step_3') }}</li>
                    <li>{{ __('emails.invoice_step_4') }}</li>
                    <li>{{ __('emails.invoice_step_5') }}</li>
                </ol>
            </div>

            <div class="action-section">
                <p>{{ __('emails.invoice_action_text') }}</p>
                <a href="{{ route('representative.invoices.show', $invoice->id) }}" class="button">
                    {{ __('emails.invoice_action_button') }}
                </a>
            </div>

            <div class="note-box">
                <p><strong>{{ __('emails.important_note') }}</strong> {{ __('emails.invoice_note') }}</p>
            </div>

            <div class="signature">
                <p>{{ __('emails.regards_formal_extra') }}</p>
                <p class="dept">{{ __('emails.pharmacy_drug_control_dept') }}</p>
                <p class="dept">{{ __('emails.ministry_health_libya_dept') }}</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ __('emails.footer_ministry') }}</strong></p>
            <p>{{ __('emails.footer_pharmacy') }}</p>
            <p>{{ __('emails.footer_contact') }}</p>
            <p class="copyright">{{ __('emails.footer_copyright_alt', ['year' => date('Y')]) }}<br>{{ __('emails.footer_auto_reply') }}</p>
        </div>
    </div>
</body>
</html>
