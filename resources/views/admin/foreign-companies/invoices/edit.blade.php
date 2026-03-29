@extends('layouts.app')

@section('title', __('invoices.edit_invoice') . ' - ' . $invoice->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.index') }}">{{ __('invoices.foreign_invoices') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a></li>
    <li class="breadcrumb-item active">{{ __('general.edit') }}</li>
@endsection

@section('content')


<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ti ti-edit me-2"></i>
                    {{ __('invoices.edit_invoice') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>{{ __('general.note') }}</strong> {{ __('invoices.edit_note') }}
                </div>

                <form action="{{ route('admin.foreign-company-invoices.update', $invoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="ti ti-building me-2"></i>
                                {{ __('companies.company_info') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="text-muted small">{{ __('companies.company_name') }}</label>
                                        <div class="fw-bold">{{ $invoice->foreignCompany->localCompany->name_ar }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="text-muted small">{{ __('invoices.invoice_number') }}</label>
                                        <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    {{ __('general.amount') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount', $invoice->amount) }}"
                                           min="0"
                                           step="0.01"
                                           required>
                                    <span class="input-group-text">{{ __('general.currency') }}</span>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">{{ __('invoices.amount_in_lyd') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('invoices.invoice_status') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ __('invoices.status_pending') }}"
                                       readonly
                                       disabled>
                                <small class="text-muted">{{ __('invoices.cannot_change_status') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('invoices.description_optional') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="{{ __('invoices.description_placeholder') }}">{{ old('description', $invoice->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('invoices.max_chars', ['count' => 500]) }}</small>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}"
                           class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>
                            {{ __('general.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ __('general.save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="ti ti-clock me-2"></i>
                    {{ __('invoices.invoice_info') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">{{ __('general.issue_date') }}</label>
                            <div>{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">{{ __('invoices.issued_by') }}</label>
                            <div>{{ $invoice->issuedBy->name ?? __('invoices.unknown') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">{{ __('general.updated_at') }}</label>
                            <div>{{ $invoice->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
