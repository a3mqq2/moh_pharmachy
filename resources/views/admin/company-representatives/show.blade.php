@extends('layouts.app')

@section('title', $representative->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.company-representatives.index') }}">ممثلي الشركات</a></li>
    <li class="breadcrumb-item active">{{ $representative->name }}</li>
@endsection

@php
    $allProducts = $representative->foreignCompanies->flatMap->pharmaceuticalProducts;
@endphp

@section('content')

<div class="show-header mt-3 mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-2"><i class="ti ti-id me-2 text-primary"></i>{{ $representative->name }}</h4>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if($representative->job_title)
                    <span class="text-muted">{{ $representative->job_title }}</span>
                @endif
                @if($representative->is_verified)
                    <span class="badge bg-success">موثق</span>
                @else
                    <span class="badge bg-danger">غير موثق</span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('admin.company-representatives.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-right me-1"></i>رجوع
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="repTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">
                    <i class="ti ti-user me-1"></i>البيانات الشخصية
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-local">
                    <i class="ti ti-building-skyscraper me-1"></i>الشركات المحلية
                    @if($representative->companies->count() > 0)
                        <span class="badge bg-info rounded-pill ms-1">{{ $representative->companies->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-foreign">
                    <i class="ti ti-world me-1"></i>الشركات الأجنبية
                    @if($representative->foreignCompanies->count() > 0)
                        <span class="badge bg-warning rounded-pill ms-1">{{ $representative->foreignCompanies->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-products">
                    <i class="ti ti-pill me-1"></i>الأصناف الدوائية
                    @if($allProducts->count() > 0)
                        <span class="badge bg-primary rounded-pill ms-1">{{ $allProducts->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane fade show active" id="tab-info">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-user me-2"></i>معلومات الممثل</h6>
                        <div class="table-responsive">
                            <table class="table table-striped info-table">
                                <tr><th class="bg-light" width="40%">الاسم</th><td>{{ $representative->name }}</td></tr>
                                <tr><th class="bg-light">المسمى الوظيفي</th><td>{{ $representative->job_title ?? '-' }}</td></tr>
                                <tr>
                                    <th class="bg-light">حالة التوثيق</th>
                                    <td>
                                        @if($representative->is_verified)
                                            <span class="badge bg-light-success">موثق</span>
                                        @else
                                            <span class="badge bg-light-danger">غير موثق</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th class="bg-light">تاريخ التسجيل</th><td>{{ $representative->created_at->format('Y-m-d h:i A') }}</td></tr>
                                @if($representative->email_verified_at)
                                <tr><th class="bg-light">تاريخ التوثيق</th><td>{{ $representative->email_verified_at->format('Y-m-d h:i A') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-phone me-2"></i>معلومات الاتصال</h6>
                        <div class="table-responsive">
                            <table class="table table-striped info-table">
                                <tr><th class="bg-light" width="40%">البريد الإلكتروني</th><td><a href="mailto:{{ $representative->email }}">{{ $representative->email }}</a></td></tr>
                                <tr><th class="bg-light">الهاتف</th><td dir="ltr" class="text-end">{{ $representative->phone ?? '-' }}</td></tr>
                                <tr><th class="bg-light">الشركات المحلية</th><td><span class="badge bg-light-info">{{ $representative->companies->count() }}</span></td></tr>
                                <tr><th class="bg-light">الشركات الأجنبية</th><td><span class="badge bg-light-warning">{{ $representative->foreignCompanies->count() }}</span></td></tr>
                                <tr><th class="bg-light">الأصناف الدوائية</th><td><span class="badge bg-light-primary">{{ $allProducts->count() }}</span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-local">
                @if($representative->companies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الشركة</th>
                                <th>نوع النشاط</th>
                                <th>الحالة</th>
                                <th>تاريخ التسجيل</th>
                                <th class="text-center" width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($representative->companies as $company)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('admin.local-companies.show', $company->id) }}" class="fw-semibold text-primary">
                                            {{ $company->company_name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($company->company_type === 'distributor')
                                            <span class="badge bg-light-info"><i class="ti ti-truck-delivery me-1"></i>موزع</span>
                                        @elseif($company->company_type === 'supplier')
                                            <span class="badge bg-light-warning"><i class="ti ti-package me-1"></i>مورد</span>
                                        @else
                                            {{ $company->company_type ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @switch($company->status)
                                            @case('uploading_documents')
                                                <span class="badge bg-light-secondary">رفع المستندات</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-light-warning">قيد المراجعة</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-light-primary">مقبولة</span>
                                                @break
                                            @case('payment_review')
                                                <span class="badge bg-light-info">مراجعة الدفع</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-light-success">مفعلة</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-light-danger">مرفوضة</span>
                                                @break
                                            @case('suspended')
                                                <span class="badge bg-light-dark">معلقة</span>
                                                @break
                                            @default
                                                <span class="badge bg-light-secondary">{{ $company->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $company->created_at->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.local-companies.show', $company->id) }}" class="avtar avtar-xs btn-link-primary">
                                            <i class="ti ti-eye f-18"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-building-skyscraper f-40 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">لا توجد شركات محلية مسجلة</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-foreign">
                @if($representative->foreignCompanies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الشركة</th>
                                <th>الدولة</th>
                                <th>نوع الكيان</th>
                                <th>نوع النشاط</th>
                                <th>الحالة</th>
                                <th>تاريخ التسجيل</th>
                                <th class="text-center" width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($representative->foreignCompanies as $company)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('admin.foreign-companies.show', $company->id) }}" class="fw-semibold text-primary">
                                            {{ $company->company_name }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-dark">{{ $company->country ?? '-' }}</span></td>
                                    <td>
                                        @if($company->entity_type === 'factory')
                                            <span class="badge bg-light-info">مصنع</span>
                                        @else
                                            <span class="badge bg-light-primary">شركة</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($company->activity_type === 'medicines')
                                            <span class="badge bg-light-primary">أدوية</span>
                                        @elseif($company->activity_type === 'medical_supplies')
                                            <span class="badge bg-light-info">مستلزمات طبية</span>
                                        @elseif($company->activity_type === 'both')
                                            <span class="badge bg-light-success">كلاهما</span>
                                        @else
                                            {{ $company->activity_type ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @switch($company->status)
                                            @case('uploading_documents')
                                                <span class="badge bg-light-secondary">رفع المستندات</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-light-warning">قيد المراجعة</span>
                                                @break
                                            @case('pending_payment')
                                                <span class="badge bg-light-info">قيد السداد</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-light-primary">مقبولة</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-light-success">مفعلة</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-light-danger">مرفوضة</span>
                                                @break
                                            @case('suspended')
                                                <span class="badge bg-light-dark">معلقة</span>
                                                @break
                                            @default
                                                <span class="badge bg-light-secondary">{{ $company->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $company->created_at->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.foreign-companies.show', $company->id) }}" class="avtar avtar-xs btn-link-primary">
                                            <i class="ti ti-eye f-18"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-world f-40 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">لا توجد شركات أجنبية مسجلة</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-products">
                @if($allProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم المنتج</th>
                                <th>الاسم العلمي</th>
                                <th>الشكل الصيدلاني</th>
                                <th>التركيز</th>
                                <th>الشركة المصنعة</th>
                                <th>الحالة</th>
                                <th class="text-center" width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allProducts as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('admin.pharmaceutical-products.show', $product->id) }}" class="fw-semibold text-primary">
                                            {{ $product->product_name }}
                                        </a>
                                        @if($product->trade_name)
                                            <small class="text-muted d-block">{{ $product->trade_name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->scientific_name ?? '-' }}</td>
                                    <td>{{ $product->pharmaceutical_form ?? '-' }}</td>
                                    <td>{{ $product->concentration ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.foreign-companies.show', $product->foreign_company_id) }}" class="text-primary">
                                            {{ $product->foreignCompany->company_name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>
                                        @switch($product->status)
                                            @case('pending_review')
                                                <span class="badge bg-light-warning">قيد المراجعة</span>
                                                @break
                                            @case('preliminary_approved')
                                                <span class="badge bg-light-info">موافقة مبدئية</span>
                                                @break
                                            @case('pending_payment')
                                                <span class="badge bg-light-info">قيد السداد</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-light-success">مفعل</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-light-danger">مرفوض</span>
                                                @break
                                            @default
                                                <span class="badge bg-light-secondary">{{ $product->status }}</span>
                                        @endswitch
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.pharmaceutical-products.show', $product->id) }}" class="avtar avtar-xs btn-link-primary">
                                            <i class="ti ti-eye f-18"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-pill f-40 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">لا توجد أصناف دوائية مسجلة</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection
