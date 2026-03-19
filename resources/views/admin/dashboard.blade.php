@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')

@php
    $totalPendingActions = $pendingApprovalProducts->count() + $pendingReceiptInvoices->count() + $pendingLocalCompanies->count() + $pendingForeignCompanies->count();
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xxl-3">
        <a href="{{ route('admin.local-companies.index') }}" class="text-decoration-none">
            <div class="card widget-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-primary-subtle text-primary">
                            <i class="ti ti-building-skyscraper"></i>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $stats['local_companies']['total'] }}</h2>
                            <span class="text-muted">الشركات المحلية</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-2">
                        <span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>{{ $stats['local_companies']['active'] }} نشطة</span>
                        <span class="badge bg-warning-subtle text-warning"><i class="ti ti-clock me-1"></i>{{ $stats['local_companies']['pending'] }} معلقة</span>
                    </div>
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="ti ti-calendar-event me-1"></i>اليوم: {{ $stats['local_companies']['today'] }}</span>
                            <span><i class="ti ti-calendar-stats me-1"></i>الأسبوع: {{ $stats['local_companies']['week'] }}</span>
                            <span><i class="ti ti-calendar me-1"></i>الشهر: {{ $stats['local_companies']['month'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xxl-3">
        <a href="{{ route('admin.foreign-companies.index') }}" class="text-decoration-none">
            <div class="card widget-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-info-subtle text-info">
                            <i class="ti ti-world"></i>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $stats['foreign_companies']['total'] }}</h2>
                            <span class="text-muted">الشركات الأجنبية</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-2">
                        <span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>{{ $stats['foreign_companies']['active'] }} نشطة</span>
                        <span class="badge bg-warning-subtle text-warning"><i class="ti ti-clock me-1"></i>{{ $stats['foreign_companies']['pending'] }} معلقة</span>
                    </div>
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="ti ti-calendar-event me-1"></i>اليوم: {{ $stats['foreign_companies']['today'] }}</span>
                            <span><i class="ti ti-calendar-stats me-1"></i>الأسبوع: {{ $stats['foreign_companies']['week'] }}</span>
                            <span><i class="ti ti-calendar me-1"></i>الشهر: {{ $stats['foreign_companies']['month'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xxl-3">
        <a href="{{ route('admin.pharmaceutical-products.index') }}" class="text-decoration-none">
            <div class="card widget-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-success-subtle text-success">
                            <i class="ti ti-pill"></i>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $stats['pharmaceutical_products']['total'] }}</h2>
                            <span class="text-muted">الأصناف الدوائية</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-2">
                        <span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>{{ $stats['pharmaceutical_products']['active'] }} معتمدة</span>
                        <span class="badge bg-warning-subtle text-warning"><i class="ti ti-clock me-1"></i>{{ $stats['pharmaceutical_products']['pending_review'] + $stats['pharmaceutical_products']['pending_final_approval'] }} معلقة</span>
                    </div>
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="ti ti-calendar-event me-1"></i>اليوم: {{ $stats['pharmaceutical_products']['today'] }}</span>
                            <span><i class="ti ti-calendar-stats me-1"></i>الأسبوع: {{ $stats['pharmaceutical_products']['week'] }}</span>
                            <span><i class="ti ti-calendar me-1"></i>الشهر: {{ $stats['pharmaceutical_products']['month'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xxl-3">
        <a href="{{ route('admin.company-representatives.index') }}" class="text-decoration-none">
            <div class="card widget-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-purple-subtle text-purple">
                            <i class="ti ti-id"></i>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold">{{ $stats['representatives']['total'] }}</h2>
                            <span class="text-muted">ممثلي الشركات</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-2">
                        <span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>{{ $stats['representatives']['active'] }} نشط</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

@if($totalPendingActions > 0)
<div class="row g-3 mb-4">
    @if($pendingLocalCompanies->count() > 0)
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-header bg-warning bg-opacity-10 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-building-skyscraper text-warning fs-4"></i>
                    <h6 class="mb-0 fw-bold">شركات محلية تحتاج مراجعة</h6>
                </div>
                <span class="badge bg-warning">{{ $pendingLocalCompanies->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الشركة</th>
                                <th>الممثل</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingLocalCompanies as $company)
                            <tr>
                                <td><strong>{{ $company->company_name }}</strong></td>
                                <td><small class="text-muted">{{ $company->representative?->name ?? '-' }}</small></td>
                                <td><small>{{ $company->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.local-companies.show', $company) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($pendingForeignCompanies->count() > 0)
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-header bg-info bg-opacity-10 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-world text-info fs-4"></i>
                    <h6 class="mb-0 fw-bold">شركات أجنبية تحتاج مراجعة</h6>
                </div>
                <span class="badge bg-info">{{ $pendingForeignCompanies->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الشركة</th>
                                <th>الدولة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingForeignCompanies as $company)
                            <tr>
                                <td><strong>{{ $company->company_name }}</strong></td>
                                <td><small class="text-muted">{{ $company->country }}</small></td>
                                <td><small>{{ $company->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.foreign-companies.show', $company) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($pendingApprovalProducts->count() > 0)
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-pill text-primary fs-4"></i>
                    <h6 class="mb-0 fw-bold text-white">أصناف دوائية تحتاج موافقة</h6>
                </div>
                <span class="badge bg-primary">{{ $pendingApprovalProducts->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الصنف</th>
                                <th>الشكل الصيدلاني</th>
                                <th>الشركة الأجنبية</th>
                                <th>الممثل</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovalProducts as $product)
                            <tr>
                                <td><strong>{{ $product->product_name }}</strong></td>
                                <td><small class="text-muted">{{ $product->pharmaceutical_form }}</small></td>
                                <td>{{ $product->foreignCompany->company_name }}</td>
                                <td>{{ $product->representative->name }}</td>
                                <td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td>
                                <td><small>{{ $product->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.pharmaceutical-products.show', $product) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($pendingReceiptInvoices->count() > 0)
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-danger bg-opacity-10 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-file-invoice text-danger fs-4"></i>
                    <h6 class="mb-0 fw-bold">فواتير تحتاج رفع إيصال</h6>
                </div>
                <span class="badge bg-danger">{{ $pendingReceiptInvoices->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>الصنف الدوائي</th>
                                <th>الممثل</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReceiptInvoices as $invoice)
                            <tr>
                                <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                                <td><strong>{{ $invoice->pharmaceuticalProduct->product_name }}</strong></td>
                                <td><small class="text-muted">{{ $invoice->pharmaceuticalProduct->representative->name }}</small></td>
                                <td><strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                                <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.pharmaceutical-products.show', $invoice->pharmaceuticalProduct) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<div class="mb-4">
    <h6 class="fw-bold text-muted mb-3"><i class="ti ti-building-skyscraper me-1"></i>الشركات المحلية حسب الحالة</h6>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.local-companies.index', ['status' => 'active']) }}" class="text-decoration-none">
                <div class="filled-widget filled-success">
                    <div class="filled-widget-icon"><i class="ti ti-circle-check"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['local_companies']['active'] }}</h3>
                        <span class="filled-widget-label">نشطة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.local-companies.index', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="filled-widget filled-warning">
                    <div class="filled-widget-icon"><i class="ti ti-clock"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['local_companies']['pending'] }}</h3>
                        <span class="filled-widget-label">قيد المراجعة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.local-companies.index', ['status' => 'approved']) }}" class="text-decoration-none">
                <div class="filled-widget filled-primary">
                    <div class="filled-widget-icon"><i class="ti ti-thumb-up"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['local_companies']['approved'] }}</h3>
                        <span class="filled-widget-label">موافق عليها</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.local-companies.index', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="filled-widget filled-danger">
                    <div class="filled-widget-icon"><i class="ti ti-x"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['local_companies']['rejected'] }}</h3>
                        <span class="filled-widget-label">مرفوضة</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="mb-4">
    <h6 class="fw-bold text-muted mb-3"><i class="ti ti-world me-1"></i>الشركات الأجنبية حسب الحالة</h6>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.foreign-companies.index', ['status' => 'active']) }}" class="text-decoration-none">
                <div class="filled-widget filled-success">
                    <div class="filled-widget-icon"><i class="ti ti-circle-check"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['foreign_companies']['active'] }}</h3>
                        <span class="filled-widget-label">نشطة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.foreign-companies.index', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="filled-widget filled-warning">
                    <div class="filled-widget-icon"><i class="ti ti-clock"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['foreign_companies']['pending'] }}</h3>
                        <span class="filled-widget-label">قيد المراجعة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.foreign-companies.index', ['status' => 'uploading_documents']) }}" class="text-decoration-none">
                <div class="filled-widget filled-info">
                    <div class="filled-widget-icon"><i class="ti ti-upload"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['foreign_companies']['uploading_documents'] }}</h3>
                        <span class="filled-widget-label">رفع المستندات</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.foreign-companies.index', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="filled-widget filled-danger">
                    <div class="filled-widget-icon"><i class="ti ti-x"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['foreign_companies']['rejected'] }}</h3>
                        <span class="filled-widget-label">مرفوضة</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="mb-4">
    <h6 class="fw-bold text-muted mb-3"><i class="ti ti-pill me-1"></i>الأصناف الدوائية حسب الحالة</h6>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'active']) }}" class="text-decoration-none">
                <div class="filled-widget filled-success">
                    <div class="filled-widget-icon"><i class="ti ti-circle-check"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['pharmaceutical_products']['active'] }}</h3>
                        <span class="filled-widget-label">معتمدة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_review']) }}" class="text-decoration-none">
                <div class="filled-widget filled-warning">
                    <div class="filled-widget-icon"><i class="ti ti-clock"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['pharmaceutical_products']['pending_review'] }}</h3>
                        <span class="filled-widget-label">قيد المراجعة</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_payment']) }}" class="text-decoration-none">
                <div class="filled-widget filled-info">
                    <div class="filled-widget-icon"><i class="ti ti-credit-card"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['pharmaceutical_products']['pending_payment'] }}</h3>
                        <span class="filled-widget-label">قيد السداد</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="filled-widget filled-danger">
                    <div class="filled-widget-icon"><i class="ti ti-x"></i></div>
                    <div>
                        <h3 class="filled-widget-count">{{ $stats['pharmaceutical_products']['rejected'] }}</h3>
                        <span class="filled-widget-label">مرفوضة</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <a href="{{ route('admin.local-companies.create') }}" class="text-decoration-none">
            <div class="card quick-action-card border-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="widget-icon-sm bg-primary-subtle text-primary"><i class="ti ti-building-skyscraper"></i></div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">إضافة شركة محلية</h6>
                        <small class="text-muted">تسجيل شركة محلية جديدة</small>
                    </div>
                    <i class="ti ti-chevron-left ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4">
        <a href="{{ route('admin.foreign-companies.create') }}" class="text-decoration-none">
            <div class="card quick-action-card border-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="widget-icon-sm bg-info-subtle text-info"><i class="ti ti-world"></i></div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">إضافة شركة أجنبية</h6>
                        <small class="text-muted">تسجيل شركة أجنبية جديدة</small>
                    </div>
                    <i class="ti ti-chevron-left ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4">
        <a href="{{ route('admin.announcements.create') }}" class="text-decoration-none">
            <div class="card quick-action-card border-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="widget-icon-sm bg-success-subtle text-success"><i class="ti ti-speakerphone"></i></div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">إرسال تعميم</h6>
                        <small class="text-muted">إنشاء تعميم جديد</small>
                    </div>
                    <i class="ti ti-chevron-left ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
