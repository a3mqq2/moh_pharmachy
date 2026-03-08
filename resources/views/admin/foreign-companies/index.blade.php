@extends('layouts.app')

@section('title', 'الشركات الأجنبية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الشركات الأجنبية</li>
@endsection

@section('content')


<div class="card mb-3 mt-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="ti ti-filter me-1"></i> الفلاتر
                </button>
                <span class="badge bg-secondary align-self-center">{{ $companies->total() }} شركة</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.foreign-companies.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-printer me-1"></i> طباعة كشف
                </a>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'entity_type', 'activity_type', 'country', 'date_from', 'date_to']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الشركة، البريد الإلكتروني..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>قيد السداد</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبولة</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مفعلة</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>معلقة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع الكيان</label>
                        <select name="entity_type" class="form-select">
                            <option value="">الكل</option>
                            <option value="company" {{ request('entity_type') == 'company' ? 'selected' : '' }}>شركة</option>
                            <option value="factory" {{ request('entity_type') == 'factory' ? 'selected' : '' }}>مصنع</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع النشاط</label>
                        <select name="activity_type" class="form-select">
                            <option value="">الكل</option>
                            <option value="medicines" {{ request('activity_type') == 'medicines' ? 'selected' : '' }}>أدوية</option>
                            <option value="medical_supplies" {{ request('activity_type') == 'medical_supplies' ? 'selected' : '' }}>مستلزمات طبية</option>
                            <option value="both" {{ request('activity_type') == 'both' ? 'selected' : '' }}>كلاهما</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الدولة</label>
                        <input type="text" name="country" class="form-control" placeholder="اسم الدولة" value="{{ request('country') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> بحث
                            </button>
                            @if(request()->hasAny(['search', 'status', 'entity_type', 'activity_type', 'country', 'date_from', 'date_to']))
                                <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i> مسح الفلاتر
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="text-white" style="background-color: #000000;">
                    <tr>
                        <th class="text-white">اسم الشركة</th>
                        <th class="text-white">الدولة</th>
                        <th class="text-white">النوع</th>
                        <th class="text-white">النشاط</th>
                        <th class="text-white">الشركة المحلية</th>
                        <th class="text-white">المستندات</th>
                        <th class="text-white">الحالة</th>
                        <th class="text-white">تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr onclick="window.location='{{ route('admin.foreign-companies.show', $company) }}'" style="cursor: pointer;">
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                            @if($company->email)
                                <br><small class="text-muted">{{ $company->email }}</small>
                            @endif
                        </td>
                        <td>{{ $company->country }}</td>
                        <td>
                            <span class="badge bg-{{ $company->entity_type == 'factory' ? 'info' : 'primary' }}">{{ $company->entity_type_name }}</span>
                        </td>
                        <td>{{ $company->activity_type_name }}</td>
                        <td>
                            @if($company->localCompany)
                                <strong>{{ $company->localCompany->company_name }}</strong>
                                @if($company->localCompany->registration_number)
                                    <br><span class="badge bg-dark">{{ $company->localCompany->registration_number }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $company->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }}">
                                {{ $company->documents->count() }} مستند
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ str_replace('badge-', 'bg-', $company->status_badge_class) }}">{{ $company->status_name }}</span>
                        </td>
                        <td>
                            <small>{{ $company->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $company->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-world fs-1 d-block mb-2"></i>
                                لا توجد شركات أجنبية مسجلة
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($companies->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $companies->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
