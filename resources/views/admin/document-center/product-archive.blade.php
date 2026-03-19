@extends('layouts.app')

@section('title', 'أرشيف الأدوية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أرشيف مستندات الأدوية</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti ti-pill me-2"></i>أرشيف مستندات الأدوية</h5>
            <form method="GET" action="{{ route('admin.document-center.product-archive') }}" class="d-flex gap-2 flex-wrap align-items-center">
                <select name="product_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $productStatus == 'all' ? 'selected' : '' }}>جميع الحالات</option>
                    <option value="uploading_documents" {{ $productStatus == 'uploading_documents' ? 'selected' : '' }}>قيد رفع المستندات</option>
                    <option value="pending_review" {{ $productStatus == 'pending_review' ? 'selected' : '' }}>قيد المراجعة</option>
                    <option value="active" {{ $productStatus == 'active' ? 'selected' : '' }}>معتمد</option>
                    <option value="rejected" {{ $productStatus == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                </select>
                <select name="doc_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $docStatus == 'all' ? 'selected' : '' }}>جميع المستندات</option>
                    <option value="complete" {{ $docStatus == 'complete' ? 'selected' : '' }}>مستندات مكتملة</option>
                    <option value="incomplete" {{ $docStatus == 'incomplete' ? 'selected' : '' }}>مستندات ناقصة</option>
                </select>
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم التجاري أو العلمي..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
                </div>
                @if(request('search') || request('product_status') || request('doc_status'))
                <a href="{{ route('admin.document-center.product-archive') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-x me-1"></i>مسح
                </a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        @forelse($products as $product)
        <div class="product-archive-item border-bottom">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 product-toggle" role="button" data-bs-toggle="collapse" data-bs-target="#product-{{ $product['id'] }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 44px; height: 44px; background: #ecfdf5;">
                        <i class="ti ti-pill text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="mb-0 fw-bold">{{ $product['trade_name'] }}</h6>
                            @php
                                $statusBadge = match($product['status']) {
                                    'active' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'pending_review', 'pending_final_approval' => 'bg-warning',
                                    'uploading_documents' => 'bg-info',
                                    default => 'bg-secondary',
                                };
                                $statusName = match($product['status']) {
                                    'uploading_documents' => 'قيد رفع المستندات',
                                    'pending_review' => 'قيد المراجعة',
                                    'preliminary_approved' => 'موافقة مبدئية',
                                    'pending_final_approval' => 'قيد الموافقة النهائية',
                                    'pending_payment' => 'قيد السداد',
                                    'payment_review' => 'قيد مراجعة السداد',
                                    'rejected' => 'مرفوض',
                                    'active' => 'معتمد',
                                    default => $product['status'],
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}" style="font-size: 0.7rem;">{{ $statusName }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @if($product['scientific_name'])
                            <small class="text-muted">
                                <i class="ti ti-flask me-1"></i>{{ $product['scientific_name'] }}
                            </small>
                            @endif
                            <small class="text-muted">
                                <i class="ti ti-building me-1"></i>{{ $product['company_name'] }}
                            </small>
                            <small class="text-muted">
                                <i class="ti ti-files me-1"></i>{{ $product['total_docs'] }} مستند
                            </small>
                            @if($product['missing_count'] > 0)
                                <span class="badge bg-light-danger text-danger" style="font-size: 0.65rem;">{{ $product['missing_count'] }} ناقص</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center" style="min-width: 120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar {{ $product['completion'] == 100 ? 'bg-success' : ($product['completion'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width: {{ $product['completion'] }}%; border-radius: 4px;"></div>
                            </div>
                            <small class="fw-bold {{ $product['completion'] == 100 ? 'text-success' : ($product['completion'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $product['completion'] }}%</small>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $product['uploaded_required'] }}/{{ $product['required_count'] }} مطلوب</small>
                    </div>
                    <i class="ti ti-chevron-down text-muted product-chevron"></i>
                </div>
            </div>
            <div class="collapse" id="product-{{ $product['id'] }}">
                <div class="px-3 pb-3">
                    @if($product['documents']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نوع المستند</th>
                                    <th>اسم الملف</th>
                                    <th>الحجم</th>
                                    <th>التاريخ</th>
                                    <th style="width: 100px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product['documents'] as $doc)
                                <tr>
                                    <td>{{ $doc['id'] }}</td>
                                    <td class="fw-medium">{{ $doc['type_name'] }}</td>
                                    <td><small class="text-muted">{{ Str::limit($doc['original_name'], 35) }}</small></td>
                                    <td>
                                        @if($doc['file_size'])
                                            <small>{{ $doc['file_size'] >= 1048576 ? number_format($doc['file_size'] / 1048576, 1) . ' MB' : number_format($doc['file_size'] / 1024, 1) . ' KB' }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><small>{{ $doc['created_at']->format('Y-m-d') }}</small></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" title="عرض"
                                                data-file-url="{{ $doc['file_url'] }}"
                                                data-file-name="{{ $doc['original_name'] ?? $doc['type_name'] }}"
                                                data-download-url="{{ $doc['file_url'] }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="ti ti-folder-off fs-3 d-block mb-2"></i>
                        <small>لا توجد مستندات مرفوعة</small>
                    </div>
                    @endif
                    <div class="mt-2 text-end">
                        <a href="{{ $product['route'] }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-external-link me-1"></i>عرض الصنف
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="ti ti-folder-off fs-1 d-block mb-3"></i>
            <h6>لا توجد أصناف مطابقة</h6>
            <small>جرب تغيير معايير البحث</small>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('css')
<style>
.product-archive-item:last-child { border-bottom: none !important; }
.product-toggle { cursor: pointer; transition: background 0.2s; }
.product-toggle:hover { background: #f8fafc; }
.product-toggle[aria-expanded="true"] .product-chevron { transform: rotate(180deg); }
.product-chevron { transition: transform 0.2s; }
.bg-light-danger { background-color: #fef2f2 !important; }
</style>
@endpush
