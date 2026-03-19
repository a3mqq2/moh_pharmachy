<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('logo-primary.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            direction: rtl;
        }

        .verification-card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            max-width: 580px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .card-header {
            background: #0d47a1;
            color: white;
            padding: 20px 30px;
            text-align: center;
            border-bottom: 3px solid #0a3d8f;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .card-header img {
            width: 55px;
            height: 55px;
        }

        .header-titles h2 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .header-titles h3 {
            font-size: 12px;
            font-weight: 600;
            opacity: 0.9;
        }

        .card-header h1 {
            font-size: 15px;
            font-weight: 700;
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .status-section {
            text-align: center;
            padding: 20px 30px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 30px;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .status-success {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #4caf50;
        }

        .status-warning {
            background: #fff8e1;
            color: #e65100;
            border: 1px solid #ff9800;
        }

        .status-danger {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ef5350;
        }

        .status-secondary {
            background: #f5f5f5;
            color: #424242;
            border: 1px solid #9e9e9e;
        }

        .status-info {
            background: #e3f2fd;
            color: #0d47a1;
            border: 1px solid #42a5f5;
        }

        .status-primary {
            background: #e8eaf6;
            color: #1a237e;
            border: 1px solid #5c6bc0;
        }

        .card-body {
            padding: 20px 30px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 11px 5px;
            font-size: 14px;
        }

        .info-table .label {
            color: #555;
            font-weight: 600;
            width: 40%;
        }

        .info-table .value {
            color: #1a1a1a;
            font-weight: 700;
        }

        .card-footer {
            background: #fafafa;
            padding: 14px 30px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #eee;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            .card-body { padding: 15px; }
            .card-header { padding: 15px; }
            .status-section { padding: 15px; }
            .info-table td { font-size: 13px; padding: 9px 3px; }
            .header-top { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="card-header">
            <div class="header-top">
                <img src="{{ asset('logo-primary.png') }}" alt="">
                <div class="header-titles">
                    <h2>وزارة الصحة</h2>
                    <h3>إدارة الصيدلة والمستلزمات الطبية</h3>
                </div>
            </div>
            <h1>{{ $title }}</h1>
        </div>

        <div class="status-section">
            <span class="status-badge status-{{ $statusColor }}">{{ $status }}</span>
        </div>

        <div class="card-body">
            <table class="info-table">
                @foreach($fields as $label => $value)
                    @if($value)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="value">{{ $value }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>

        <div class="card-footer">
            وزارة الصحة &copy; {{ date('Y') }} &mdash; هذه الصفحة للتحقق من صحة الشهادة الصادرة من إدارة الصيدلة والمستلزمات الطبية
        </div>
    </div>
</body>
</html>
