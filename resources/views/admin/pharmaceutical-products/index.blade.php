@extends('layouts.app')

@section('title', 'الأصناف الدوائية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الأصناف الدوائية</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-capsules me-2"></i>الأصناف الدوائية</h5>
                <span class="badge bg-secondary">{{ $products->total() }} صنف دوائي</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> الفلاتر
                </button>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'foreign_company', 'local_company']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الصنف الدوائي..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                            <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="preliminary_approved" {{ request('status') == 'preliminary_approved' ? 'selected' : '' }}>موافقة مبدئية</option>
                            <option value="pending_final_approval" {{ request('status') == 'pending_final_approval' ? 'selected' : '' }}>قيد الموافقة النهائية</option>
                            <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>قيد السداد</option>
                            <option value="payment_review" {{ request('status') == 'payment_review' ? 'selected' : '' }}>قيد مراجعة السداد</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>معتمد</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الشركة الأجنبية</label>
                        <input type="text" name="foreign_company" class="form-control" placeholder="اسم الشركة الأجنبية..." value="{{ request('foreign_company') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الشركة المحلية</label>
                        <input type="text" name="local_company" class="form-control" placeholder="اسم الشركة المحلية..." value="{{ request('local_company') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-9"></div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            @if(request()->hasAny(['search', 'status', 'foreign_company', 'local_company']))
                                <a href="{{ route('admin.pharmaceutical-products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> مسح الفلاتر
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم التجاري</th>
                        <th>الاسم العلمي</th>
                        <th>الشكل الصيدلاني</th>
                        <th>الشركة الأجنبية</th>
                        <th>الشركة المحلية</th>
                        <th>الممثل</th>
                        <th>الحالة</th>
                        <th>تاريخ التقديم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr onclick="window.location='{{ route('admin.pharmaceutical-products.show', $product) }}'" style="cursor: pointer;">
                        <td>
                            <span class="badge bg-dark">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</span>
                        </td>
                        <td>
                            <strong>{{ $product->product_name }}</strong>
                            @if($product->registration_number)
                                <br><small class="text-muted">{{ $product->registration_number }}</small>
                            @endif
                        </td>
                        <td>{{ $product->scientific_name }}</td>
                        <td>{{ $product->pharmaceutical_form }}</td>
                        <td>{{ $product->foreignCompany->company_name }}</td>
                        <td>{{ $product->foreignCompany->localCompany->company_name }}</td>
                        <td>
                            <div>{{ $product->representative->name }}</div>
                            <div class="d-flex gap-2 mt-1">
                                <a href="tel:{{ $product->representative->phone }}" class="text-decoration-none" onclick="event.stopPropagation();" title="اتصال">
                                    <i class="fas fa-phone text-primary"></i>
                                </a>
                                @if($product->representative->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $product->representative->phone) }}" target="_blank" class="text-decoration-none" onclick="event.stopPropagation();" title="واتساب">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </a>
                                @endif
                                <small class="text-muted" dir="ltr">{{ $product->representative->phone }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span>
                        </td>
                        <td>
                            <small>{{ $product->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $product->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-capsules fs-1 d-block mb-2"></i>
                                لا توجد أصناف دوائية مسجلة
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