</div>

@endsection

@push('styles')
<style>
.widget-card {
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: transform .2s ease, box-shadow .2s ease;
    cursor: pointer;
}
.widget-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,.1);
}

.widget-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.widget-icon-sm {
    width: 44px;
    height: 44px;
    min-width: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.bg-primary-subtle { background: rgba(59, 130, 246, 0.1) !important; }
.text-primary { color: #3b82f6 !important; }
.bg-success-subtle { background: rgba(16, 185, 129, 0.1) !important; }
.bg-warning-subtle { background: rgba(245, 158, 11, 0.1) !important; }
.bg-danger-subtle { background: rgba(239, 68, 68, 0.1) !important; }
.bg-info-subtle { background: rgba(6, 182, 212, 0.1) !important; }
.bg-secondary-subtle { background: rgba(107, 114, 128, 0.1) !important; }
.bg-purple-subtle { background: rgba(139, 92, 246, 0.1) !important; }
.text-purple { color: #8b5cf6 !important; }
.bg-purple { background: #8b5cf6 !important; }

.filled-widget {
    border-radius: 12px;
    padding: 1rem 1.25rem;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    transition: transform .2s ease, box-shadow .2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.filled-widget:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.1);
}
.filled-widget-icon {
    font-size: 1.3rem;
    width: 44px;
    height: 44px;
    min-width: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.filled-widget-count {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    color: #1f2937;
}
.filled-widget-label {
    font-size: 0.78rem;
    font-weight: 500;
    color: #6b7280;
}

.filled-success .filled-widget-icon { background: rgba(16, 185, 129, 0.12); color: #059669; }
.filled-warning .filled-widget-icon { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.filled-primary .filled-widget-icon { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.filled-danger .filled-widget-icon { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
.filled-info .filled-widget-icon { background: rgba(6, 182, 212, 0.12); color: #0891b2; }

.quick-action-card {
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    transition: transform .2s ease, box-shadow .2s ease;
}
.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,.08);
}
</style>
@endpush
