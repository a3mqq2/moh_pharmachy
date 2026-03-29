@extends('layouts.auth')

@section('title', __('companies.my_companies'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.dashboard') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ __('companies.my_companies') }}</h1>
                <p>{{ __('companies.my_companies_desc') }}</p>
            </div>
        </div>
        <a href="{{ route('representative.companies.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i>
            {{ __('companies.register_new') }}
        </a>
    </div>

    

    @if($companies->count() > 0)
        <!-- Desktop Table View -->
        <div class="companies-table desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('companies.company_name') }}</th>
                        <th>{{ __('companies.company_type') }}</th>
                        <th>{{ __('general.city') }}</th>
                        <th>{{ __('general.registration_date') }}</th>
                        <th>{{ __('general.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $index => $company)
                    <tr class="clickable-row" onclick="window.location='{{ route('representative.companies.show', $company) }}'">
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $company->company_name }}</strong></td>
                        <td>{{ $company->company_type_name }}</td>
                        <td>{{ $company->city }}</td>
                        <td>{{ $company->created_at->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge badge-{{ $company->status_color }}">
                                {{ $company->status_name }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="companies-cards mobile-only">
            @foreach($companies as $company)
            <div class="company-card clickable-card" onclick="window.location='{{ route('representative.companies.show', $company) }}'">
                <div class="card-header">
                    <div class="company-name">
                        <i class="ti ti-building"></i>
                        <h3>{{ $company->company_name }}</h3>
                    </div>
                    <span class="badge badge-{{ $company->status_color }}">
                        {{ $company->status_name }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="card-info-row">
                        <span class="info-label">{{ __('companies.company_type') }}:</span>
                        <span class="info-value">{{ $company->company_type_name }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="info-label">{{ __('general.city') }}:</span>
                        <span class="info-value">{{ $company->city }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="info-label">{{ __('general.registration_date') }}:</span>
                        <span class="info-value">{{ $company->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ti ti-building-community"></i>
            </div>
            <h2>{{ __('companies.no_companies') }}</h2>
            <p>{{ __('companies.no_companies_registered') }}</p>
            <a href="{{ route('representative.companies.create') }}" class="primary-btn">
                <i class="ti ti-plus"></i>
                {{ __('companies.register_new') }}
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '{{ __("general.success") }}',
            text: '{{ session('success') }}',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#10b981',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '{{ __("general.error") }}',
            text: '{{ session('error') }}',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif
</script>
@endpush

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1200px;
        padding: 0 20px;
    }

    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-to-home {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #1a5f4a;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 1.2rem;
    }

    .back-to-home:hover {
        background: #1a5f4a;
        color: white;
        border-color: #1a5f4a;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: white;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .companies-table {
        overflow-x: auto;
    }

    .companies-table table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .companies-table thead {
        background: #f9fafb;
    }

    .companies-table th {
        padding: 12px 16px;
        text-align: right;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .companies-table td {
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
    }

    .companies-table tbody tr:last-child td {
        border-bottom: none;
    }

    .companies-table tbody tr:hover {
        background: #f9fafb;
    }

    .clickable-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-row:hover {
        background: #f0fdf4 !important;
        transform: translateX(-2px);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .badge-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .table-action {
        color: #1a5f4a;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .table-action:hover {
        text-decoration: underline;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        margin: 30px 0;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .empty-icon i {
        font-size: 2.5rem;
        color: #6b7280;
    }

    .empty-state h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .empty-state p {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0 0 25px 0;
    }

    .primary-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #1a5f4a;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .primary-btn:hover {
        background: #164538;
    }

    /* Mobile Cards */
    .companies-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .company-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .company-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: #1a5f4a;
    }

    .clickable-card {
        cursor: pointer;
    }

    .clickable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .company-name {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .company-name i {
        font-size: 1.5rem;
        color: #1a5f4a;
    }

    .company-name h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .card-body {
        padding: 16px;
    }

    .card-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .card-info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 600;
    }

    .card-footer {
        padding: 16px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .card-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px;
        background: #1a5f4a;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .card-action-btn:hover {
        background: #164538;
    }

    /* Responsive Display Control */
    .desktop-only {
        display: block;
    }

    .mobile-only {
        display: none;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .desktop-only {
            display: none;
        }

        .mobile-only {
            display: block;
        }

        .dashboard-container {
            padding: 20px;
        }

        .companies-cards {
            gap: 15px;
        }
    }
</style>
@endpush
