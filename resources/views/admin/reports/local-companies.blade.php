@extends('layouts.app')
@section('title', 'تقرير الشركات المحلية')
@section('content')
<div class="container-fluid mt-3">

    <div class="card mb-4">
        <div class="card-body">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="ti ti-arrow-right me-1"></i>
                        العودة للتقارير
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="window.open('{{ route('admin.reports.local-companies', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                        <i class="ti ti-printer me-1"></i>
                        طباعة
                    </button>
                    <a href="{{ route('admin.reports.local-companies', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success">
                        <i class="ti ti-file-spreadsheet me-1"></i>
                        تصدير Excel
                    </a>
                </div>
            </div>
        </div>
            <h5 class="mb-3">
                <i class="ti ti-filter me-2"></i>
                تصفية النتائج
            </h5>
            <form method="GET" action="{{ route('admin.reports.local-companies') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="uploading_documents" {{ request('status') === 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمدة</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>مفعلة</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوضة</option>
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i>
                            بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
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
                            <td>{{ ($companies->currentPage() - 1) * $companies->perPage() + $loop->iteration }}</td>
                            <td><strong>{{ $company->company_name }}</strong></td>
                            <td>{{ $company->representative?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $company->status_color }}">{{ $company->status_name }}</span></td>
                            <td>{{ $company->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">لا توجد نتائج</td>
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

            @if($companies->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $companies->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
