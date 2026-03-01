<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة تسجيل شركة</title>
    <style>
        body {
            font-family: 'Traditional Arabic', 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            direction: rtl;
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
                <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>وزارة الصحة</h1>
            <p>إدارة الصيدلة والرقابة الدوائية</p>
        </div>

        <div class="content">
            <div class="doc-number">
                التاريخ: {{ now()->format('Y/m/d') }}
            </div>

            <div class="greeting">
                <p>السيد/ة الممثل المحترم،</p>
                <p><strong>تحية طيبة وبعد،</strong></p>
                <p>تشير إدارة الصيدلة والرقابة الدوائية بوزارة الصحة إلى طلب تسجيل شركتكم <strong>{{ $company->company_name }}</strong>، وتفيدكم بأنه تم قبول الطلب وإصدار فاتورة التسجيل وفقاً للبيانات التالية:</p>
            </div>

            <div class="company-info">
                <table>
                    <tr>
                        <td>اسم الشركة</td>
                        <td>{{ $company->company_name }}</td>
                    </tr>
                    <tr>
                        <td>البريد الإلكتروني</td>
                        <td>{{ $company->email }}</td>
                    </tr>
                    <tr>
                        <td>رقم الهاتف</td>
                        <td>{{ $company->phone }}</td>
                    </tr>
                </table>
            </div>

            <div class="invoice-card">
                <div class="invoice-header">
                    <div class="invoice-number">رقم الفاتورة: {{ $invoice->invoice_number }}</div>
                    <p class="invoice-title">{{ $invoice->type_name }}</p>
                </div>

                <div class="invoice-details">
                    <table>
                        <tr>
                            <td>البيان</td>
                            <td>{{ $invoice->description }}</td>
                        </tr>
                        <tr>
                            <td>تاريخ الإصدار</td>
                            <td>{{ $invoice->created_at->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <td>تاريخ الاستحقاق</td>
                            <td>{{ $invoice->due_date->format('Y/m/d') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>إجمالي المبلغ المستحق</td>
                            <td>{{ number_format($invoice->amount, 2) }} دينار ليبي</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="instructions">
                <h3>إجراءات الدفع المطلوبة:</h3>
                <ol>
                    <li>يرجى سداد المبلغ المستحق في أحد البنوك المعتمدة.</li>
                    <li>الحصول على إيصال الدفع الرسمي من البنك.</li>
                    <li>رفع صورة من إيصال الدفع عبر النظام الإلكتروني.</li>
                    <li>انتظار مراجعة واعتماد الإيصال من قبل الإدارة المختصة.</li>
                    <li>سيتم تفعيل الشركة وإصدار رقم القيد الرسمي بعد التأكد من سداد المبلغ.</li>
                </ol>
            </div>

            <div class="action-section">
                <p>للدخول إلى النظام ورفع إيصال الدفع، يرجى الضغط على الرابط التالي:</p>
                <a href="{{ route('representative.invoices.show', $invoice->id) }}" class="button">
                    عرض الفاتورة ورفع الإيصال
                </a>
            </div>

            <div class="note-box">
                <p><strong>ملاحظة هامة:</strong> يرجى إتمام عملية الدفع قبل التاريخ المحدد للاستحقاق تفادياً لأي تأخير في إنهاء إجراءات التسجيل. وفي حال وجود أي استفسار، يرجى التواصل مع الإدارة عبر الوسائل الرسمية المعتمدة.</p>
            </div>

            <div class="signature">
                <p>وتفضلوا بقبول فائق الاحترام والتقدير،،،</p>
                <p class="dept">إدارة الصيدلة والرقابة الدوائية</p>
                <p class="dept">وزارة الصحة - دولة ليبيا</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>وزارة الصحة - دولة ليبيا</strong></p>
            <p>إدارة الصيدلة والرقابة الدوائية</p>
            <p>البريد الإلكتروني: pharmacy@health.gov.ly | الهاتف: 218-21-XXXXXXX</p>
            <p class="copyright">جميع الحقوق محفوظة © {{ date('Y') }} وزارة الصحة<br>هذه رسالة إلكترونية آلية، يرجى عدم الرد عليها مباشرة.</p>
        </div>
    </div>
</body>
</html>
