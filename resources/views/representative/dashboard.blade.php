@extends('layouts.auth')

@section('title', 'لوحة التحكم')

@section('content')
<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>لوحة التحكم</h1>
            <p>{{ $representative->name }} - {{ $representative->job_title }}</p>
        </div>
        <form action="{{ route('representative.logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="ti ti-logout"></i>
                تسجيل الخروج
            </button>
        </form>
    </div>

    

    @if($announcements->count() > 0)
        <div class="announcements-section">
            <div class="section-header" style="margin-top: 0;">
                <h2><i class="ti ti-speakerphone" style="margin-left: 8px; color: #1a5f4a;"></i>التعميمات</h2>
            </div>
            @foreach($announcements as $announcement)
                <div class="announcement-card announcement-{{ $announcement->priority }}">
                    <div class="announcement-header">
                        <div class="announcement-title-row">
                            @if($announcement->priority === 'urgent')
                                <span class="announcement-badge urgent">عاجل</span>
                            @elseif($announcement->priority === 'important')
                                <span class="announcement-badge important">مهم</span>
                            @endif
                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                        </div>
                        <span class="announcement-date">{{ $announcement->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="announcement-body">{{ Str::limit($announcement->body, 200) }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @if($expiringItems->count() > 0)
        <div class="expiry-alert">
            <div class="alert-icon expiry-icon">
                <i class="ti ti-clock-exclamation"></i>
            </div>
            <div class="alert-content">
                <h3>تنبيه - قرب انتهاء الصلاحية</h3>
                <p>الشركات التالية ستنتهي صلاحيتها خلال الثلاثة أشهر القادمة. يرجى رفع إيصال تحويل رسوم التجديد قبل انتهاء الصلاحية.</p>
                <div class="expiry-items-list">
                    @foreach($expiringItems as $item)
                        <div class="expiry-item">
                            <div class="expiry-info">
                                @if($item['type'] === 'local_company')
                                    <span class="expiry-type-badge local">شركة محلية</span>
                                @else
                                    <span class="expiry-type-badge foreign">شركة أجنبية</span>
                                @endif
                                <span class="expiry-name">{{ $item['name'] }}</span>
                                <span class="expiry-date">ينتهي في: {{ $item['expires_at'] }}</span>
                            </div>
                            <div class="expiry-actions">
                                <span class="expiry-days">{{ $item['days_remaining'] }} يوم متبقي</span>
                                @if($item['invoice_route'])
                                    <a href="{{ $item['invoice_route'] }}" class="expiry-pay-link">
                                        <i class="ti ti-upload"></i>
                                        رفع الإيصال
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($pendingInvoices->count() > 0)
        <div class="pending-invoices-alert">
            <div class="alert-icon">
                <i class="ti ti-alert-circle"></i>
            </div>
            <div class="alert-content">
                <h3>تنبيه هام - فواتير تحتاج إلى رفع إيصال دفع</h3>
                <p>لقد تم قبول شركتك بشكل مبدئي. نأمل منكم رفع إيصال تحويل القيمة لحساب وزارة الصحة لإتمام عملية التفعيل.</p>
                <div class="pending-invoices-list">
                    @foreach($pendingInvoices as $invoice)
                        <div class="invoice-item">
                            <div class="invoice-info">
                                <span class="invoice-number">{{ $invoice['invoice_number'] }}</span>
                                <span class="company-name">{{ $invoice['company_name'] }}</span>
                                <span class="invoice-amount">{{ number_format($invoice['amount'], 2) }} دينار ليبي</span>
                            </div>
                            <a href="{{ $invoice['route'] }}" class="invoice-link">
                                <i class="ti ti-arrow-left"></i>
                                رفع الإيصال
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($representative->companies->count() == 0)
        <!-- No Companies Message -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ti ti-building-community"></i>
            </div>
            <h2>لا توجد شركات مسجلة</h2>
            <p>لم تقم بتسجيل أي شركة بعد. يمكنك البدء بتسجيل شركتك الأولى</p>
            <a href="{{ route('representative.companies.create') }}" class="primary-btn">
                <i class="ti ti-plus"></i>
                تسجيل شركة جديدة
            </a>
        </div>
    @else
        <!-- Quick Actions -->
        <div class="section-header">
            <h2>الإجراءات المتاحة</h2>
        </div>

        <div class="actions-grid">
            <a href="{{ route('representative.companies.index') }}" class="action-btn">
                <i class="ti ti-building"></i>
                <span>إدارة الشركات المحلية</span>
            </a>
            @if($hasActiveSupplierCompany)
                <a href="{{ route('representative.foreign-companies.index') }}" class="action-btn">
                    <i class="ti ti-world"></i>
                    <span>إدارة الشركات الأجنبية</span>
                </a>
            @endif
            @php
                $activeForeignCompaniesCount = \App\Models\ForeignCompany::where('representative_id', $representative->id)->where('status', 'active')->count();
            @endphp
            @if($activeForeignCompaniesCount > 0)
                <a href="{{ route('representative.pharmaceutical-products.index') }}" class="action-btn">
                    <i class="ti ti-pill"></i>
                    <span>الأصناف الدوائية</span>
                </a>
            @endif
            <a href="{{ route('representative.invoices.index') }}" class="action-btn">
                <i class="ti ti-file-invoice"></i>
                <span>الفواتير والمدفوعات</span>
            </a>
        </div>

        <!-- Recent Companies -->
    
    @endif

    <!-- Footer -->
    <div class="dashboard-footer">
        <p>© {{ date('Y') }} وزارة الصحة - إدارة الصيدلة </p>
    </div>
</div>
@endsection

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

    /* Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 30px;
    }

    .header-content h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .header-content p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Almarai', sans-serif;
    }

    .logout-btn:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    /* Pending Invoices Alert */
    .pending-invoices-alert {
        display: flex;
        gap: 20px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid #fbbf24;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
    }

    .alert-icon {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: #f59e0b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .alert-icon i {
        font-size: 1.75rem;
        color: #ffffff;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 10px 0;
    }

    .alert-content p {
        font-size: 0.95rem;
        color: #78350f;
        margin: 0 0 20px 0;
        line-height: 1.6;
    }

    .pending-invoices-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .invoice-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border: 1px solid #fbbf24;
        border-radius: 6px;
        padding: 15px 20px;
        transition: all 0.2s ease;
    }

    .invoice-item:hover {
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(251, 191, 36, 0.15);
    }

    .invoice-info {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .invoice-number {
        font-weight: 700;
        color: #1a5f4a;
        font-size: 0.9rem;
    }

    .company-name {
        color: #374151;
        font-size: 0.875rem;
    }

    .invoice-amount {
        color: #92400e;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .invoice-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #1a5f4a;
        color: #ffffff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .invoice-link:hover {
        background: #164538;
        transform: translateX(-2px);
    }

    .invoice-link i {
        font-size: 1rem;
    }

    /* Expiry Alert */
    .expiry-alert {
        display: flex;
        gap: 20px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 2px solid #f87171;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    .expiry-icon {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: #ef4444;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .expiry-icon i {
        font-size: 1.75rem;
        color: #ffffff;
    }

    .expiry-alert .alert-content h3 {
        color: #991b1b;
    }

    .expiry-alert .alert-content p {
        color: #7f1d1d;
    }

    .expiry-items-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .expiry-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border: 1px solid #f87171;
        border-radius: 6px;
        padding: 12px 18px;
    }

    .expiry-info {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .expiry-type-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .expiry-type-badge.local {
        background: #d1fae5;
        color: #065f46;
    }

    .expiry-type-badge.foreign {
        background: #dbeafe;
        color: #1e40af;
    }

    .expiry-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .expiry-date {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .expiry-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .expiry-days {
        background: #fef2f2;
        color: #991b1b;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .expiry-pay-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #1a5f4a;
        color: #ffffff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .expiry-pay-link:hover {
        background: #164538;
    }

    .expiry-pay-link i {
        font-size: 0.9rem;
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .stat-title {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        font-size: 1.25rem;
        color: white;
    }

    .stat-icon.blue {
        background: #3b82f6;
    }

    .stat-icon.orange {
        background: #f59e0b;
    }

    .stat-icon.green {
        background: #10b981;
    }

    .stat-icon.red {
        background: #ef4444;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    /* Section Header */
    .section-header {
        margin: 30px 0 15px 0;
    }

    .section-header h2 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        text-decoration: none;
        color: #374151;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        background: #f9fafb;
        border-color: #1a5f4a;
        color: #1a5f4a;
    }

    .action-btn i {
        font-size: 1.25rem;
        color: #1a5f4a;
    }

    /* Companies Table */
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

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
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

    .table-action {
        color: #1a5f4a;
        text-decoration: none;
        font-weight: 500;
    }

    .table-action:hover {
        text-decoration: underline;
    }

    /* Footer */
    .dashboard-footer {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
        margin-top: 30px;
    }

    .dashboard-footer p {
        margin: 0;
        font-size: 0.8rem;
        color: #9ca3af;
    }

    /* Announcements */
    .announcements-section {
        margin-bottom: 30px;
    }

    .announcement-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 18px 20px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        border-right: 4px solid #3b82f6;
    }

    .announcement-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .announcement-card.announcement-important {
        border-right-color: #f59e0b;
        background: #fffbeb;
    }

    .announcement-card.announcement-urgent {
        border-right-color: #ef4444;
        background: #fef2f2;
    }

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .announcement-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .announcement-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .announcement-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .announcement-badge.urgent {
        background: #fee2e2;
        color: #991b1b;
    }

    .announcement-badge.important {
        background: #fef3c7;
        color: #92400e;
    }

    .announcement-date {
        font-size: 0.75rem;
        color: #9ca3af;
        flex-shrink: 0;
    }

    .announcement-body {
        font-size: 0.875rem;
        color: #4b5563;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            gap: 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .companies-table {
            font-size: 0.8rem;
        }

        .expiry-alert {
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }

        .expiry-item {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .expiry-info {
            flex-direction: column;
            gap: 6px;
            align-items: flex-start;
        }

        .pending-invoices-alert {
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }

        .alert-icon {
            width: 45px;
            height: 45px;
        }

        .alert-icon i {
            font-size: 1.5rem;
        }

        .alert-content h3 {
            font-size: 1rem;
        }

        .alert-content p {
            font-size: 0.875rem;
        }

        .invoice-item {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
            padding: 15px;
        }

        .invoice-info {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .invoice-link {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: '{{ session('success') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#10b981',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: '{{ session('error') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif
</script>
@endpush
