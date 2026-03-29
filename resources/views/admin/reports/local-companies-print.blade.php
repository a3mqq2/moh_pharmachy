<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('reports.local_companies_report') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', 'Tahoma', sans-serif;
            direction: rtl;
            padding: 15mm 20mm;
            color: #1a1a1a;
            background: #fff;
            font-size: 13px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 3px double #1a5f4a;
        }

        .header-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 10px;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header .ministry { font-size: 20px; font-weight: 800; color: #1a5f4a; margin-bottom: 2px; }
        .header .department { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .header .report-title {
            font-size: 18px;
            font-weight: 700;
            background: #1a5f4a;
            color: #fff;
            display: inline-block;
            padding: 4px 30px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 11px;
            color: #555;
        }

        .meta-bar .filters span {
            display: inline-block;
            background: #fff;
            border: 1px solid #ddd;
            padding: 3px 12px;
            border-radius: 3px;
            margin-inline-start: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .meta-bar .report-date { font-weight: 700; color: #1a5f4a; font-size: 13px; }

        .total-badge {
            display: inline-block;
            background: #1a5f4a;
            color: #fff;
            padding: 3px 16px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead { display: table-header-group; }

        th {
            background: #1a5f4a;
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            padding: 8px 8px;
            text-align: right;
            border: 1px solid #15503f;
            white-space: nowrap;
        }

        td {
            padding: 7px 8px;
            text-align: right;
            border: 1px solid #d0d0d0;
            font-size: 12px;
            color: #222;
        }

        tbody tr:nth-child(even) { background: #f5f8f7; }
        tbody tr:hover { background: #e8f5e9; }

        .row-num {
            text-align: center;
            font-weight: bold;
            color: #1a5f4a;
            width: 30px;
        }

        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 2px solid #1a5f4a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            color: #888;
        }

        .footer .page-info { font-style: italic; }
        .footer div { font-size: 11px; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            @page { margin: 10mm; size: landscape; }
            tbody tr:hover { background: inherit; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">
            <img src="{{ asset('logo-v.png') }}" alt="">
        </div>
        <div class="ministry">{{ __('general.ministry_of_health') }} - {{ __('general.state_of_libya') }}</div>
        <div class="department">{{ __('general.pharmacy_drug_control') }}</div>
        <div class="report-title">{{ __('reports.local_companies_report') }}</div>
    </div>

    <div class="meta-bar">
        <div class="filters">
            @if(request('status'))
                <span>{{ __('general.status') }}: {{ ['pending' => __('companies.status_pending_review'), 'uploading_documents' => __('companies.status_uploading_docs'), 'approved' => __('companies.approved_label'), 'active' => __('companies.status_active'), 'rejected' => __('companies.status_rejected')][request('status')] ?? request('status') }}</span>
            @endif
            @if(request('from_date'))
                <span>{{ __('general.from_date') }}: {{ request('from_date') }}</span>
            @endif
            @if(request('to_date'))
                <span>{{ __('general.to_date') }}: {{ request('to_date') }}</span>
            @endif
            <span class="total-badge">{{ __('general.total_companies') }}: {{ $companies->count() }}</span>
        </div>
        <div class="report-date">{{ date('Y-m-d') }}</div>
    </div>

    @php
        $allCols = [0,1,2,3,4,5,6,7,8,9,10,11,12];
        $visibleCols = request('cols') ? array_map('intval', explode(',', request('cols'))) : $allCols;
        $colHeaders = [
            0 => '#',
            1 => __('companies.company_name'),
            2 => __('companies.company_type'),
            3 => __('general.city'),
            4 => __('general.phone'),
            5 => __('general.email'),
            6 => __('companies.license_type'),
            7 => __('companies.license_specialty'),
            8 => __('companies.manager_name'),
            9 => __('companies.representative'),
            10 => __('general.status'),
            11 => __('general.registration_date'),
            12 => __('companies.expiry_date'),
        ];
    @endphp

    <table>
        <thead>
            <tr>
                @foreach($colHeaders as $i => $label)
                    @if(in_array($i, $visibleCols))<th>{{ $label }}</th>@endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
            @php
                $rowData = [
                    0 => $loop->iteration,
                    1 => $company->company_name,
                    2 => $company->company_type_name,
                    3 => $company->city ?? '-',
                    4 => $company->phone ?? '-',
                    5 => $company->email ?? '-',
                    6 => $company->license_type_name,
                    7 => $company->license_specialty_name,
                    8 => $company->manager_name ?? '-',
                    9 => $company->representative?->full_name ?? '-',
                    10 => $company->status_name,
                    11 => $company->created_at->format('Y-m-d'),
                    12 => $company->expires_at ? $company->expires_at->format('Y-m-d') : '-',
                ];
            @endphp
            <tr>
                @foreach($rowData as $i => $val)
                    @if(in_array($i, $visibleCols))<td class="{{ $i === 0 ? 'row-num' : '' }}">{{ $val }}</td>@endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div>&copy; {{ date('Y') }} {{ __('general.copyright_ministry') }}</div>
        <div class="page-info">{{ __('general.auto_generated_report_v2') }}</div>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
