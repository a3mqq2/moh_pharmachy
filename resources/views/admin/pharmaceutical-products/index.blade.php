@extends('layouts.app')

@section('title', __('products.pharmaceutical_products'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('products.pharmaceutical_products') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-capsules me-2"></i>{{ __('products.pharmaceutical_products') }}</h5>
                <span class="badge bg-secondary">{{ $products->total() }} {{ __('products.product') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filters') }}
                </button>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'foreign_company', 'local_company']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('general.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('products.product_name_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>{{ __('companies.status_uploading_docs') }}</option>
                            <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>{{ __('products.status_pending_review') }}</option>
                            <option value="preliminary_approved" {{ request('status') == 'preliminary_approved' ? 'selected' : '' }}>{{ __('products.status_preliminary_approved') }}</option>
                            <option value="pending_final_approval" {{ request('status') == 'pending_final_approval' ? 'selected' : '' }}>{{ __('products.status_pending_final') }}</option>
                            <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>{{ __('products.status_pending_payment') }}</option>
                            <option value="payment_review" {{ request('status') == 'payment_review' ? 'selected' : '' }}>{{ __('products.status_payment_review') }}</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('general.rejected') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('products.status_active') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('companies.foreign_company') }}</label>
                        <input type="text" name="foreign_company" class="form-control" placeholder="{{ __('companies.foreign_company_name_placeholder') }}" value="{{ request('foreign_company') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('companies.local_company') }}</label>
                        <input type="text" name="local_company" class="form-control" placeholder="{{ __('companies.local_company_name_placeholder') }}" value="{{ request('local_company') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-9"></div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                            </button>
                            @if(request()->hasAny(['search', 'status', 'foreign_company', 'local_company']))
                                <a href="{{ route('admin.pharmaceutical-products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> {{ __('general.clear_filters') }}
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
                        <th>{{ __('products.trade_name') }}</th>
                        <th>{{ __('products.scientific_name') }}</th>
                        <th>{{ __('products.pharmaceutical_form') }}</th>
                        <th>{{ __('companies.foreign_company') }}</th>
                        <th>{{ __('companies.local_company') }}</th>
                        <th>{{ __('general.representative') }}</th>
                        <th>{{ __('general.status') }}</th>
                        <th>{{ __('general.submission_date') }}</th>
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
                                <a href="tel:{{ $product->representative->phone }}" class="text-decoration-none" onclick="event.stopPropagation();" title="{{ __('general.call') }}">
                                    <i class="fas fa-phone text-primary"></i>
                                </a>
                                @if($product->representative->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $product->representative->phone) }}" target="_blank" class="text-decoration-none" onclick="event.stopPropagation();" title="{{ __('general.whatsapp') }}">
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
                                {{ __('products.no_products') }}
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
