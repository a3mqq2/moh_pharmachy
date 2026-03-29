<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.otp_title') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 30px 15px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 500px; background-color: #ffffff; border: 1px solid #e0e0e0;">

                    <tr>
                        <td align="center" style="padding: 30px 30px 20px 30px; border-bottom: 1px solid #eee;">
                            <img src="{{ config('app.url') }}/logo-v.png" alt="{{ __('emails.ministry_of_health') }}" width="100" style="display: block; max-width: 100px; height: auto;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px;">

                            <p style="margin: 0 0 20px 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 15px; color: #333; line-height: 1.6;">
                                {!! __('emails.otp_greeting', ['name' => '<strong>' . $name . '</strong>']) !!}
                            </p>

                            <p style="margin: 0 0 25px 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 14px; color: #555; line-height: 1.7;">
                                @if($type == 'registration')
                                    {{ __('emails.otp_registration') }}
                                @elseif($type == 'login')
                                    {{ __('emails.otp_login') }}
                                @elseif($type == 'password_reset')
                                    {{ __('emails.otp_password_reset') }}
                                @else
                                    {{ __('emails.otp_default') }}
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <div style="display: inline-block; background-color: #f8f9fa; border: 1px dashed #ccc; padding: 20px 35px; letter-spacing: 8px; font-family: 'Courier New', monospace; font-size: 28px; font-weight: bold; color: #333;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 13px; color: #888; text-align: center;">
                                {!! __('emails.otp_expiry') !!}
                            </p>

                            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

                            <p style="margin: 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 12px; color: #999; line-height: 1.6;">
                                <strong style="color: #666;">{{ __('emails.otp_warning_label') }}</strong> {{ __('emails.otp_warning') }}
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 30px; background-color: #fafafa; border-top: 1px solid #eee;">
                            <p style="margin: 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 11px; color: #999; text-align: center; line-height: 1.6;">
                                {{ __('emails.otp_footer') }}
                                <br>
                                {{ __('emails.otp_copyright', ['year' => date('Y')]) }}
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
