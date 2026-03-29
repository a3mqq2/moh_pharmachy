@extends('layouts.app')

@section('title', __('reports.products_report'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('reports.reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('products.pharmaceutical_products') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-capsules me-2"></i>{{ __('reports.products_report') }}</h5>
                @if($filtered)
                <span class="badge bg-secondary">{{ $stats['total'] }} {{ __('products.product') }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.reports.pharmaceutical-products', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> {{ __('general.print') }}
                </button>
                <a href="{{ route('admin.reports.pharmaceutical-products', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> {{ __('general.export_excel') }}
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.pharmaceutical-products') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('general.all') }}</option>
                        <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>{{ __('products.status_uploading_docs') }}</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>{{ __('products.status_pending_review') }}</option>
                        <option value="preliminary_approved" {{ request('status') == 'preliminary_approved' ? 'selected' : '' }}>{{ __('products.status_preliminary_approved') }}</option>
                        <option value="pending_final_approval" {{ request('status') == 'pending_final_approval' ? 'selected' : '' }}>{{ __('products.status_pending_final') }}</option>
                        <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>{{ __('products.status_pending_payment') }}</option>
                        <option value="payment_review" {{ request('status') == 'payment_review' ? 'selected' : '' }}>{{ __('products.status_payment_review') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('products.status_rejected') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('products.status_approved') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.from_date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.to_date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.pharmaceutical-products') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> {{ __('general.clear') }}
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
                        <th>{{ __('products.trade_name') }}</th>
                        <th>{{ __('general.registration_number') }}</th>
                        <th>{{ __('products.scientific_name') }}</th>
                        <th>{{ __('products.dosage_form') }}</th>
                        <th>{{ __('products.concentration_short') }}</th>
                        <th>{{ __('products.foreign_company') }}</th>
                        <th>{{ __('companies.representative') }}</th>
                        <th>{{ __('general.status') }}</th>
                        <th>{{ __('general.registration_date') }}</th>
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
                                {{ __('general.no_results') }}
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="9" class="text-end">{{ __('reports.total_products') }}:</th>
                        <th>{{ $stats['total'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_approved') }}:</th>
                        <th class="text-success">{{ $stats['active'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_pending_review') }}:</th>
                        <th class="text-warning">{{ $stats['pending_review'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_preliminary_approved') }}:</th>
                        <th class="text-primary">{{ $stats['preliminary_approved'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_pending_final') }}:</th>
                        <th class="text-info">{{ $stats['pending_final_approval'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_pending_payment') }}:</th>
                        <th class="text-warning">{{ $stats['pending_payment'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_payment_review') }}:</th>
                        <th class="text-info">{{ $stats['payment_review'] }}</th>
                    </tr>
                    <tr>
                        <th colspan="9" class="text-end">{{ __('products.status_rejected') }}:</th>
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
            <h5>{{ __('general.use_filters_above') }}</h5>
            <p>{{ __('general.select_status_or_date') }}</p>
        </div>
    </div>
    @endif
</div>

@endsection
