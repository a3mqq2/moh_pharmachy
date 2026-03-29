<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.announcement_title', ['title' => $announcement->title]) }}</title>
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
        .priority-bar {
            padding: 10px 30px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
        }
        .priority-urgent {
            background-color: #fee2e2;
            color: #991b1b;
            border-bottom: 3px solid #ef4444;
        }
        .priority-important {
            background-color: #fef3c7;
            color: #92400e;
            border-bottom: 3px solid #f59e0b;
        }
        .priority-normal {
            background-color: #dbeafe;
            color: #1e40af;
            border-bottom: 3px solid #3b82f6;
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
        .announcement-body {
            background-color: #f9f9f9;
            border-right: 3px solid #1a5f4a;
            padding: 20px;
            margin: 20px 0;
            white-space: pre-line;
            font-size: 15px;
            line-height: 1.9;
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

        @if($announcement->priority !== 'normal')
        <div class="priority-bar priority-{{ $announcement->priority }}">
            @if($announcement->priority === 'urgent')
                {{ __('emails.announcement_urgent') }}
            @else
                {{ __('emails.announcement_important') }}
            @endif
        </div>
        @endif

        <div class="content">
            <h2>{{ $announcement->title }}</h2>

            <p>{{ __('emails.dear_representative_named', ['name' => $recipientName]) }}</p>
            <p>{{ __('emails.greeting') }}</p>

            <div class="announcement-body">{{ $announcement->body }}</div>

            <p style="margin-top: 30px;">{{ __('emails.regards') }}</p>
            <p><strong>{{ __('emails.ministry_pharmacy_department') }}</strong></p>
        </div>

        <div class="footer">
            <p><strong>{{ __('emails.footer_ministry') }}</strong></p>
            <p>{{ __('emails.footer_pharmacy') }}</p>
            <p>{{ __('emails.footer_contact') }}</p>
            <p style="margin-top: 10px;">&copy; {{ __('emails.footer_copyright', ['year' => date('Y')]) }}</p>
            <p>{{ __('emails.footer_auto_email') }}</p>
        </div>
    </div>
</body>
</html>
