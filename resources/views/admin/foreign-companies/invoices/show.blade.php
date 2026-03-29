@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('invoices.invoice_details') . ' - ' . $invoice->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.index') }}">{{ __('invoices.foreign_invoices') }}</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.foreign-company-invoices.download', $invoice->id) }}"
               class="btn btn-primary"
               target="_blank">
                <i class="ti ti-printer me-1"></i>
                {{ __('invoices.print_invoice') }}
            </a>

            @if($invoice->receipt_path && $invoice->receipt_status == 'pending')
            <button type="button"
                    class="btn btn-success"
                    onclick="approveReceipt()">
                <i class="ti ti-check me-1"></i>
                {{ __('invoices.approve_receipt') }}
            </button>
            <button type="button"
                    class="btn btn-danger"
                    onclick="showRejectModal()">
                <i class="ti ti-x me-1"></i>
                {{ __('invoices.reject_receipt') }}
            </button>
            @endif

            @if($invoice->status == 'pending' && !$invoice->receipt_path)
            <a href="{{ route('admin.foreign-company-invoices.edit', $invoice->id) }}"
               class="btn btn-warning">
                <i class="ti ti-edit me-1"></i>
                {{ __('invoices.edit_invoice') }}
            </a>
            @endif

            @if($invoice->status == 'pending')
            <button type="button"
                    class="btn btn-outline-danger"
                    onclick="showCancelModal()">
                <i class="ti ti-ban me-1"></i>
                {{ __('invoices.cancel_invoice') }}
            </button>
            @endif

            <a href="{{ route('admin.foreign-company-invoices.index') }}"
               class="btn btn-outline-secondary ms-auto">
                <i class="ti ti-arrow-right me-1"></i>
                {{ __('general.back_to_list') }}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice-content" type="button" role="tab">
                    <i class="ti ti-file-invoice me-1"></i>
                    {{ __('invoices.invoice_info') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="company-tab" data-bs-toggle="tab" data-bs-target="#company-content" type="button" role="tab">
                    <i class="ti ti-building me-1"></i>
                    {{ __('companies.company_info') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="receipt-tab" data-bs-toggle="tab" data-bs-target="#receipt-content" type="button" role="tab">
                    <i class="ti ti-file-text me-1"></i>
                    {{ __('invoices.payment_receipt') }}
                    @if($invoice->receipt_status == 'pending')
                        <span class="badge bg-warning ms-1">{{ __('invoices.status_review') }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-content" type="button" role="tab">
                    <i class="ti ti-timeline me-1"></i>
                    {{ __('invoices.timeline') }}
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="invoice-content" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered info-table">
                            <tr>
                                <th class="bg-light" width="40%">{{ __('invoices.invoice_number') }}</th>
                                <td>
                                    <span class="badge bg-dark fs-6">{{ $invoice->invoice_number }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('general.amount') }}</th>
                                <td>
                                    <h4 class="text-primary mb-0">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</h4>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('general.status') }}</th>
                                <td>
                                    @if($invoice->status == 'pending')
                                        <span class="badge bg-warning">{{ __('invoices.status_pending') }}</span>
                                    @elseif($invoice->status == 'paid')
                                        <span class="badge bg-success">{{ __('invoices.status_paid') }}</span>
                                    @elseif($invoice->status == 'cancelled')
                                        <span class="badge bg-danger">{{ __('invoices.status_cancelled') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered info-table">
                            <tr>
                                <th class="bg-light" width="40%">{{ __('general.issue_date') }}</th>
                                <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('invoices.issued_by') }}</th>
                                <td>{{ $invoice->issuedBy->name ?? __('invoices.unknown') }}</td>
                            </tr>
                            @if($invoice->status == 'paid')
                            <tr>
                                <th class="bg-light">{{ __('invoices.payment_date') }}</th>
                                <td>{{ $invoice->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($invoice->description)
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('general.description') }}
                    </h6>
                    <p class="mb-0">{{ $invoice->description }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="company-content" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered info-table">
                            <tr>
                                <th class="bg-light" width="40%">{{ __('companies.company_name') }}</th>
                                <td><strong>{{ $invoice->foreignCompany->company_name }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('invoices.company_status') }}</th>
                                <td>
                                    @php
                                        $statusName = match($invoice->foreignCompany->status) {
                                            'pending' => __('companies.status_pending_review'),
                                            'approved' => __('companies.status_accepted'),
                                            'active' => __('companies.status_active'),
                                            'rejected' => __('companies.status_rejected'),
                                            'suspended' => __('invoices.status_suspended_alt'),
                                            'pending_payment' => __('invoices.status_pending_payment'),
                                            'expired' => __('companies.status_expired'),
                                            default => $invoice->foreignCompany->status,
                                        };
                                        $statusColor = match($invoice->foreignCompany->status) {
                                            'active' => 'success',
                                            'pending', 'pending_payment' => 'warning',
                                            'approved' => 'info',
                                            'rejected', 'suspended', 'expired' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ $statusName }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered info-table">
                            <tr>
                                <th class="bg-light" width="40%">{{ __('companies.company_representative') }}</th>
                                <td><strong>{{ $invoice->foreignCompany->representative->name ?? __('invoices.unknown') }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('general.email') }}</th>
                                <td>{{ $invoice->foreignCompany->representative->email ?? __('general.not_available') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('general.phone') }}</th>
                                <td>{{ $invoice->foreignCompany->representative->phone ?? __('general.not_available') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.foreign-companies.show', $invoice->foreign_company_id) }}"
                       class="btn btn-primary">
                        <i class="ti ti-external-link me-1"></i>
                        {{ __('invoices.view_company_details') }}
                    </a>
                </div>
            </div>

            <div class="tab-pane fade" id="receipt-content" role="tabpanel">
                @if($invoice->receipt_path)
                    <div class="row">
                        <div class="col-md-8">
                            @if($invoice->receipt_status == 'pending')
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="ti ti-clock me-1"></i>
                                    {{ __('invoices.receipt_pending_review') }}
                                </h6>
                                <p class="mb-0">{{ __('invoices.receipt_pending_review_msg') }}</p>
                            </div>
                            @elseif($invoice->receipt_status == 'approved')
                            <div class="alert alert-success">
                                <h6 class="alert-heading">
                                    <i class="ti ti-check me-1"></i>
                                    {{ __('invoices.receipt_approved') }}
                                </h6>
                                @if($invoice->receiptReviewedBy)
                                <p class="mb-1"><strong>{{ __('general.by') }}:</strong> {{ $invoice->receiptReviewedBy->name }}</p>
                                <p class="mb-0"><strong>{{ __('general.date') }}:</strong> {{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</p>
                                @endif
                            </div>
                            @elseif($invoice->receipt_status == 'rejected')
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="ti ti-x me-1"></i>
                                    {{ __('invoices.receipt_rejected') }}
                                </h6>
                                @if($invoice->receiptReviewedBy)
                                <p class="mb-1"><strong>{{ __('general.by') }}:</strong> {{ $invoice->receiptReviewedBy->name }}</p>
                                <p class="mb-1"><strong>{{ __('general.date') }}:</strong> {{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</p>
                                @endif
                                @if($invoice->receipt_rejection_reason)
                                <hr>
                                <p class="mb-0"><strong>{{ __('invoices.rejection_reason') }}:</strong> {{ $invoice->receipt_rejection_reason }}</p>
                                @endif
                            </div>
                            @endif

                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th class="bg-light" width="30%">{{ __('invoices.upload_date') }}</th>
                                    <td>{{ $invoice->receipt_uploaded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('invoices.receipt_status') }}</th>
                                    <td>
                                        @if($invoice->receipt_status == 'pending')
                                            <span class="badge bg-warning">{{ __('invoices.status_review') }}</span>
                                        @elseif($invoice->receipt_status == 'approved')
                                            <span class="badge bg-success">{{ __('invoices.receipt_approved_label') }}</span>
                                        @elseif($invoice->receipt_status == 'rejected')
                                            <span class="badge bg-danger">{{ __('invoices.receipt_rejected_label') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="btn btn-info btn-doc-preview" data-file-url="{{ Storage::url($invoice->receipt_path) }}" data-file-name="{{ __('invoices.upload_receipt') }}_{{ $invoice->invoice_number }}" data-download-url="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}">
                                    <i class="ti ti-eye me-1"></i>{{ __('invoices.view_receipt') }}
                                </button>
                                <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}"
                                   class="btn btn-primary">
                                    <i class="ti ti-download me-1"></i>
                                    {{ __('invoices.download_receipt') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-file-off" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">{{ __('invoices.no_receipt_uploaded') }}</h4>
                        <p class="text-muted">{{ __('invoices.waiting_receipt_upload') }}</p>
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="timeline-content" role="tabpanel">
                <div class="timeline">
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-success">
                                    <i class="ti ti-circle-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ __('invoices.invoice_issued') }}</h6>
                                <p class="text-muted mb-1">{{ __('invoices.invoice_issued_msg', ['number' => $invoice->invoice_number]) }}</p>
                                <small class="text-muted">{{ $invoice->created_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>

                    @if($invoice->receipt_uploaded_at)
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-info">
                                    <i class="ti ti-upload"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ __('invoices.receipt_uploaded') }}</h6>
                                <p class="text-muted mb-1">{{ __('invoices.receipt_uploaded_msg') }}</p>
                                <small class="text-muted">{{ $invoice->receipt_uploaded_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->receipt_reviewed_at)
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-{{ $invoice->receipt_status == 'approved' ? 'success' : 'danger' }}">
                                    <i class="ti ti-eye"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ __('invoices.receipt_review') }}</h6>
                                <p class="text-muted mb-1">
                                    {{ __('invoices.review_by_msg', ['action' => $invoice->receipt_status == 'approved' ? __('invoices.receipt_approved_action') : __('invoices.receipt_rejected_action'), 'name' => $invoice->receiptReviewedBy->name ?? __('invoices.unknown')]) }}
                                </p>
                                <small class="text-muted">{{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->status == 'paid')
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-success">
                                    <i class="ti ti-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ __('invoices.payment_done') }}</h6>
                                <p class="text-muted mb-1">{{ __('invoices.payment_done_msg') }}</p>
                                <small class="text-muted">{{ $invoice->paid_at?->format('Y-m-d H:i') ?? $invoice->updated_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->status == 'cancelled')
                    <div class="timeline-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-danger">
                                    <i class="ti ti-ban"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ __('invoices.invoice_cancelled') }}</h6>
                                <p class="text-muted mb-1">{{ __('invoices.invoice_cancelled_msg') }}</p>
                                <small class="text-muted">{{ $invoice->updated_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.invoices.reject-receipt', [$invoice->foreign_company_id, $invoice->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('invoices.reject_receipt_modal') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="{{ __('invoices.rejection_reason_placeholder') }}"></textarea>
                        <small class="text-muted">{{ __('invoices.min_chars', ['count' => 10]) }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>
                        {{ __('invoices.reject_receipt') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-company-invoices.cancel', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('invoices.cancel_invoice') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        {{ __('invoices.cancel_confirm_msg') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.cancellation_reason') }}</label>
                        <textarea name="cancellation_reason"
                                  class="form-control"
                                  rows="3"
                                  placeholder="{{ __('invoices.cancellation_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-ban me-1"></i>
                        {{ __('invoices.cancel_invoice') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function approveReceipt() {
        Swal.fire({
            title: '{{ __('invoices.approve_receipt_title') }}',
            text: '{{ __('invoices.approve_receipt_msg') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __('invoices.yes_approve') }}',
            cancelButtonText: '{{ __('general.cancel') }}',
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.foreign-companies.invoices.approve-receipt', [$invoice->foreign_company_id, $invoice->id]) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function showRejectModal() {
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    function showCancelModal() {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
</script>
@endpush
