@extends('layouts.auth')

@section('title', 'الشركات الأجنبية')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.dashboard') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>الشركات الأجنبية</h1>
                <p>قائمة بجميع الشركات الأجنبية المسجلة</p>
            </div>
        </div>
        <a href="{{ route('representative.foreign-companies.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i>
            تسجيل شركة أجنبية جديدة
        </a>
    </div>

    

    @if($companies->count() > 0)
        <!-- Desktop Table View -->
        <div class="companies-table desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الشركة</th>
                        <th>الدولة</th>
                        <th>نوع الكيان</th>
                        <th>الشركة المحلية (الوكيل)</th>
                        <th>نوع النشاط</th>
                        <th>عدد المنتجات</th>
                        <th>تاريخ التسجيل</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $index => $company)
                    <tr class="clickable-row" onclick="window.location='{{ route('representative.foreign-companies.show', $company) }}'">
                        <td>{{ $companies->firstItem() + $index }}</td>
                        <td><strong>{{ $company->company_name }}</strong></td>
                        <td>{{ $company->country }}</td>
                        <td>{{ $company->entity_type_name }}</td>
                        <td>{{ $company->localCompany->company_name }}</td>
                        <td>{{ $company->activity_type_name }}</td>
                        <td>{{ $company->products_count }}</td>
                        <td>{{ $company->created_at->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge {{ $company->status_badge_class }}">
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
            <div class="company-card clickable-card" onclick="window.location='{{ route('representative.foreign-companies.show', $company) }}'">
                <div class="card-header">
                    <div class="company-name">
                        <i class="ti ti-world"></i>
                        <h3>{{ $company->company_name }}</h3>
                    </div>
                    <span class="badge {{ $company->status_badge_class }}">
                        {{ $company->status_name }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">الدولة:</span>
                        <span class="value">{{ $company->country }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">نوع الكيان:</span>
                        <span class="value">{{ $company->entity_type_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">الوكيل المحلي:</span>
                        <span class="value">{{ $company->localCompany->company_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">نوع النشاط:</span>
                        <span class="value">{{ $company->activity_type_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">عدد المنتجات:</span>
                        <span class="value">{{ $company->products_count }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">تاريخ التسجيل:</span>
                        <span class="value">{{ $company->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($companies->hasPages())
        <div class="pagination-wrapper">
            {{ $companies->links() }}
        </div>
        @endif
    @else
        <div class="empty-state">
            <h2>لا توجد شركات أجنبية مسجلة</h2>
            <p>لم تقم بتسجيل أي شركة أجنبية بعد. يمكنك البدء بتسجيل شركتك الأولى</p>
            <a href="{{ route('representative.foreign-companies.create') }}" class="primary-btn">
                <i class="ti ti-plus"></i>
                تسجيل شركة أجنبية جديدة
            </a>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1400px;
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
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 8px;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
    }

    .back-to-home:hover {
        background: #e5e7eb;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #1a5f4a;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .companies-table {
        margin-bottom: 30px;
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
        white-space: nowrap;
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
        white-space: nowrap;
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
        color: #4b5563;
    }

    .table-action {
        color: #1a5f4a;
        text-decoration: none;
        font-weight: 500;
        white-space: nowrap;
    }

    .table-action:hover {
        text-decoration: underline;
    }

    /* Mobile Cards */
    .companies-cards {
        display: none;
    }

    .company-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .company-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .company-card .company-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .company-card .company-name i {
        font-size: 1.5rem;
        color: #1a5f4a;
    }

    .company-card .company-name h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .company-card .card-body {
        padding: 15px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-row .value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 600;
    }

    .company-card .card-footer {
        padding: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .card-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        background: #1a5f4a;
        color: #ffffff;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .clickable-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .clickable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
        border-color: #1a5f4a;
    }

    /* Empty State */
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

    /* Pagination */
    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .desktop-only {
            display: none;
        }

        .companies-cards {
            display: block;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
        }

        .page-header-content {
            width: 100%;
        }

        .btn-primary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush
