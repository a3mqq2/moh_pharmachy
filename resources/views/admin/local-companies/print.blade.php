<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('companies.companies_record') }}</title>
    <style>
        @font-face {
            font-family: 'Almarai';
            src: url('https://fonts.gstatic.com/s/almarai/v12/tssoApxBaigK_hnnS_anhnicoq72sXg.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Almarai';
            src: url('https://fonts.gstatic.com/s/almarai/v12/tssoApxBaigK_hnnQ-aghnicoq72sXg.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            direction: rtl;
            background: #fff;
            color: #333;
        }

        .container {
            max-width: 100%;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a5f4a;
            padding-bottom: 15px;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 15px;
        }

        .header-text {
            text-align: center;
        }

        .header-text h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .header-text h3 {
            font-size: 18px;
            color: #1a5f4a;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .header-text h1 {
            font-size: 24px;
            color: #1a5f4a;
            font-weight: bold;
            margin-top: 10px;
        }

        .filters-info {
            background: #f8f9fa;
            padding: 8px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filters-info span {
            margin-left: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: right;
            vertical-align: middle;
        }

        th {
            background-color: #1a5f4a;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1a5f4a;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-btn:hover {
            background: #155a45;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 130px;
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            z-index: 1000;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-primary { background: #cce5ff; color: #004085; }

        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }

        .total-row {
            background: #e9ecef !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        {{ __('general.print') }}
    </button>
    <a href="{{ route('admin.local-companies.index', request()->query()) }}" class="back-btn no-print">
        {{ __('general.back') }}
    </a>

    <div class="container">
        <div class="header">
            <div class="header-content">
                <img src="{{ asset('logo-v.png') }}" alt="{{ __('general.ministry_logo') }}" class="logo">
                <div class="header-text">
                    <h4>{{ __('general.state_of_libya') }}</h4>
                    <h3>{{ __('general.ministry_pharmacy_department') }}</h3>
                    <h1>{{ __('companies.companies_record') }}</h1>
                </div>
            </div>
        </div>

        <div class="filters-info">
            <div>
                @if(request('company_type'))
                    <span>{{ __('general.classification') }}: {{ \App\Models\LocalCompany::companyTypes()[request('company_type')] ?? request('company_type') }}</span>
                @endif
                @if(request('status'))
                    <span>{{ __('general.status') }}: {{ \App\Models\LocalCompany::statuses()[request('status')] ?? request('status') }}</span>
                @endif
                @if(request('license_type'))
                    <span>{{ __('companies.license_type') }}: {{ \App\Models\LocalCompany::licenseTypes()[request('license_type')] ?? request('license_type') }}</span>
                @endif
                @if(request('license_specialty'))
                    <span>{{ __('general.specialty') }}: {{ \App\Models\LocalCompany::licenseSpecialties()[request('license_specialty')] ?? request('license_specialty') }}</span>
                @endif
                @if(request('city'))
                    <span>{{ __('general.city') }}: {{ request('city') }}</span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span>{{ __('general.period') }}: {{ request('date_from', '-') }} {{ __('general.to') }} {{ request('date_to', '-') }}</span>
                @endif
                @if(!request()->hasAny(['company_type', 'status', 'license_type', 'license_specialty', 'city', 'date_from', 'date_to']))
                    <span>{{ __('general.all_companies') }}</span>
                @endif
            </div>
            <div>
                <strong>{{ __('general.total_companies') }}: {{ $companies->count() }}</strong>
                &nbsp;|&nbsp;
                <span>{{ __('general.print_date') }}: {{ now()->format('Y-m-d h:i A') }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="4%">{{ __('general.serial_no') }}</th>
                    <th width="6%">{{ __('general.registration_number') }}</th>
                    <th width="18%">{{ __('companies.company_name') }}</th>
                    <th width="8%">{{ __('general.classification') }}</th>
                    <th width="14%">{{ __('companies.responsible_manager') }}</th>
                    <th width="10%">{{ __('general.phone') }}</th>
                    <th width="8%">{{ __('general.city') }}</th>
                    <th width="10%">{{ __('companies.license_type') }}</th>
                    <th width="10%">{{ __('general.specialty') }}</th>
                    <th width="6%">{{ __('general.status') }}</th>
                    <th width="6%">{{ __('general.registration_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $index => $company)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $company->registration_number ?? '-' }}</td>
                    <td>{{ $company->company_name }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $company->company_type == 'distributor' ? 'info' : 'primary' }}">
                            {{ $company->company_type_name }}
                        </span>
                    </td>
                    <td>{{ $company->manager_name }}</td>
                    <td dir="ltr" class="text-center">{{ $company->manager_phone }}</td>
                    <td>{{ $company->city }}</td>
                    <td>{{ $company->license_type_name }}</td>
                    <td>{{ $company->license_specialty_name }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = match($company->status) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                'suspended' => 'secondary',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $statusClass }}">{{ $company->status_name }}</span>
                    </td>
                    <td class="text-center">{{ $company->registration_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 30px;">
                        {{ __('general.no_matching_filters') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($companies->count() > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-center">{{ __('general.total_label') }}</td>
                    <td class="text-center">
                        {{ __('companies.distributor') }}: {{ $companies->where('company_type', 'distributor')->count() }}
                        |
                        {{ __('companies.supplier') }}: {{ $companies->where('company_type', 'supplier')->count() }}
                    </td>
                    <td colspan="5"></td>
                    <td class="text-center">
                        {{ __('general.active_label') }}: {{ $companies->where('status', 'approved')->count() }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="footer">
            <p>{{ __('general.auto_generated_report') }}</p>
            <p>{{ now()->format('Y-m-d h:i:s A') }}</p>
        </div>
    </div>

    <script>
        window.onafterprint = function() {
        };
    </script>
</body>
</html>
