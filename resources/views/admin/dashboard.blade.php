@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row">
    <div class="col-md-6 col-xxl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-primary">
                            <i class="ti ti-building f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">الشركات المحلية</h6>
                    </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                    <div class="mt-3 row align-items-center">
                        <div class="col-12">
                            <h3 class="mb-1">{{ $stats['local_companies_total'] }}</h3>
                            <div class="d-flex justify-content-between mt-3">
                                <small class="text-success"><i class="ti ti-circle-check"></i> نشطة: {{ $stats['local_companies_active'] }}</small>
                                <small class="text-warning"><i class="ti ti-clock"></i> قيد المراجعة: {{ $stats['local_companies_pending'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xxl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-success">
                            <i class="ti ti-pill f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">الأصناف الدوائية</h6>
                    </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                    <div class="mt-3 row align-items-center">
                        <div class="col-12">
                            <h3 class="mb-1">{{ $stats['pharmaceutical_products_total'] }}</h3>
                            <div class="d-flex justify-content-between mt-3">
                                <small class="text-success"><i class="ti ti-check"></i> معتمدة: {{ $stats['pharmaceutical_products_active'] }}</small>
                                <small class="text-warning"><i class="ti ti-clock"></i> قيد المراجعة: {{ $stats['pharmaceutical_products_pending_review'] + $stats['pharmaceutical_products_pending_final_approval'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xxl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-warning">
                            <i class="ti ti-users f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">الممثلين</h6>
                    </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                    <div class="mt-3 row align-items-center">
                        <div class="col-12">
                            <h3 class="mb-1">{{ $stats['representatives_total'] }}</h3>
                            <div class="d-flex justify-content-between mt-3">
                                <small class="text-success"><i class="ti ti-user-check"></i> نشطين: {{ $stats['representatives_active'] }}</small>
                                <small class="text-muted"><i class="ti ti-user-x"></i> غير نشطين: {{ $stats['representatives_total'] - $stats['representatives_active'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xxl-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-info">
                            <i class="ti ti-currency-dollar f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">إجمالي الإيرادات</h6>
                    </div>
                </div>
                <div class="bg-body p-3 mt-3 rounded">
                    <div class="mt-3 row align-items-center">
                        <div class="col-12">
                            <h3 class="mb-1">{{ number_format($stats['total_revenue'] + $stats['pharmaceutical_revenue'], 0) }} د.ل</h3>
                            <div class="d-flex justify-content-between mt-3">
                                <small class="text-primary"><i class="ti ti-trending-up"></i> من {{ $stats['invoices_paid'] + $stats['pharmaceutical_invoices_paid'] }} فاتورة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($pharmaceuticalProductsNeedApproval->count() > 0)
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-clock-check text-primary fs-4 me-2"></i>
                        <h5 class="mb-0">أصناف دوائية تحتاج موافقة</h5>
                    </div>
                    <span class="badge bg-primary">{{ $pharmaceuticalProductsNeedApproval->count() }}</span>
                </div>
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
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pharmaceuticalProductsNeedApproval as $product)
                            <tr>
                                <td><strong>{{ $product->product_name }}</strong></td>
                                <td><small class="text-muted">{{ $product->pharmaceutical_form }}</small></td>
                                <td>{{ $product->foreignCompany->company_name }}</td>
                                <td>{{ $product->representative->name }}</td>
                                <td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td>
                                <td><small>{{ $product->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.pharmaceutical-products.show', $product) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
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

    @if($pharmaceuticalInvoicesNeedReceipt->count() > 0)
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-triangle text-warning fs-4 me-2"></i>
                        <h5 class="mb-0">فواتير أدوية تحتاج رفع إيصال</h5>
                    </div>
                    <span class="badge bg-warning">{{ $pharmaceuticalInvoicesNeedReceipt->count() }}</span>
                </div>
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
                                <th>تاريخ الإصدار</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pharmaceuticalInvoicesNeedReceipt as $invoice)
                            <tr>
                                <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                                <td><strong>{{ $invoice->pharmaceuticalProduct->product_name }}</strong></td>
                                <td><span class="text-muted">{{ $invoice->pharmaceuticalProduct->representative->name }}</span></td>
                                <td><strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                                <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.pharmaceutical-products.show', $invoice->pharmaceuticalProduct) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
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

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">توزيع الشركات المحلية حسب الحالة</h5>
            </div>
            <div class="card-body">
                <canvas id="companiesChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">توزيع الأصناف الدوائية حسب الحالة</h5>
            </div>
            <div class="card-body">
                <canvas id="pharmaceuticalChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">الإيرادات الشهرية</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueMonthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">التسجيلات الشهرية</h5>
            </div>
            <div class="card-body">
                <canvas id="registrationsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">إحصائيات الفواتير</h5>
            </div>
            <div class="card-body">
                <canvas id="invoicesChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">الإيرادات حسب النوع</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">آخر الشركات المسجلة</h5>
                    <a href="{{ route('admin.local-companies.index') }}" class="btn btn-sm btn-link-primary">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اسم الشركة</th>
                                <th>الممثل</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLocalCompanies as $company)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avtar avtar-s bg-light-primary">
                                                <i class="ti ti-building"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0">{{ $company->company_name }}</h6>
                                            <small class="text-muted">{{ $company->company_type_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $company->representative?->name ?? '-' }}</td>
                                <td>{!! $company->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('admin.local-companies.show', $company) }}" class="btn btn-sm btn-link-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">لا توجد شركات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">آخر الأصناف الدوائية</h5>
                    <a href="{{ route('admin.pharmaceutical-products.index') }}" class="btn btn-sm btn-link-success">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اسم الصنف</th>
                                <th>الشركة</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPharmaceuticalProducts as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avtar avtar-s bg-light-success">
                                                <i class="ti ti-pill"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0">{{ $product->product_name }}</h6>
                                            <small class="text-muted">{{ $product->pharmaceutical_form }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $product->foreignCompany->company_name }}</small>
                                </td>
                                <td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td>
                                <td>
                                    <a href="{{ route('admin.pharmaceutical-products.show', $product) }}" class="btn btn-sm btn-link-success">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">لا توجد أصناف</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">آخر الشركات الأجنبية</h5>
                    <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-sm btn-link-info">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اسم الشركة</th>
                                <th>الدولة</th>
                                <th>الشركة المحلية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentForeignCompanies as $company)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avtar avtar-s bg-light-info">
                                                <i class="ti ti-world"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0">{{ $company->company_name }}</h6>
                                            <small class="text-muted">{{ $company->entity_type_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $company->country }}</td>
                                <td><small>{{ $company->localCompany->company_name }}</small></td>
                                <td>
                                    <a href="{{ route('admin.foreign-companies.show', $company) }}" class="btn btn-sm btn-link-info">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">لا توجد شركات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">نظرة عامة - الأصناف الدوائية</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_review']) }}" class="btn btn-link-secondary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="p-1 d-block bg-warning rounded-circle">
                                    <span class="visually-hidden">قيد المراجعة</span>
                                </span>
                            </div>
                            <div class="flex-grow-1 mx-2">
                                <p class="mb-0 d-grid text-start">
                                    <span>قيد المراجعة</span>
                                </p>
                            </div>
                            <div class="badge bg-light-warning">{{ $stats['pharmaceutical_products_pending_review'] }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'preliminary_approved']) }}" class="btn btn-link-secondary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="p-1 d-block bg-primary rounded-circle">
                                    <span class="visually-hidden">موافقة مبدئية</span>
                                </span>
                            </div>
                            <div class="flex-grow-1 mx-2">
                                <p class="mb-0 d-grid text-start">
                                    <span>موافقة مبدئية</span>
                                </p>
                            </div>
                            <div class="badge bg-light-primary">{{ $stats['pharmaceutical_products_preliminary_approved'] }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_final_approval']) }}" class="btn btn-link-secondary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="p-1 d-block bg-info rounded-circle">
                                    <span class="visually-hidden">موافقة نهائية</span>
                                </span>
                            </div>
                            <div class="flex-grow-1 mx-2">
                                <p class="mb-0 d-grid text-start">
                                    <span>قيد الموافقة النهائية</span>
                                </p>
                            </div>
                            <div class="badge bg-light-info">{{ $stats['pharmaceutical_products_pending_final_approval'] }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'payment_review']) }}" class="btn btn-link-secondary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="p-1 d-block bg-secondary rounded-circle">
                                    <span class="visually-hidden">مراجعة السداد</span>
                                </span>
                            </div>
                            <div class="flex-grow-1 mx-2">
                                <p class="mb-0 d-grid text-start">
                                    <span>قيد مراجعة السداد</span>
                                </p>
                            </div>
                            <div class="badge bg-light-secondary">{{ $stats['pharmaceutical_products_payment_review'] }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.pharmaceutical-products.index', ['status' => 'active']) }}" class="btn btn-link-secondary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="p-1 d-block bg-success rounded-circle">
                                    <span class="visually-hidden">معتمدة</span>
                                </span>
                            </div>
                            <div class="flex-grow-1 mx-2">
                                <p class="mb-0 d-grid text-start">
                                    <span>أصناف معتمدة</span>
                                </p>
                            </div>
                            <div class="badge bg-light-success">{{ $stats['pharmaceutical_products_active'] }}</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-card {
    transition: transform .15s ease, box-shadow .15s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 18px rgba(0,0,0,.08);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = 'Almarai, sans-serif';

const companiesChart = new Chart(document.getElementById('companiesChart'), {
    type: 'doughnut',
    data: {
        labels: ['نشطة', 'قيد المراجعة', 'موافق عليها', 'مرفوضة'],
        datasets: [{
            data: [
                {{ $stats['local_companies_active'] }},
                {{ $stats['local_companies_pending'] }},
                {{ $stats['local_companies_approved'] }},
                {{ $stats['local_companies_rejected'] }}
            ],
            backgroundColor: [
                '#10b981',
                '#f59e0b',
                '#3b82f6',
                '#ef4444'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                rtl: true,
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            }
        }
    }
});

const pharmaceuticalChart = new Chart(document.getElementById('pharmaceuticalChart'), {
    type: 'doughnut',
    data: {
        labels: [
            'معتمدة',
            'قيد المراجعة',
            'موافقة مبدئية',
            'موافقة نهائية',
            'قيد السداد',
            'مراجعة السداد',
            'مرفوضة'
        ],
        datasets: [{
            data: [
                {{ $stats['pharmaceutical_products_active'] }},
                {{ $stats['pharmaceutical_products_pending_review'] }},
                {{ $stats['pharmaceutical_products_preliminary_approved'] }},
                {{ $stats['pharmaceutical_products_pending_final_approval'] }},
                {{ $stats['pharmaceutical_products_pending_payment'] }},
                {{ $stats['pharmaceutical_products_payment_review'] }},
                {{ $stats['pharmaceutical_products_rejected'] }}
            ],
            backgroundColor: [
                '#10b981',
                '#f59e0b',
                '#3b82f6',
                '#8b5cf6',
                '#ec4899',
                '#06b6d4',
                '#ef4444'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                rtl: true,
                labels: {
                    padding: 10,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

const registrationsChart = new Chart(document.getElementById('registrationsChart'), {
    type: 'line',
    data: {
        labels: [
            @foreach($monthlyRegistrations as $reg)
                '{{ $reg->month }}',
            @endforeach
        ],
        datasets: [
            {
                label: 'الشركات المحلية',
                data: [
                    @foreach($monthlyRegistrations as $reg)
                        {{ $reg->count }},
                    @endforeach
                ],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'الأصناف الدوائية',
                data: [
                    @foreach($monthlyPharmaceuticalRegistrations as $reg)
                        {{ $reg->count }},
                    @endforeach
                ],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                rtl: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

const revenueMonthlyChart = new Chart(document.getElementById('revenueMonthlyChart'), {
    type: 'bar',
    data: {
        labels: [
            @foreach($monthlyRevenue as $rev)
                '{{ $rev->month }}',
            @endforeach
        ],
        datasets: [
            {
                label: 'الشركات المحلية',
                data: [
                    @foreach($monthlyRevenue as $rev)
                        {{ $rev->local_revenue }},
                    @endforeach
                ],
                backgroundColor: '#3b82f6'
            },
            {
                label: 'الأصناف الدوائية',
                data: [
                    @foreach($monthlyPharmaceuticalRevenue as $rev)
                        {{ $rev->pharma_revenue }},
                    @endforeach
                ],
                backgroundColor: '#10b981'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                rtl: true
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += new Intl.NumberFormat('ar-LY').format(context.parsed.y) + ' د.ل';
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('ar-LY').format(value) + ' د.ل';
                    }
                }
            }
        }
    }
});

const invoicesChart = new Chart(document.getElementById('invoicesChart'), {
    type: 'bar',
    data: {
        labels: ['الشركات المحلية', 'الأصناف الدوائية'],
        datasets: [
            {
                label: 'مدفوعة',
                data: [
                    {{ $stats['invoices_paid'] }},
                    {{ $stats['pharmaceutical_invoices_paid'] }}
                ],
                backgroundColor: '#10b981'
            },
            {
                label: 'غير مدفوعة',
                data: [
                    {{ $stats['invoices_unpaid'] }},
                    {{ $stats['pharmaceutical_invoices_unpaid'] }}
                ],
                backgroundColor: '#ef4444'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                rtl: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

const revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'pie',
    data: {
        labels: ['الشركات المحلية', 'الأصناف الدوائية'],
        datasets: [{
            data: [
                {{ $stats['total_revenue'] }},
                {{ $stats['pharmaceutical_revenue'] }}
            ],
            backgroundColor: [
                '#3b82f6',
                '#10b981'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                rtl: true,
                labels: {
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += new Intl.NumberFormat('ar-LY').format(context.parsed) + ' د.ل';
                        return label;
                    }
                }
            }
        }
    }
});
</script>
@endpush
