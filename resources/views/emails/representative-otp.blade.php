<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق</title>
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

                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 500px; background-color: #ffffff; border: 1px solid #e0e0e0;">

                    <!-- Logo Section -->
                    <tr>
                        <td align="center" style="padding: 30px 30px 20px 30px; border-bottom: 1px solid #eee;">
                            <img src="{{ config('app.url') }}/logo-v.png" alt="وزارة الصحة" width="100" style="display: block; max-width: 100px; height: auto;">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">

                            <!-- Greeting -->
                            <p style="margin: 0 0 20px 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 15px; color: #333; line-height: 1.6;">
                                السلام عليكم <strong>{{ $name }}</strong>،
                            </p>

                            <!-- Message -->
                            <p style="margin: 0 0 25px 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 14px; color: #555; line-height: 1.7;">
                                @if($type === 'registration')
                                    لإتمام عملية التسجيل في بوابة الشركات، يرجى إدخال رمز التحقق التالي:
                                @elseif($type === 'login')
                                    لتسجيل الدخول إلى حسابك، يرجى إدخال رمز التحقق التالي:
                                @elseif($type === 'password_reset')
                                    لإعادة تعيين كلمة المرور، يرجى إدخال رمز التحقق التالي:
                                @else
                                    يرجى إدخال رمز التحقق التالي:
                                @endif
                            </p>

                            <!-- OTP Code -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <div style="display: inline-block; background-color: #f8f9fa; border: 1px dashed #ccc; padding: 20px 35px; letter-spacing: 8px; font-family: 'Courier New', monospace; font-size: 28px; font-weight: bold; color: #333;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Note -->
                            <p style="margin: 20px 0 0 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 13px; color: #888; text-align: center;">
                                هذا الرمز صالح لمدة <strong>10 دقائق</strong>
                            </p>

                            <!-- Divider -->
                            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

                            <!-- Warning -->
                            <p style="margin: 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 12px; color: #999; line-height: 1.6;">
                                <strong style="color: #666;">تنبيه:</strong> لا تشارك هذا الرمز مع أي شخص.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; background-color: #fafafa; border-top: 1px solid #eee;">
                            <p style="margin: 0; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 11px; color: #999; text-align: center; line-height: 1.6;">
                                وزارة الصحة الليبية - إدارة الصيدلة 
                                <br>
                                © {{ date('Y') }} جميع الحقوق محفوظة
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
