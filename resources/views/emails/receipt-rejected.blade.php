<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.receipt_rejected_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', 'Arial', 'Tahoma', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
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

        .rejection-box {
            background-color: #fff5f5;
            border-right: 3px solid #c41e3a;
            padding: 15px;
            margin: 20px 0;
        }
        .rejection-box p {
            margin: 5px 0;
            font-size: 14px;
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
        .note ol {
            margin: 10px 0;
            padding-right: 20px;
        }
        .note li {
            margin: 5px 0;
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
                <img src="{{ asset('logo-v.png') }}" alt="{{ __('emails.ministry_of_health') }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>{{ __('emails.ministry_of_health_libya') }}</h1>
            <p>{{ __('emails.pharmacy_and_drug_control') }}</p>
            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0 0 0;">
        </div>

        <div class="content">
            <h2>{{ __('emails.receipt_rejected_heading') }}</h2>

            <p>{{ __('emails.dear_representative') }}</p>
            <p>{{ __('emails.greeting') }}</p>

            <p>{{ __('emails.receipt_rejected_body') }}</p>

            <table class="info-table">
                <tr>
                    <td>{{ __('emails.company_name_colon') }}</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.invoice_number_label') }}</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.amount_label') }}</td>
                    <td>{{ number_format($invoice->amount, 2) }} {{ __('emails.lyd') }}</td>
                </tr>
            </table>

            <div class="rejection-box">
                <p><strong>{{ __('emails.receipt_rejection_reason') }}</strong></p>
                <p>{{ $rejectionReason }}</p>
            </div>

            <div class="note">
                <p><strong>{{ __('emails.required_steps') }}</strong></p>
                <ol>
                    <li>{{ __('emails.receipt_rejected_step_1') }}</li>
                    <li>{{ __('emails.receipt_rejected_step_2') }}</li>
                    <li>{{ __('emails.receipt_rejected_step_3') }}</li>
                    <li>{{ __('emails.receipt_rejected_step_4') }}</li>
                    <li>{{ __('emails.receipt_rejected_step_5') }}</li>
                </ol>
            </div>

            <p><strong>{{ __('emails.important_note') }}</strong> {{ __('emails.receipt_rejected_note') }}</p>

            <p style="margin-top: 30px;">{{ __('emails.regards') }}</p>
            <p><strong>{{ __('emails.ministry_pharmacy_department') }}</strong></p>
        </div>

        <div class="footer">
            <p><strong>{{ __('emails.footer_ministry') }}</strong></p>
            <p>{{ __('emails.footer_pharmacy') }}</p>
            <p>{{ __('emails.footer_contact') }}</p>
            <p style="margin-top: 10px;">{{ __('emails.footer_copyright', ['year' => date('Y')]) }}</p>
            <p>{{ __('emails.footer_auto_email') }}</p>
        </div>
    </div>
</body>
</html>
