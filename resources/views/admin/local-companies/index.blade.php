@extends('layouts.app')

@php
    $pageTitle = 'الشركات المحلية';
    if (request('company_type') == 'distributor') {
        $pageTitle = 'الشركات الموزعة';
    } elseif (request('company_type') == 'supplier') {
        $pageTitle = 'الشركات الموردة';
    }
@endphp
@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الشركات المحلية</li>
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
                <a href="{{ route('admin.local-companies.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-printer me-1"></i> طباعة كشف
                </a>
                <a href="{{ route('admin.local-companies.create') }}" class="btn btn-success btn-sm">
                    <i class="ti ti-plus me-1"></i> إضافة شركة جديدة
                </a>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'company_type', 'license_type', 'license_specialty', 'city', 'date_from', 'date_to', 'missing_docs']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الشركة، البريد، رقم القيد..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تصنيف الشركة</label>
                        <select name="company_type" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\LocalCompany::companyTypes() as $key => $value)
                                <option value="{{ $key }}" {{ request('company_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\LocalCompany::statuses() as $key => $value)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع الترخيص</label>
                        <select name="license_type" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\LocalCompany::licenseTypes() as $key => $value)
                                <option value="{{ $key }}" {{ request('license_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">التخصص</label>
                        <select name="license_specialty" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\LocalCompany::licenseSpecialties() as $key => $value)
                                <option value="{{ $key }}" {{ request('license_specialty') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-2">
                        <label class="form-label">المدينة</label>
                        <input type="text" name="city" class="form-control" placeholder="اسم المدينة" value="{{ request('city') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="missing_docs" value="1" class="form-check-input" id="missing_docs" {{ request('missing_docs') ? 'checked' : '' }}>
                            <label class="form-check-label" for="missing_docs">
                                <i class="ti ti-alert-triangle text-warning me-1"></i>
                                شركات بمستندات ناقصة
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> بحث
                            </button>
                            @if(request()->hasAny(['search', 'status', 'company_type', 'license_type', 'license_specialty', 'city', 'date_from', 'date_to', 'missing_docs']))
                                <a href="{{ route('admin.local-companies.index') }}" class="btn btn-outline-secondary">
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
                        <th class="text-white">رقم القيد</th>
                        <th class="text-white">اسم الشركة</th>
                        <th class="text-white">التصنيف</th>
                        <th class="text-white">المدير المسؤول</th>
                        <th class="text-white">المدينة</th>
                        <th class="text-white">الترخيص</th>
                        <th class="text-white">الحالة</th>
                        <th class="text-white">تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr onclick="window.location='{{ route('admin.local-companies.show', $company) }}'" style="cursor: pointer;">
                        <td>
                            @if($company->registration_number)
                                <span class="badge bg-dark">{{ $company->registration_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-{{ $company->company_type == 'distributor' ? 'info' : 'primary' }}">{{ $company->company_type_name }}</span>
                        </td>
                        <td>
                            <div>{{ $company->manager_name }}</div>
                            <div class="d-flex gap-2 mt-1">
                                <a href="tel:{{ $company->manager_phone }}" class="text-decoration-none" onclick="event.stopPropagation();" title="اتصال">
                                    <i class="ti ti-phone text-primary"></i>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->manager_phone) }}" target="_blank" class="text-decoration-none" onclick="event.stopPropagation();" title="واتساب">
                                    <i class="ti ti-brand-whatsapp text-success"></i>
                                </a>
                                <small class="text-muted" dir="ltr">{{ $company->manager_phone }}</small>
                            </div>
                        </td>
                        <td>{{ $company->city }}</td>
                        <td>
                            <small>{{ $company->license_type_name }}</small><br>
                            <small class="text-muted">{{ $company->license_specialty_name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $company->status_color }}">{{ $company->status_name }}</span>
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
                                <i class="ti ti-building-store fs-1 d-block mb-2"></i>
                                لا توجد شركات مسجلة
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
