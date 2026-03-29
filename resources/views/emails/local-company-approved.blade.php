<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.local_company_approved_title') }}</title>
    <style>
        body {
            font-family: 'Traditional Arabic', 'Almarai', Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 30px 20px;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
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

        .registration-box {
            text-align: center;
            border: 2px solid #000;
            padding: 15px;
            margin: 25px auto;
            width: 250px;
        }

        .registration-label {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .registration-number {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
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

        .notes-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #000;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .notes-list {
            margin: 0;
            padding-right: 25px;
            font-size: 13px;
        }

        .notes-list li {
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
            <img src="{{ asset('logo-v.png') }}" alt="{{ __('emails.ministry_logo_alt') }}" class="logo">
            <div class="header-text">{{ __('emails.state_of_libya') }}</div>
            <div class="header-text bold">{{ __('emails.ministry_of_health') }}</div>
            <div class="header-text">{{ __('emails.pharmacy_department') }}</div>
        </div>

        <div class="content">
            <div class="document-title">{{ __('emails.local_company_approved_title') }}</div>

            <div class="recipient">
                <div>{{ __('emails.local_company_approved_dear', ['name' => $company->manager_name]) }}</div>
                <div>{{ __('emails.local_company_approved_manager', ['company' => $company->company_name]) }}</div>
                <div>{{ __('emails.local_company_approved_respected') }}</div>
            </div>

            <div class="message-text">
                <p>{{ __('emails.salam') }}</p>
                <p>
                    {{ __('emails.local_company_approved_body') }}
                </p>
            </div>

            <div class="registration-box">
                <div class="registration-label">{{ __('emails.registration_number') }}</div>
                <div class="registration-number">{{ $company->registration_number }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">{{ __('emails.company_name_label') }}</td>
                    <td>{{ $company->company_name }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('emails.classification') }}</td>
                    <td>{{ $company->company_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('emails.license_type') }}</td>
                    <td>{{ $company->license_type_name }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('emails.specialty') }}</td>
                    <td>{{ $company->license_specialty_name }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('emails.registration_date') }}</td>
                    <td>{{ $company->registration_date?->format('Y-m-d') }}</td>
                </tr>
            </table>

            <div class="notes-section">
                <div class="notes-title">{{ __('emails.important_notes') }}</div>
                <ul class="notes-list">
                    <li>{{ __('emails.local_approved_note_1') }}</li>
                    <li>{{ __('emails.local_approved_note_2') }}</li>
                    <li>{{ __('emails.local_approved_note_3') }}</li>
                </ul>
            </div>

            <div class="closing">
                <p>{{ __('emails.regards_formal') }}</p>
                <p>{{ __('emails.pharmacy_department_full') }}</p>
            </div>
        </div>

        <div class="footer">
            {{ __('emails.footer_auto_notice', ['date' => now()->format('Y-m-d')]) }}
        </div>
    </div>
</body>
</html>
