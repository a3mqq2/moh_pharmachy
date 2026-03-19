@extends('layouts.app')

@section('title', 'تقرير الشركات المحلية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">الشركات المحلية</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>تقرير الشركات المحلية</h5>
                @if($filtered)
                <span class="badge bg-secondary">{{ $stats['total'] }} شركة</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.reports.local-companies', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <a href="{{ route('admin.reports.local-companies', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> تصدير Excel
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.local-companies') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                        <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمدة</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مفعلة</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
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
                            <a href="{{ route('admin.reports.local-companies') }}" class="btn btn-outline-secondary">
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
                        <th>اسم الشركة</th>
                        <th>الممثل</th>
                        <th>الحالة</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td><span class="badge bg-dark">{{ method_exists($companies, 'currentPage') ? ($companies->currentPage() - 1) * $companies->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                        <td><strong>{{ $company->company_name }}</strong></td>
                        <td>{{ $company->representative?->name ?? '-' }}</td>
                        <td><span class="badge bg-{{ $company->status_color }}">{{ $company->status_name }}</span></td>
                        <td><small>{{ $company->created_at->format('Y-m-d') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-building fs-1 d-block mb-2"></i>
                                لا توجد نتائج
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">إجمالي الشركات:</th>
                        <th>{{ $stats['total'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">مفعلة:</th>
                        <th class="text-success">{{ $stats['active'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">قيد المراجعة:</th>
                        <th class="text-warning">{{ $stats['pending'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">معتمدة:</th>
                        <th class="text-primary">{{ $stats['approved'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">مرفوضة:</th>
                        <th class="text-danger">{{ $stats['rejected'] }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @if(method_exists($companies, 'hasPages') && $companies->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $companies->links() }}
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
