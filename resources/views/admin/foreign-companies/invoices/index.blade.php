@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('invoices.foreign_invoices'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('invoices.foreign_invoices') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>{{ __('invoices.foreign_invoices') }}</h5>
                <span class="badge bg-secondary">{{ $invoices->total() }} {{ __('invoices.invoice') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filter') }}
                </button>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'receipt_status', 'sort_by', 'sort_order']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('invoices.search_by_invoice_number') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('invoices.invoice_number_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('invoices.invoice_status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('invoices.status_pending') }}</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('invoices.status_paid') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('invoices.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('invoices.receipt_status') }}</label>
                        <select name="receipt_status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="pending" {{ request('receipt_status') == 'pending' ? 'selected' : '' }}>{{ __('invoices.status_review') }}</option>
                            <option value="approved" {{ request('receipt_status') == 'approved' ? 'selected' : '' }}>{{ __('invoices.receipt_approved_label') }}</option>
                            <option value="rejected" {{ request('receipt_status') == 'rejected' ? 'selected' : '' }}>{{ __('invoices.receipt_rejected_label') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('invoices.sort_by') }}</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ __('general.created_at') }}</option>
                            <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>{{ __('general.amount') }}</option>
                            <option value="invoice_number" {{ request('sort_by') == 'invoice_number' ? 'selected' : '' }}>{{ __('invoices.invoice_number') }}</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">{{ __('invoices.sort_direction') }}</label>
                        <select name="sort_order" class="form-select">
                            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>{{ __('invoices.descending') }}</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ __('invoices.ascending') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> {{ __('general.apply') }}
                        </button>
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
                        <th>{{ __('companies.company') }}</th>
                        <th>{{ __('general.amount') }}</th>
                        <th>{{ __('invoices.invoice_status') }}</th>
                        <th>{{ __('invoices.receipt_status') }}</th>
                        <th>{{ __('general.created_at') }}</th>
                        <th>{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $invoice->foreignCompany->localCompany->name_ar }}</strong>
                                @if($invoice->foreignCompany->localCompany->name_en)
                                <br><small class="text-muted">{{ $invoice->foreignCompany->localCompany->name_en }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong class="text-primary">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong>
                        </td>
                        <td>
                            @if($invoice->status == 'pending')
                                <span class="badge bg-warning">{{ __('invoices.status_pending') }}</span>
                            @elseif($invoice->status == 'paid')
                                <span class="badge bg-success">{{ __('invoices.status_paid') }}</span>
                            @elseif($invoice->status == 'cancelled')
                                <span class="badge bg-danger">{{ __('invoices.status_cancelled') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($invoice->receipt_path)
                                @if($invoice->receipt_status == 'pending')
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock"></i> {{ __('invoices.status_review') }}
                                    </span>
                                @elseif($invoice->receipt_status == 'approved')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> {{ __('invoices.receipt_approved_label') }}
                                    </span>
                                @elseif($invoice->receipt_status == 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> {{ __('invoices.receipt_rejected_label') }}
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-file"></i> {{ __('invoices.no_receipt') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $invoice->created_at->format('Y-m-d') }}</div>
                            <small class="text-muted">{{ $invoice->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('general.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($invoice->status == 'pending' && !$invoice->receipt_path)
                                <a href="{{ route('admin.foreign-company-invoices.edit', $invoice->id) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="{{ __('general.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if($invoice->receipt_path)
                                <button type="button" class="btn btn-sm btn-outline-primary btn-doc-preview" data-file-url="{{ Storage::url($invoice->receipt_path) }}" data-file-name="{{ __('invoices.upload_receipt') }}_{{ $invoice->invoice_number }}" data-download-url="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}" title="{{ __('invoices.view_receipt') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="{{ __('invoices.download_receipt') }}">
                                    <i class="fas fa-download"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-file-invoice" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">{{ __('invoices.no_invoices_yet') }}</p>
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
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '{{ __('general.success') }}',
            text: '{{ session('success') }}',
            confirmButtonText: '{{ __('general.ok') }}',
            confirmButtonColor: '#1a5f4a'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '{{ __('general.error') }}',
            text: '{{ session('error') }}',
            confirmButtonText: '{{ __('general.ok') }}',
            confirmButtonColor: '#dc3545'
        });
    @endif
</script>
@endpush
