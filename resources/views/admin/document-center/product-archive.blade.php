@extends('layouts.app')

@section('title', __('documents.product_archive'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('documents.product_docs_archive') }}</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti ti-pill me-2"></i>{{ __('documents.product_docs_archive') }}</h5>
            <form method="GET" action="{{ route('admin.document-center.product-archive') }}" class="d-flex gap-2 flex-wrap align-items-center">
                <select name="product_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $productStatus == 'all' ? 'selected' : '' }}>{{ __('documents.all_statuses') }}</option>
                    <option value="uploading_documents" {{ $productStatus == 'uploading_documents' ? 'selected' : '' }}>{{ __('products.status_uploading_docs') }}</option>
                    <option value="pending_review" {{ $productStatus == 'pending_review' ? 'selected' : '' }}>{{ __('products.status_pending_review') }}</option>
                    <option value="active" {{ $productStatus == 'active' ? 'selected' : '' }}>{{ __('products.status_approved') }}</option>
                    <option value="rejected" {{ $productStatus == 'rejected' ? 'selected' : '' }}>{{ __('products.status_rejected') }}</option>
                </select>
                <select name="doc_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $docStatus == 'all' ? 'selected' : '' }}>{{ __('documents.all_documents') }}</option>
                    <option value="complete" {{ $docStatus == 'complete' ? 'selected' : '' }}>{{ __('documents.complete_docs') }}</option>
                    <option value="incomplete" {{ $docStatus == 'incomplete' ? 'selected' : '' }}>{{ __('documents.incomplete_docs') }}</option>
                </select>
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('documents.search_trade_scientific') }}" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
                </div>
                @if(request('search') || request('product_status') || request('doc_status'))
                <a href="{{ route('admin.document-center.product-archive') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
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
                                    'uploading_documents' => __('products.status_uploading_docs'),
                                    'pending_review' => __('products.status_pending_review'),
                                    'preliminary_approved' => __('products.status_preliminary_approved'),
                                    'pending_final_approval' => __('products.status_pending_final'),
                                    'pending_payment' => __('products.status_pending_payment'),
                                    'payment_review' => __('products.status_payment_review'),
                                    'rejected' => __('products.status_rejected'),
                                    'active' => __('products.status_approved'),
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
                                <i class="ti ti-files me-1"></i>{{ $product['total_docs'] }} {{ __('documents.doc_count') }}
                            </small>
                            @if($product['missing_count'] > 0)
                                <span class="badge bg-light-danger text-danger" style="font-size: 0.65rem;">{{ $product['missing_count'] }} {{ __('documents.missing_label') }}</span>
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
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $product['uploaded_required'] }}/{{ $product['required_count'] }} {{ __('documents.required_label') }}</small>
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
                                    <th>{{ __('documents.document_type') }}</th>
                                    <th>{{ __('documents.document_name') }}</th>
                                    <th>{{ __('documents.file_size') }}</th>
                                    <th>{{ __('general.date') }}</th>
                                    <th style="width: 100px;">{{ __('general.actions') }}</th>
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
                                            <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" title="{{ __('general.view') }}"
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
                        <small>{{ __('documents.no_uploaded_docs') }}</small>
                    </div>
                    @endif
                    <div class="mt-2 text-end">
                        <a href="{{ $product['route'] }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-external-link me-1"></i>{{ __('documents.view_product') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="ti ti-folder-off fs-1 d-block mb-3"></i>
            <h6>{{ __('documents.no_matching_products') }}</h6>
            <small>{{ __('documents.try_different_search') }}</small>
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
