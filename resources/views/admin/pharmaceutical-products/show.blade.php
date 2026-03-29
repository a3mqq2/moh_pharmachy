@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('products.product_details') . ': ' . $product->product_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pharmaceutical-products.index') }}">{{ __('products.pharmaceutical_products') }}</a></li>
    <li class="breadcrumb-item active">{{ $product->product_name }}</li>
@endsection

@section('content')
<div class="show-header mt-3 mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-2"><i class="ti ti-pill me-2 text-primary"></i>{{ $product->product_name }}</h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span>
                <span class="badge bg-info">{{ $product->pharmaceutical_form }}</span>
                @if($product->concentration)
                    <span class="badge bg-dark">{{ $product->concentration }}</span>
                @endif
                @if($product->registration_number)
                    <span class="badge bg-dark">{{ $product->registration_number }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($product->status == 'active')
                <a href="{{ route('admin.pharmaceutical-products.certificate', $product) }}" target="_blank" class="btn btn-primary">
                    <i class="ti ti-certificate me-1"></i>{{ __('companies.print_cert') }}
                </a>
            @endif
            @if($product->status == 'pending_review')
                <form action="{{ route('admin.pharmaceutical-products.approve', $product) }}" method="POST" class="d-inline preliminary-approve-form">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i>{{ __('products.preliminary_approval') }}</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>{{ __('companies.reject') }}
                </button>
            @elseif($product->status == 'pending_final_approval')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finalApproveModal">
                    <i class="ti ti-check-circle me-1"></i>{{ __('products.final_approval') }}
                </button>
            @elseif($product->status == 'rejected')
                <form action="{{ route('admin.pharmaceutical-products.approve', $product) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.return_review') }}</button>
                </form>
            @endif
            <a href="{{ route('admin.pharmaceutical-products.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-right me-1"></i>{{ __('general.back') }}</a>
        </div>
    </div>
</div>


@if($product->status == 'rejected' && $product->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>{{ __('companies.rejection_reason') }}:</strong> {{ $product->rejection_reason }}
</div>
@endif

<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="productTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basic"><i class="ti ti-info-circle me-1"></i>{{ __('products.basic_info') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-detailed">
                    <i class="ti ti-list-details me-1"></i>{{ __('products.detailed_data') }}
                    @if($product->hasCompleteDetailedInfo())
                        <span class="badge bg-success rounded-pill ms-1"><i class="ti ti-check"></i></span>
                    @else
                        <span class="badge bg-warning rounded-pill ms-1"><i class="ti ti-dots"></i></span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>{{ __('documents.documents') }}
                    <span class="badge bg-info rounded-pill ms-1">{{ $product->documents()->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoice">
                    <i class="ti ti-file-invoice me-1"></i>{{ __('products.invoice') }}
                    @if($product->hasUnpaidInvoice())
                        <span class="badge bg-danger rounded-pill ms-1">1</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-companies"><i class="ti ti-building me-1"></i>{{ __('products.company_info') }}</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-basic">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-pill me-2"></i>{{ __('products.product_data') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.trade_name') }}</th><td>{{ $product->product_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.scientific_name') }}</th><td>{{ $product->scientific_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.dosage_form') }}</th><td>{{ $product->pharmaceutical_form }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.concentration_short') }}</th><td>{{ $product->concentration }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.usage_method') }}</th><td>{{ $product->usage_methods_text }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clipboard me-2"></i>{{ __('products.registration_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr>
                                <th class="bg-light" width="40%">{{ __('general.registration_number') }}</th>
                                <td>
                                    @if($product->registration_number)
                                        <span class="fw-bold text-primary fs-6">{{ $product->registration_number }}</span>
                                    @else
                                        <span class="text-muted">{{ __('general.not_issued_yet') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th class="bg-light" width="40%">{{ __('products.submission_date') }}</th><td>{{ $product->created_at->format('Y-m-d') }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.status') }}</th><td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td></tr>
                            @if($product->reviewed_by)
                            <tr><th class="bg-light">{{ __('products.reviewed_by') }}</th><td>{{ $product->reviewedBy->name ?? __('general.not_specified') }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.review_date') }}</th><td>{{ $product->reviewed_at ? $product->reviewed_at->format('Y-m-d h:i A') : __('general.not_specified') }}</td></tr>
                            @endif
                            @if($product->preliminary_approved_by)
                            <tr><th class="bg-light">{{ __('products.preliminary_approved_by') }}</th><td>{{ $product->preliminaryApprovedBy->name ?? __('general.not_specified') }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.preliminary_approval_date') }}</th><td>{{ $product->preliminary_approved_at ? $product->preliminary_approved_at->format('Y-m-d h:i A') : __('general.not_specified') }}</td></tr>
                            @endif
                            @if($product->final_approved_by)
                            <tr><th class="bg-light">{{ __('products.final_approved_by') }}</th><td>{{ $product->finalApprovedBy->name ?? __('general.not_specified') }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.final_approval_date') }}</th><td>{{ $product->final_approved_at ? $product->final_approved_at->format('Y-m-d h:i A') : __('general.not_specified') }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-detailed">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-list-details me-1"></i>
                    {{ __('products.detailed_data_title') }}
                </h6>

                @if($product->hasCompleteDetailedInfo())
                <div class="alert alert-success mb-4">
                    <i class="ti ti-circle-check me-2"></i>
                    {{ __('products.detailed_complete') }}
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-info-circle me-2"></i>{{ __('products.basic_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.trade_name') }}</th><td>{{ $product->trade_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.country_of_origin') }}</th><td>{{ $product->origin }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-package me-2"></i>{{ __('products.packaging_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.unit') }}</th><td>{{ $product->unit }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.packaging_type') }}</th><td>{{ $product->packaging }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.package_quantity') }}</th><td>{{ $product->quantity }}</td></tr>
                            @if($product->unit_price)
                            <tr><th class="bg-light">{{ __('products.unit_price') }}</th><td>{{ number_format($product->unit_price, 2) }} {{ __('general.currency') }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clock me-2"></i>{{ __('products.validity_storage') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.shelf_life') }}</th><td>{{ $product->shelf_life_months }} {{ __('general.month') }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.storage_conditions') }}</th><td>{{ $product->storage_conditions }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-dots me-2"></i>{{ __('products.additional_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.sale_type') }}</th><td>{{ $product->free_sale }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.samples') }}</th><td>{{ $product->samples }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.pharmacopoeia_ref') }}</th><td>{{ $product->pharmacopeal_ref }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.product_classification') }}</th><td>{{ $product->item_classification }}</td></tr>
                        </table>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    {{ __('products.detailed_incomplete') }}
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-info-circle me-2"></i>{{ __('products.basic_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.trade_name') }}</th><td>{{ $product->trade_name ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.country_of_origin') }}</th><td>{{ $product->origin ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-package me-2"></i>{{ __('products.packaging_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.unit') }}</th><td>{{ $product->unit ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.packaging_type') }}</th><td>{{ $product->packaging ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.package_quantity') }}</th><td>{{ $product->quantity ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.unit_price') }}</th><td>{{ $product->unit_price ? number_format($product->unit_price, 2) . ' ' . __('general.currency') : '-' }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clock me-2"></i>{{ __('products.validity_storage') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.shelf_life') }}</th><td>{{ $product->shelf_life_months ? $product->shelf_life_months . ' ' . __('general.month') : '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.storage_conditions') }}</th><td>{{ $product->storage_conditions ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-dots me-2"></i>{{ __('products.additional_info') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('products.sale_type') }}</th><td>{{ $product->free_sale ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.samples') }}</th><td>{{ $product->samples ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.pharmacopoeia_ref') }}</th><td>{{ $product->pharmacopeal_ref ?: '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('products.product_classification') }}</th><td>{{ $product->item_classification ?: '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-documents">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-folder me-1"></i>
                    {{ __('documents.documents') }}
                </h6>

                @if($product->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">{{ __('documents.document_type') }}</th>
                                <th width="20%">{{ __('documents.file_name') }}</th>
                                <th width="10%">{{ __('documents.file_size') }}</th>
                                <th width="15%">{{ __('general.date') }}</th>
                                <th width="20%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->documents as $index => $document)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $document->document_type_name }}</strong>
                                        @if($document->notes)
                                            <br><small class="text-muted">{{ Str::limit($document->notes, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $document->original_name }}">
                                        {{ Str::limit($document->original_name, 20) }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $document->file_size_formatted }}</small>
                                </td>
                                <td>
                                    <small>{{ $document->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info btn-doc-preview" title="{{ __('general.view') }}"
                                            data-file-url="{{ $document->file_url }}"
                                            data-file-name="{{ $document->original_name ?? $document->document_type_name }}"
                                            data-download-url="{{ $document->file_url }}">
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
                <div class="text-center py-5">
                    <i class="ti ti-folder-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">{{ __('documents.no_documents') }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoice">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-file-invoice me-1"></i>
                    {{ __('products.invoice') }}
                    @if($product->hasUnpaidInvoice())
                        <span class="badge bg-danger ms-2">{{ __('invoices.status_unpaid') }}: {{ number_format($product->getUnpaidInvoice()->amount, 2) }} {{ __('general.currency') }}</span>
                    @endif
                </h6>

                @if($product->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="15%">{{ __('invoices.invoice_number') }}</th>
                                <th width="15%">{{ __('invoices.invoice_amount') }}</th>
                                <th width="15%">{{ __('general.status') }}</th>
                                <th width="15%">{{ __('general.date') }}</th>
                                <th width="20%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> {{ __('general.currency') }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_name }}</span>
                                    @if($invoice->paid_at)
                                        <br><small class="text-muted">{{ $invoice->paid_at->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $invoice->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $invoice->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($invoice->receipt_path && $invoice->status == 'pending_review')
                                            <button type="button" class="btn btn-outline-info btn-sm btn-doc-preview"
                                                data-file-url="{{ $invoice->receipt_url }}"
                                                data-file-name="{{ __('invoices.payment_receipt') }} - {{ $invoice->invoice_number }}"
                                                data-download-url="{{ $invoice->receipt_url }}">
                                                <i class="ti ti-eye me-1"></i>{{ __('invoices.view_receipt') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-product-id="{{ $product->id }}">
                                                <i class="ti ti-check me-1"></i>{{ __('invoices.approve_receipt') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                                <i class="ti ti-x me-1"></i>{{ __('invoices.reject_receipt') }}
                                            </button>
                                        @elseif($invoice->receipt_path && $invoice->status == 'paid')
                                            <button type="button" class="btn btn-outline-info btn-sm btn-doc-preview"
                                                data-file-url="{{ $invoice->receipt_url }}"
                                                data-file-name="{{ __('invoices.payment_receipt') }} - {{ $invoice->invoice_number }}"
                                                data-download-url="{{ $invoice->receipt_url }}">
                                                <i class="ti ti-eye me-1"></i>{{ __('invoices.view_receipt') }}
                                            </button>
                                        @elseif($invoice->status == 'unpaid')
                                            <span class="text-muted">{{ __('invoices.waiting_receipt_upload') }}</span>
                                        @endif
                                    </div>

                                    <form id="approve-receipt-form-{{ $invoice->id }}" action="{{ route('admin.pharmaceutical-products.invoices.approve-receipt', [$product, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-file-invoice fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">{{ __('invoices.no_invoices_yet') }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-companies">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-world me-2"></i>{{ __('products.foreign_company') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('companies.company_name') }}</th><td>{{ $product->foreignCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.country') }}</th><td>{{ $product->foreignCompany->country }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.email') }}</th><td>{{ $product->foreignCompany->email }}</td></tr>
                            @if($product->foreignCompany->phone)
                            <tr><th class="bg-light">{{ __('general.phone') }}</th><td dir="ltr" class="text-end">{{ $product->foreignCompany->phone }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-building-skyscraper me-2"></i>{{ __('products.local_company') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('companies.company_name') }}</th><td>{{ $product->foreignCompany->localCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.commercial_reg') }}</th><td>{{ $product->foreignCompany->localCompany->commercial_registration_number }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.email') }}</th><td>{{ $product->foreignCompany->localCompany->email }}</td></tr>
                            @if($product->foreignCompany->localCompany->phone)
                            <tr><th class="bg-light">{{ __('general.phone') }}</th><td dir="ltr" class="text-end">{{ $product->foreignCompany->localCompany->phone }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="section-title"><i class="ti ti-user me-2"></i>{{ __('companies.company_representative') }}</h6>
                    <table class="table table-striped info-table">
                        <tr>
                            <th class="bg-light" width="15%">{{ __('general.name') }}</th>
                            <td width="35%">{{ $product->representative->name }}</td>
                            <th class="bg-light" width="15%">{{ __('general.email') }}</th>
                            <td width="35%">{{ $product->representative->email }}</td>
                        </tr>
                        @if($product->representative->phone)
                        <tr>
                            <th class="bg-light">{{ __('general.phone') }}</th>
                            <td dir="ltr" class="text-end">{{ $product->representative->phone }}</td>
                            <th class="bg-light">{{ __('general.country') }}</th>
                            <td>{{ $product->representative->nationality ?? '-' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="bg-light">{{ __('general.registration_date') }}</th>
                            <td>{{ $product->representative->created_at->format('Y-m-d h:i A') }}</td>
                            <th class="bg-light">{{ __('general.status') }}</th>
                            <td>
                                @if($product->representative->email_verified_at)
                                    <span class="badge bg-success">{{ __('general.enabled') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('general.disabled') }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($product->status == 'pending_review')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pharmaceutical-products.reject', $product) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('companies.reject') }} {{ __('products.product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __('companies.rejection_reason') }} <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('general.confirm') }} {{ __('companies.reject') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($product->status == 'pending_final_approval')
<div class="modal fade" id="finalApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pharmaceutical-products.final-approve', $product) }}" method="POST" class="final-approve-form">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">{{ __('products.final_approval') }} {{ __('products.product') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('products.final_approve_msg') }}</p>
                    <hr>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_pre_registered" value="1" id="productPreRegistered">
                            <label class="form-check-label" for="productPreRegistered">{{ __('products.pre_registered_before_system') }}</label>
                        </div>
                    </div>
                    <div id="productPreRegFields" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.reg_year') }} <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_year" class="form-control" min="1990" max="{{ date('Y') }}" placeholder="{{ __('companies.reg_year_example') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.serial_number') }} <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_sequence" class="form-control" min="1" placeholder="{{ __('companies.serial_example') }}">
                            </div>
                        </div>
                        <div class="alert alert-light py-2">
                            <small>{{ __('companies.reg_number_display') }} <strong id="pharmaPreRegPreview">-</strong></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('products.confirm_final_approval') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="rejectReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectReceiptForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">{{ __('invoices.reject_receipt_modal') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>{{ __('general.warning') }}:</strong> {{ __('products.reject_receipt_warning') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="{{ __('invoices.rejection_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('general.confirm') }} {{ __('invoices.reject_receipt') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tabKey = 'pharmaceuticalProductTab_{{ $product->id }}';
const savedTab = sessionStorage.getItem(tabKey);
if (savedTab) {
    const tabButton = document.querySelector('[data-bs-target="' + savedTab + '"]');
    if (tabButton) {
        const tab = new bootstrap.Tab(tabButton);
        tab.show();
    }
}

document.querySelectorAll('#productTabs button[data-bs-toggle="tab"]').forEach(function(tabButton) {
    tabButton.addEventListener('shown.bs.tab', function(e) {
        sessionStorage.setItem(tabKey, e.target.getAttribute('data-bs-target'));
    });
});

document.querySelector('.preliminary-approve-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '{{ __("products.confirm_preliminary_approval") }}',
        text: '{{ __("products.preliminary_approve_msg") }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("products.yes_preliminary_approve") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
});

document.getElementById('productPreRegistered')?.addEventListener('change', function() {
    document.getElementById('productPreRegFields').style.display = this.checked ? '' : 'none';
});

function updatePharmaPreRegPreview() {
    const year = document.querySelector('#finalApproveModal input[name="pre_registration_year"]')?.value;
    const seq = document.querySelector('#finalApproveModal input[name="pre_registration_sequence"]')?.value;
    const preview = document.getElementById('pharmaPreRegPreview');
    if (preview) {
        preview.textContent = (year && seq) ? year + '-' + seq : '-';
    }
}
document.querySelector('#finalApproveModal input[name="pre_registration_year"]')?.addEventListener('input', updatePharmaPreRegPreview);
document.querySelector('#finalApproveModal input[name="pre_registration_sequence"]')?.addEventListener('input', updatePharmaPreRegPreview);

document.querySelector('.restore-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '{{ __("companies.return_review") }}',
        text: '{{ __("products.return_review_confirm") }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("general.yes") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
});

document.querySelectorAll('.btn-approve-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const productId = this.getAttribute('data-product-id');
        Swal.fire({
            title: '{{ __("products.approve_receipt_activate") }}',
            text: '{{ __("products.approve_receipt_activate_msg") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("invoices.yes_approve") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('approve-receipt-form-' + invoiceId).submit();
            }
        });
    });
});

document.querySelectorAll('.btn-reject-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const invoiceNumber = this.getAttribute('data-number');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/pharmaceutical-products/" . $product->id . "/invoices") }}/' + invoiceId + '/reject-receipt';

        new bootstrap.Modal(document.getElementById('rejectReceiptModal')).show();
    });
});
</script>
@endpush
