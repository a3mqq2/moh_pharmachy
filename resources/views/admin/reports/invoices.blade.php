@extends('layouts.app')

@section('title', __('reports.invoices_report'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('reports.reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('invoices.invoices') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>{{ __('reports.invoices_report') }}</h5>
                @if($filtered)
                <span class="badge bg-secondary">{{ $stats['total_invoices'] }} {{ __('invoices.invoice') }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.reports.invoices', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> {{ __('general.print') }}
                </button>
                <a href="{{ route('admin.reports.invoices', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> {{ __('general.export_excel') }}
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.invoices') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('invoices.invoice_type') }}</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>{{ __('general.all') }}</option>
                        <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>{{ __('invoices.local_companies') }}</option>
                        <option value="pharmaceutical" {{ request('type') == 'pharmaceutical' ? 'selected' : '' }}>{{ __('invoices.pharmaceutical_products') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('general.all') }}</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('invoices.status_paid') }}</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ __('invoices.status_unpaid') }}</option>
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
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.invoices') }}" class="btn btn-outline-secondary">
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
        @if($type == 'all' || $type == 'local')
        <div class="border-bottom">
            <div class="px-3 py-2 bg-light border-bottom">
                <strong><i class="fas fa-building me-1"></i> {{ __('invoices.local_invoices') }}</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('invoices.invoice_number') }}</th>
                            <th>{{ __('companies.company_name') }}</th>
                            <th>{{ __('general.amount') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th>{{ __('general.issue_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localInvoices as $invoice)
                        <tr>
                            <td><span class="badge bg-dark">{{ method_exists($localInvoices, 'currentPage') ? ($localInvoices->currentPage() - 1) * $localInvoices->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->localCompany->company_name }}</td>
                            <td><strong class="text-primary">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">{{ __('invoices.no_invoices_yet') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">{{ __('general.total_label') }}:</th>
                            <th>{{ $stats['local_total'] }}</th>
                            <th>{{ __('invoices.status_paid') }}: {{ $stats['local_paid'] }}</th>
                            <th>{{ number_format($stats['local_revenue'], 2) }} {{ __('general.currency') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if(method_exists($localInvoices, 'hasPages') && $localInvoices->hasPages())
            <div class="d-flex justify-content-center py-2">
                {{ $localInvoices->links() }}
            </div>
            @endif
        </div>
        @endif

        @if($type == 'all' || $type == 'pharmaceutical')
        <div>
            <div class="px-3 py-2 bg-light border-bottom">
                <strong><i class="fas fa-capsules me-1"></i> {{ __('invoices.pharma_invoices') }}</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('invoices.invoice_number') }}</th>
                            <th>{{ __('invoices.pharmaceutical_product') }}</th>
                            <th>{{ __('general.amount') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th>{{ __('general.issue_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pharmaInvoices as $invoice)
                        <tr>
                            <td><span class="badge bg-dark">{{ method_exists($pharmaInvoices, 'currentPage') ? ($pharmaInvoices->currentPage() - 1) * $pharmaInvoices->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->pharmaceuticalProduct->product_name }}</td>
                            <td><strong class="text-success">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">{{ __('invoices.no_invoices_yet') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">{{ __('general.total_label') }}:</th>
                            <th>{{ $stats['pharma_total'] }}</th>
                            <th>{{ __('invoices.status_paid') }}: {{ $stats['pharma_paid'] }}</th>
                            <th>{{ number_format($stats['pharma_revenue'], 2) }} {{ __('general.currency') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if(method_exists($pharmaInvoices, 'hasPages') && $pharmaInvoices->hasPages())
            <div class="d-flex justify-content-center py-2">
                {{ $pharmaInvoices->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
    @if($type == 'all')
    <div class="card-footer">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                    <span><strong>{{ __('invoices.total_invoices') }}:</strong></span>
                    <span class="badge bg-info fs-6">{{ $stats['total_invoices'] }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                    <span><strong>{{ __('invoices.total_revenue') }}:</strong></span>
                    <span class="badge bg-success fs-6">{{ number_format($stats['total_revenue'], 2) }} {{ __('general.currency') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="card-body text-center py-5">
        <div class="text-muted">
            <i class="fas fa-filter fs-1 d-block mb-3"></i>
            <h5>{{ __('general.use_filters_above') }}</h5>
            <p>{{ __('general.select_type_status_date') }}</p>
        </div>
    </div>
    @endif
</div>

@endsection
