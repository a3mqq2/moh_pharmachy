@extends('layouts.app')

@section('title', 'تقرير الأصناف الدوائية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">الأصناف الدوائية</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-capsules me-2"></i>تقرير الأصناف الدوائية</h5>
                @if($filtered)
                <span class="badge bg-secondary">{{ $stats['total'] }} صنف</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.reports.pharmaceutical-products', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <a href="{{ route('admin.reports.pharmaceutical-products', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> تصدير Excel
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.pharmaceutical-products') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>قيد المراجعة</option>
                        <option value="preliminary_approved" {{ request('status') == 'preliminary_approved' ? 'selected' : '' }}>موافقة مبدئية</option>
                        <option value="pending_final_approval" {{ request('status') == 'pending_final_approval' ? 'selected' : '' }}>قيد الموافقة النهائية</option>
                        <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>قيد السداد</option>
                        <option value="payment_review" {{ request('status') == 'payment_review' ? 'selected' : '' }}>قيد مراجعة السداد</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>معتمدة</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.pharmaceutical-products') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> مسح
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
    @if($filtered)
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم التجاري</th>
                        <th>رقم القيد</th>
                        <th>الاسم العلمي</th>
                        <th>الشكل الصيدلاني</th>
                        <th>التركيز</th>
                        <th>الشركة الأجنبية</th>
                        <th>الممثل</th>
                        <th>الحالة</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td><span class="badge bg-dark">{{ method_exists($products, 'currentPage') ? ($products->currentPage() - 1) * $products->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                        <td><strong>{{ $product->product_name }}</strong></td>
                        <td>{{ $product->registration_number ?? '-' }}</td>
                        <td>{{ $product->scientific_name }}</td>
                        <td>{{ $product->pharmaceutical_form }}</td>
                        <td>{{ $product->concentration }}</td>
                        <td>{{ $product->foreignCompany->company_name }}</td>
                        <td>{{ $product->representative->name }}</td>
                        <td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td>
                        <td><small>{{ $product->created_at->format('Y-m-d') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-capsules fs-1 d-block mb-2"></i>
                                لا توجد نتائج
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="9" class="text-end">إجمالي الأصناف:</th>
                        <th>{{ $stats['total'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">معتمدة:</th>
                        <th class="text-success">{{ $stats['active'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">قيد المراجعة:</th>
                        <th class="text-warning">{{ $stats['pending_review'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">موافقة مبدئية:</th>
                        <th class="text-primary">{{ $stats['preliminary_approved'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">قيد الموافقة النهائية:</th>
                        <th class="text-info">{{ $stats['pending_final_approval'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">قيد السداد:</th>
                        <th class="text-warning">{{ $stats['pending_payment'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">قيد مراجعة السداد:</th>
                        <th class="text-info">{{ $stats['payment_review'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">مرفوضة:</th>
                        <th class="text-danger">{{ $stats['rejected'] }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @if(method_exists($products, 'hasPages') && $products->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
    @endif
    @else
    <div class="card-body text-center py-5">
        <div class="text-muted">
            <i class="fas fa-filter fs-1 d-block mb-3"></i>
            <h5>استخدم الفلاتر أعلاه لعرض النتائج</h5>
            <p>اختر الحالة أو التاريخ ثم اضغط بحث</p>
        </div>
    </div>
    @endif
</div>

@endsection
