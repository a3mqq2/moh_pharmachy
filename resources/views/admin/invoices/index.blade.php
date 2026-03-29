@extends('layouts.app')

@section('title', __('invoices.invoices'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('invoices.invoices') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>{{ __('invoices.invoices') }}</h5>
                <span class="badge bg-secondary">{{ $invoices->total() }} {{ __('invoices.invoice') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filters') }}
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.invoices.index', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> {{ __('general.print') }}
                </button>
                <a href="{{ route('admin.invoices.index', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> {{ __('general.export_excel') }}
                </a>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap mt-2">
            <div class="badge bg-light text-dark">
                <i class="fas fa-file-invoice me-1"></i>
                {{ __('invoices.total') }}: {{ $stats['total'] }}
            </div>
            <div class="badge bg-info">
                <i class="fas fa-building me-1"></i>
                {{ __('invoices.local') }}: {{ $stats['local_total'] }}
            </div>
            <div class="badge bg-success">
                <i class="fas fa-globe-americas me-1"></i>
                {{ __('invoices.foreign') }}: {{ $stats['foreign_total'] }}
            </div>
            <div class="badge bg-purple" style="background-color: #6f42c1;">
                <i class="fas fa-capsules me-1"></i>
                {{ __('invoices.pharmaceutical') }}: {{ $stats['pharmaceutical_total'] }}
            </div>
            <div class="badge bg-warning">
                <i class="fas fa-clock me-1"></i>
                {{ __('invoices.status_pending') }}: {{ $stats['pending'] }}
            </div>
            <div class="badge bg-primary">
                <i class="fas fa-check me-1"></i>
                {{ __('invoices.status_paid') }}: {{ $stats['paid'] }}
            </div>
            <div class="badge bg-success">
                <i class="fas fa-coins me-1"></i>
                {{ __('invoices.revenue') }}: {{ number_format($stats['total_revenue'], 2) }} {{ __('general.currency') }}
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'type', 'sort_by', 'sort_order', 'from_date', 'to_date']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('invoices.search_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('invoices.invoice_type') }}</label>
                        <select name="type" class="form-select">
                            <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>{{ __('general.all') }}</option>
                            <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>{{ __('invoices.local_companies') }}</option>
                            <option value="foreign" {{ request('type') == 'foreign' ? 'selected' : '' }}>{{ __('invoices.foreign_companies') }}</option>
                            <option value="pharmaceutical" {{ request('type') == 'pharmaceutical' ? 'selected' : '' }}>{{ __('products.pharmaceutical_products') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ __('invoices.status_unpaid') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('invoices.status_pending') }}</option>
                            <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>{{ __('invoices.status_pending_review') }}</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('invoices.status_paid') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('invoices.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.from_date') }}</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.to_date') }}</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                            </button>
                            @if(request()->hasAny(['search', 'status', 'type', 'sort_by', 'sort_order', 'from_date', 'to_date']))
                                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
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
                        <th>{{ __('invoices.invoice_number') }}</th>
                        <th>{{ __('invoices.invoice_type') }}</th>
                        <th>{{ __('companies.company_name') }}</th>
                        <th>{{ __('general.description') }}</th>
                        <th>{{ __('invoices.amount') }}</th>
                        <th>{{ __('general.status') }}</th>
                        <th>{{ __('general.created_at') }}</th>
                        <th>{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <span class="badge bg-dark">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                <span class="badge bg-info">
                                    <i class="fas fa-building me-1"></i>{{ __('invoices.local_company') }}
                                </span>
                            @elseif($invoice->company_type == 'foreign')
                                <span class="badge bg-success">
                                    <i class="fas fa-globe-americas me-1"></i>{{ __('invoices.foreign_company') }}
                                </span>
                            @else
                                <span class="badge" style="background-color: #6f42c1;">
                                    <i class="fas fa-capsules me-1"></i>{{ __('products.pharmaceutical_product') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $invoice->company?->company_name ?? __('general.not_available') }}</strong>
                        </td>
                        <td>
                            <small>{{ $invoice->description ?? '-' }}</small>
                        </td>
                        <td>
                            <strong class="text-primary">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">{{ __('invoices.status_paid') }}</span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge bg-warning">{{ __('invoices.status_unpaid') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @elseif($invoice->company_type == 'foreign')
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">{{ __('invoices.status_paid') }}</span>
                                @elseif($invoice->status == 'pending')
                                    <span class="badge bg-warning">{{ __('invoices.status_pending') }}</span>
                                @elseif($invoice->status == 'cancelled')
                                    <span class="badge bg-danger">{{ __('invoices.status_cancelled') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @else
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">{{ __('invoices.status_paid') }}</span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge bg-warning">{{ __('invoices.status_unpaid') }}</span>
                                @elseif($invoice->status == 'pending_review')
                                    <span class="badge bg-info">{{ __('invoices.status_pending_review') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <small>{{ $invoice->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $invoice->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                <a href="{{ route('admin.local-companies.show', $invoice->company) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('general.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @elseif($invoice->company_type == 'foreign')
                                <a href="{{ route('admin.foreign-company-invoices.show', $invoice) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('general.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @else
                                <a href="{{ route('admin.pharmaceutical-products.show', $invoice->pharmaceuticalProduct) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('general.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice fs-1 d-block mb-2"></i>
                                {{ __('invoices.no_invoices') }}
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
