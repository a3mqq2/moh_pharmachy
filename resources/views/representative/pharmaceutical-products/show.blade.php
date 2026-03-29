@extends('layouts.auth')

@section('title', __('products.product_details'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.pharmaceutical-products.index') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ $pharmaceuticalProduct->product_name }}</h1>
                <p>{{ __('products.product_details') }}</p>
            </div>
        </div>
        <div class="header-actions">
            <span class="badge {{ $pharmaceuticalProduct->status_badge_class }}">
                {{ $pharmaceuticalProduct->status_name }}
            </span>
            @if($pharmaceuticalProduct->registration_number)
                <span class="badge" style="background: #1f2937; color: #fff;">
                    {{ $pharmaceuticalProduct->registration_number }}
                </span>
            @endif
        </div>
    </div>

    @if($pharmaceuticalProduct->status == 'preliminary_approved')
    <div class="alert alert-success mb-4">
        <i class="ti ti-circle-check me-2"></i>
        <strong>{{ __('products.preliminary_approved_msg') }}</strong>
        <p class="mb-0 mt-2">{{ __('products.complete_detailed_info') }}</p>
    </div>

    <div class="mb-4">
        <a href="{{ route('representative.pharmaceutical-products.edit-details', $pharmaceuticalProduct) }}" class="btn btn-primary btn-lg w-100">
            <i class="ti ti-edit me-1"></i>
            {{ __('products.complete_detailed_data') }}
        </a>
    </div>
    @endif

    @if($pharmaceuticalProduct->status == 'pending_final_approval')
    <div class="alert alert-info mb-4">
        <i class="ti ti-clock me-2"></i>
        <strong>{{ __('products.awaiting_final_approval') }}</strong>
        <p class="mb-0 mt-2">{{ __('products.awaiting_final_approval_desc') }}</p>
    </div>
    @endif

    @if($pharmaceuticalProduct->status == 'preliminary_approved' && $pharmaceuticalProduct->hasCompleteDetailedInfo())
    <div class="alert alert-gradient-success mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="alert-icon-wrapper">
                <i class="ti ti-circle-check-filled"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2" style="font-weight: 700; font-size: 1.1rem;">
                    <i class="ti ti-sparkles me-1"></i>
                    {{ __('products.all_data_ready') }}
                </h5>
                <p class="mb-2" style="font-size: 0.95rem;">
                    {{ __('products.all_data_ready_desc') }}
                </p>
                <div class="alert-steps mt-3">
                    <div class="step-item completed">
                        <i class="ti ti-check"></i>
                        <span>{{ __('products.upload_documents_step') }}</span>
                    </div>
                    <div class="step-item completed">
                        <i class="ti ti-check"></i>
                        <span>{{ __('products.preliminary_approval_step') }}</span>
                    </div>
                    <div class="step-item completed">
                        <i class="ti ti-check"></i>
                        <span>{{ __('products.detailed_data_step') }}</span>
                    </div>
                    <div class="step-item next">
                        <i class="ti ti-arrow-left"></i>
                        <span>{{ __('products.final_review_step') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <form action="{{ route('representative.pharmaceutical-products.submit-details', $pharmaceuticalProduct) }}" method="POST" style="display: inline-block;" class="submit-details-form-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">
                <i class="ti ti-send"></i>
                {{ __('products.submit_final_review') }}
            </button>
        </form>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab"
                            data-bs-target="#basic-info" type="button" role="tab">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('products.basic_info') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="detailed-info-tab" data-bs-toggle="tab"
                            data-bs-target="#detailed-info" type="button" role="tab">
                        <i class="ti ti-list-details me-1"></i>
                        {{ __('products.detailed_data') }}
                        @if($pharmaceuticalProduct->hasCompleteDetailedInfo())
                            <i class="ti ti-circle-check ms-1" style="color: #10b981;"></i>
                        @else
                            <i class="ti ti-alert-circle ms-1" style="color: #f59e0b;"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab"
                            data-bs-target="#documents" type="button" role="tab">
                        <i class="ti ti-file-text me-1"></i>
                        {{ __('documents.documents') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="invoice-tab" data-bs-toggle="tab"
                            data-bs-target="#invoice" type="button" role="tab">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('products.invoice') }}
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                    <h4 class="section-title">{{ __('products.product_data') }}</h4>
                    <table class="table table-bordered">
                        @if($pharmaceuticalProduct->registration_number)
                        <tr>
                            <th class="bg-light" width="30%">{{ __('general.registration_number') }}</th>
                            <td><strong>{{ $pharmaceuticalProduct->registration_number }}</strong></td>
                        </tr>
                        @endif
                        <tr>
                            <th class="bg-light" width="30%">{{ __('products.trade_name') }}</th>
                            <td><strong>{{ $pharmaceuticalProduct->product_name }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('products.scientific_name') }}</th>
                            <td>{{ $pharmaceuticalProduct->scientific_name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('products.dosage_form') }}</th>
                            <td>{{ $pharmaceuticalProduct->pharmaceutical_form }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('products.concentration') }}</th>
                            <td>{{ $pharmaceuticalProduct->concentration }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('products.usage_method') }}</th>
                            <td>{{ $pharmaceuticalProduct->usage_methods_text }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.registration_date') }}</th>
                            <td>{{ $pharmaceuticalProduct->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.status') }}</th>
                            <td>
                                <span class="badge {{ $pharmaceuticalProduct->status_badge_class }}">
                                    {{ $pharmaceuticalProduct->status_name }}
                                </span>
                            </td>
                        </tr>
                        @if($pharmaceuticalProduct->status == 'rejected' && $pharmaceuticalProduct->rejection_reason)
                        <tr>
                            <th class="bg-light">{{ __('companies.rejection_reason') }}</th>
                            <td class="text-danger"><strong>{{ $pharmaceuticalProduct->rejection_reason }}</strong></td>
                        </tr>
                        @endif
                        @if($pharmaceuticalProduct->reviewed_at)
                        <tr>
                            <th class="bg-light">{{ __('products.review_date') }}</th>
                            <td>{{ $pharmaceuticalProduct->reviewed_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                        @if($pharmaceuticalProduct->reviewedBy)
                        <tr>
                            <th class="bg-light">{{ __('products.reviewed_request') }}</th>
                            <td>{{ $pharmaceuticalProduct->reviewedBy->name }}</td>
                        </tr>
                        @endif
                        @if($pharmaceuticalProduct->preliminary_approved_at)
                        <tr>
                            <th class="bg-light">{{ __('products.preliminary_approval_date') }}</th>
                            <td>{{ $pharmaceuticalProduct->preliminary_approved_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                        @if($pharmaceuticalProduct->final_approved_at)
                        <tr>
                            <th class="bg-light">{{ __('products.final_approval_date') }}</th>
                            <td>{{ $pharmaceuticalProduct->final_approved_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    <h4 class="section-title mt-4">{{ __('products.foreign_company_info') }}</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" width="30%">{{ __('companies.company_name') }}</th>
                            <td><strong>{{ $pharmaceuticalProduct->foreignCompany->company_name }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.country') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->country }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('companies.entity_type') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->entity_type_name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('companies.activity_type') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->activity_type_name }}</td>
                        </tr>
                    </table>

                    <h4 class="section-title mt-4">{{ __('products.local_company_agent_info') }}</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" width="30%">{{ __('products.local_company_name_label') }}</th>
                            <td><strong>{{ $pharmaceuticalProduct->foreignCompany->localCompany->company_name }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.address') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->localCompany->commercial_address }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.email') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->localCompany->company_email }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.phone') }}</th>
                            <td>{{ $pharmaceuticalProduct->foreignCompany->localCompany->phone }}</td>
                        </tr>
                    </table>

                    @if(in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected']))
                    <div class="form-actions mt-4">
                        <a href="{{ route('representative.pharmaceutical-products.edit', $pharmaceuticalProduct) }}" class="btn btn-primary">
                            <i class="ti ti-edit"></i>
                            {{ __('companies.edit_data') }}
                        </a>
                    </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="detailed-info" role="tabpanel">
                    <h4 class="section-title">{{ __('products.detailed_data_title') }}</h4>

                    @if($pharmaceuticalProduct->hasCompleteDetailedInfo())
                    <div class="alert alert-success mb-4">
                        <i class="ti ti-circle-check me-2"></i>
                        {{ __('products.detailed_complete') }}
                    </div>

                    <div class="mb-4">
                        <h4 class="section-title">{{ __('products.basic_info') }}</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="30%">{{ __('products.trade_name') }}</th>
                                <td><strong>{{ $pharmaceuticalProduct->trade_name }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.country_of_origin') }}</th>
                                <td>{{ $pharmaceuticalProduct->origin }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="mb-4">
                        <h4 class="section-title">{{ __('products.packaging_info') }}</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="30%">{{ __('products.unit') }}</th>
                                <td>{{ $pharmaceuticalProduct->unit }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.packaging_type') }}</th>
                                <td>{{ $pharmaceuticalProduct->packaging }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.package_quantity') }}</th>
                                <td>{{ $pharmaceuticalProduct->quantity }}</td>
                            </tr>
                            @if($pharmaceuticalProduct->unit_price)
                            <tr>
                                <th class="bg-light">{{ __('products.unit_price') }}</th>
                                <td><strong>{{ number_format($pharmaceuticalProduct->unit_price, 2) }} {{ __('general.currency') }}</strong></td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <div class="mb-4">
                        <h4 class="section-title">{{ __('products.validity_storage') }}</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="30%">{{ __('products.shelf_life') }}</th>
                                <td>{{ $pharmaceuticalProduct->shelf_life_months }} {{ __('general.month') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.storage_conditions') }}</th>
                                <td>{{ $pharmaceuticalProduct->storage_conditions }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="mb-4">
                        <h4 class="section-title">{{ __('products.additional_info') }}</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="30%">{{ __('products.sale_type') }}</th>
                                <td>{{ $pharmaceuticalProduct->free_sale }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.samples') }}</th>
                                <td>{{ $pharmaceuticalProduct->samples }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.pharmacopoeia_ref') }}</th>
                                <td>{{ $pharmaceuticalProduct->pharmacopeal_ref }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">{{ __('products.product_classification') }}</th>
                                <td>{{ $pharmaceuticalProduct->item_classification }}</td>
                            </tr>
                        </table>
                    </div>

                    @if($pharmaceuticalProduct->status == 'preliminary_approved')
                    <div class="form-actions">
                        <a href="{{ route('representative.pharmaceutical-products.edit-details', $pharmaceuticalProduct) }}" class="btn btn-primary">
                            <i class="ti ti-edit"></i>
                            {{ __('products.edit_detailed_data') }}
                        </a>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-warning mb-4">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>{{ __('products.detailed_not_complete') }}</strong>
                        @if($pharmaceuticalProduct->status == 'preliminary_approved')
                        <p class="mb-0 mt-2">{{ __('products.click_complete_detailed') }}</p>
                        @endif
                    </div>

                    @if($pharmaceuticalProduct->status == 'preliminary_approved')
                    <div class="text-center">
                        <a href="{{ route('representative.pharmaceutical-products.edit-details', $pharmaceuticalProduct) }}" class="btn btn-primary btn-lg">
                            <i class="ti ti-edit me-1"></i>
                            {{ __('products.complete_detailed_data') }}
                        </a>
                    </div>
                    @endif
                    @endif
                </div>

                <div class="tab-pane fade" id="documents" role="tabpanel">
                    @if($pharmaceuticalProduct->status == 'rejected' && $pharmaceuticalProduct->rejection_reason)
                        <div class="alert alert-danger mb-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-alert-triangle" style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-2" style="font-weight: 700;">{{ __('companies.request_rejected') }}</h5>
                                    <p class="mb-2"><strong>{{ __('companies.rejection_reason') }}:</strong></p>
                                    <p class="mb-0" style="background: rgba(255,255,255,0.9); padding: 12px; border-radius: 4px; border-right: 3px solid #dc2626;">
                                        {{ $pharmaceuticalProduct->rejection_reason }}
                                    </p>
                                    <hr style="margin: 15px 0; border-color: rgba(220, 38, 38, 0.3);">
                                    <p class="mb-0" style="font-size: 0.9rem;">
                                        <i class="ti ti-info-circle me-1"></i>
                                        {{ __('companies.edit_data_note') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php
                        $documentTypes = \App\Models\PharmaceuticalProductDocument::getDocumentTypes();
                        $requiredTypes = \App\Models\PharmaceuticalProductDocument::getRequiredDocumentTypes();
                        $optionalTypes = \App\Models\PharmaceuticalProductDocument::getOptionalDocumentTypes();
                        $uploadedTypesList = $pharmaceuticalProduct->documents()->pluck('document_type')->unique()->toArray();
                        $uploadedRequired = array_intersect($uploadedTypesList, $requiredTypes);
                        $missingRequired = array_diff($requiredTypes, $uploadedTypesList);
                        $allRequiredDone = count($missingRequired) === 0;
                        $progressPercent = count($requiredTypes) > 0 ? round((count($uploadedRequired) / count($requiredTypes)) * 100) : 0;

                        $groupedTypes = [
                            __('products.international_certificates') => [
                                'registration_forms',
                                'fda_certificate',
                                'ema_certificate',
                                'cpp_fsc_certificate',
                                'pricing_certificate',
                                'other_countries_registration'
                            ],
                            __('products.technical_studies') => [
                                'drug_master_file',
                                'product_specifications',
                                'active_ingredients_analysis',
                                'packaging_specifications',
                                'accelerated_stability_studies',
                                'hot_climate_stability_studies',
                                'pharmacology_toxicology_studies',
                                'bioequivalence_studies'
                            ],
                            __('products.packaging_materials') => [
                                'product_labels',
                                'internal_leaflets'
                            ]
                        ];
                    @endphp

                    <div class="docs-summary-bar">
                        <div class="summary-right">
                            <div class="summary-progress {{ $allRequiredDone ? 'done' : '' }}">
                                <div class="progress-ring">
                                    <svg viewBox="0 0 36 36">
                                        <path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="ring-fill" stroke-dasharray="{{ $progressPercent }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <span class="ring-text">{{ count($uploadedRequired) }}/{{ count($requiredTypes) }}</span>
                                </div>
                                <div class="summary-text">
                                    <strong>{{ __('documents.mandatory_label') }}</strong>
                                    <span>{{ $allRequiredDone ? __('documents.all_mandatory_uploaded') : __('documents.mandatory_docs_count', ['uploaded' => count($uploadedRequired), 'total' => count($requiredTypes)]) }}</span>
                                </div>
                            </div>
                            @if(count($missingRequired) > 0)
                            <div class="missing-docs-inline">
                                @foreach($missingRequired as $type)
                                    <span class="missing-tag"><i class="ti ti-circle-x"></i> {{ $documentTypes[$type] ?? $type }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @if(in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected']))
                        <div class="summary-actions">
                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').style.display='flex'">
                                <i class="ti ti-upload"></i>
                                {{ __('documents.upload_document') }}
                            </button>
                            @if($pharmaceuticalProduct->hasAllRequiredDocuments())
                                @if($pharmaceuticalProduct->status == 'rejected')
                                    <form action="{{ route('representative.pharmaceutical-products.submit-for-review', $pharmaceuticalProduct) }}" method="POST" style="margin: 0;" class="resubmit-review-form">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ti ti-refresh"></i>
                                            {{ __('products.resubmit_review') }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('representative.pharmaceutical-products.submit-for-review', $pharmaceuticalProduct) }}" method="POST" style="margin: 0;" class="submit-review-form">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ti ti-send"></i>
                                            {{ __('companies.submit_for_review') }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                        @endif
                    </div>

                    @php
                        $allDocuments = $pharmaceuticalProduct->documents()->get();
                    @endphp

                    @if($allDocuments->count() > 0)
                        <div class="documents-list">
                            @foreach($allDocuments as $document)
                            @php $isMandatory = in_array($document->document_type, $requiredTypes); @endphp
                            <div class="document-item uploaded">
                                <div class="document-header">
                                    <div class="document-info">
                                        <i class="ti ti-file-check"></i>
                                        <div>
                                            <h5>
                                                {{ $document->document_type_name }}
                                                <span class="doc-type-tag {{ $isMandatory ? 'tag-required' : 'tag-optional' }}">
                                                    {{ $isMandatory ? __('documents.mandatory_label') : __('documents.optional_label') }}
                                                </span>
                                            </h5>
                                            <small>{{ $document->original_name }} ({{ $document->file_size_formatted }})</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="document-actions">
                                    <button type="button" class="btn-doc btn-view btn-doc-preview" data-file-url="{{ $document->file_url }}" data-file-name="{{ $document->original_name }}" data-download-url="{{ $document->file_url }}">
                                        <i class="ti ti-eye"></i>
                                        {{ __('general.view') }}
                                    </button>
                                    @if(in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected']))
                                        <form action="{{ route('representative.pharmaceutical-products.update-document', [$pharmaceuticalProduct, $document]) }}" method="POST" enctype="multipart/form-data" style="display: inline;" class="inline-edit-form">
                                            @csrf
                                            <input type="file" name="document" class="d-none edit-file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <button type="button" class="btn-doc btn-edit btn-trigger-edit">
                                                <i class="ti ti-edit"></i>
                                                {{ __('general.edit') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('representative.pharmaceutical-products.delete-document', [$pharmaceuticalProduct, $document]) }}"
                                              method="POST"
                                              style="display: inline;"
                                              class="delete-document-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-doc btn-delete">
                                                <i class="ti ti-trash"></i>
                                                {{ __('general.delete') }}
                                            </button>
                                        </form>
                                    @elseif(!in_array($pharmaceuticalProduct->status, ['pending_final_approval']))
                                        @if($document->pendingUpdateRequest)
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="ti ti-clock me-1"></i>{{ __('documents.update_request_pending') }}</span>
                                        @else
                                            <button type="button" class="btn-doc btn-edit btn-update-request" style="color: #fff; background: #f59e0b; border-color: #f59e0b;" data-doc-id="{{ $document->id }}" data-doc-name="{{ $document->original_name }}" data-doc-type="pharmaceutical_product_document">
                                                <i class="ti ti-replace"></i>
                                                {{ __('documents.update_request') }}
                                            </button>
                                        @endif
                                    @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('documents.no_documents') }}
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="invoice" role="tabpanel">
                    @if($pharmaceuticalProduct->invoices->count() > 0)
                        @php
                            $invoice = $pharmaceuticalProduct->invoices->first();
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-3 text-muted">{{ __('products.invoice_data') }}</h6>
                                        <table class="table table-sm table-bordered">
                                            <tr>
                                                <th width="40%" class="bg-light">{{ __('invoices.invoice_number') }}</th>
                                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">{{ __('invoices.invoice_amount') }}</th>
                                                <td><strong class="text-success">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">{{ __('general.status') }}</th>
                                                <td><span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_name }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">{{ __('general.issue_date') }}</th>
                                                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                            </tr>
                                            @if($invoice->paid_at)
                                            <tr>
                                                <th class="bg-light">{{ __('invoices.payment_date') }}</th>
                                                <td>{{ $invoice->paid_at->format('Y-m-d') }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-3 text-muted">{{ __('invoices.payment_receipt') }}</h6>

                                        @if($invoice->status == 'unpaid')
                                            <div class="alert alert-warning mb-3">
                                                <i class="ti ti-alert-circle me-2"></i>
                                                <strong>{{ __('products.please_upload_receipt') }}</strong>
                                            </div>

                                            <form action="{{ route('representative.pharmaceutical-products.invoices.upload-receipt', [$pharmaceuticalProduct, $invoice]) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('invoices.payment_receipt') }} <span class="text-danger">*</span></label>
                                                    <input type="file" name="receipt" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="text-muted">{{ __('products.max_size_5mb') }}</small>
                                                </div>
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="ti ti-upload me-1"></i>
                                                    {{ __('invoices.upload_receipt') }}
                                                </button>
                                            </form>
                                        @elseif($invoice->status == 'pending_review')
                                            <div class="alert alert-info mb-3">
                                                <i class="ti ti-clock me-2"></i>
                                                <strong>{{ __('products.receipt_uploaded') }}</strong>
                                                <p class="mb-0 mt-2">{{ __('products.receipt_under_review') }}</p>
                                            </div>

                                            @if($invoice->receipt_path)
                                                <a href="{{ $invoice->receipt_url }}" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                                    <i class="ti ti-eye me-1"></i>
                                                    {{ __('products.view_uploaded_receipt') }}
                                                </a>
                                            @endif
                                        @elseif($invoice->status == 'paid')
                                            <div class="alert alert-success mb-3">
                                                <i class="ti ti-check me-2"></i>
                                                <strong>{{ __('products.receipt_accepted') }}</strong>
                                                <p class="mb-0 mt-2">{{ __('products.product_activated') }}</p>
                                            </div>

                                            @if($invoice->receipt_path)
                                                <a href="{{ $invoice->receipt_url }}" target="_blank" class="btn btn-outline-success w-100">
                                                    <i class="ti ti-eye me-1"></i>
                                                    {{ __('products.view_receipt') }}
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($pharmaceuticalProduct->status == 'rejected' && $pharmaceuticalProduct->rejection_reason)
                            <div class="alert alert-danger">
                                <i class="ti ti-alert-circle me-2"></i>
                                <strong>{{ __('companies.rejection_reason') }}:</strong>
                                <p class="mb-0 mt-2">{{ $pharmaceuticalProduct->rejection_reason }}</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            {{ __('products.invoice_after_approval') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div id="uploadModal" class="upload-modal">
    <div class="upload-modal-content">
        <div class="upload-modal-header">
            <h3><i class="ti ti-upload"></i> {{ __('documents.upload_new') }}</h3>
            <button type="button" class="close-modal" onclick="document.getElementById('uploadModal').style.display='none'">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form action="{{ route('representative.pharmaceutical-products.upload-document', $pharmaceuticalProduct) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            <div class="upload-modal-body">
                <div class="form-group">
                    <label>{{ __('documents.document_type') }} <span class="required">*</span></label>
                    <select name="document_type" id="document_type" class="form-control" required>
                        <option value="">{{ __('documents.select_type') }}</option>
                        @php
                            $uploadedDocTypes = $pharmaceuticalProduct->documents()
                                ->select('document_type')
                                ->distinct()
                                ->pluck('document_type')
                                ->toArray();
                        @endphp
                        @foreach($groupedTypes as $groupName => $groupDocTypes)
                            <optgroup label="{{ $groupName }}">
                                @foreach($groupDocTypes as $docType)
                                    <option value="{{ $docType }}">
                                        {{ $documentTypes[$docType] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('documents.file') }} <span class="required">*</span></label>
                    <input type="file" name="document" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                    <small>{{ __('products.max_size_types') }}</small>
                </div>

                <div class="form-group">
                    <label>{{ __('documents.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3" maxlength="500" placeholder="{{ __('documents.notes_optional') }}"></textarea>
                </div>
            </div>
            <div class="upload-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('uploadModal').style.display='none'">{{ __('general.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-upload"></i> {{ __('documents.upload_document') }}</button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="updateRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('representative.document-update-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentable_type" id="ur_documentable_type">
                <input type="hidden" name="documentable_id" id="ur_documentable_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-replace me-2"></i>{{ __('documents.request_update') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">{{ __('documents.document') }}: <strong id="ur_doc_name"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.new_file') }} <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <small class="text-muted">{{ __('documents.file_limit_short') }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.update_reason') }}</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="{{ __('documents.enter_reason') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>{{ __('documents.send_request') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    * {
        font-family: 'Almarai', sans-serif !important;
    }

    .auth-form {
        width: 100%;
        max-width: 1400px;
        padding: 0 20px;
    }

    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 100% !important;
    }

    .dashboard-container * {
        max-width: 100% !important;
    }

    .dashboard-container .card,
    .dashboard-container .card-body,
    .dashboard-container .tab-content,
    .dashboard-container .tab-pane,
    .dashboard-container .table {
        max-width: 100% !important;
        width: 100% !important;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-to-home {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .back-to-home:hover {
        background: #1a5f4a;
        color: #ffffff;
        border-color: #1a5f4a;
    }

    .page-header-content h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header-content p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .card-header {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 0;
    }

    .nav-tabs {
        border-bottom: none;
        padding: 15px 20px 0;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-weight: 500;
        padding: 10px 20px;
        transition: all 0.2s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #1a5f4a;
        border-bottom-color: #e5e7eb;
    }

    .nav-tabs .nav-link.active {
        color: #1a5f4a;
        border-bottom-color: #1a5f4a;
        background: transparent;
    }

    .card-body {
        padding: 25px;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }

    .table {
        margin-bottom: 0;
    }

    .table th,
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table th {
        font-weight: 600;
        color: #374151;
    }

    .table td {
        color: #1f2937;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .alert {
        padding: 15px;
        border-radius: 6px;
        border: 1px solid;
        display: flex;
        align-items: center;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .alert-danger .alert-heading {
        color: #991b1b;
        font-size: 1.1rem;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-color: #6ee7b7;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }

    .alert-gradient-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 50%, #6ee7b7 100%);
        border: 2px solid #10b981;
        border-radius: 12px;
        padding: 20px;
        color: #064e3b;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        animation: fadeInScale 0.5s ease-out;
        position: relative;
        overflow: hidden;
    }

    .alert-gradient-success::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes shimmer {
        0% {
            left: -100%;
        }
        100% {
            left: 100%;
        }
    }

    .alert-gradient-success .alert-icon-wrapper {
        font-size: 3rem;
        color: #10b981;
        line-height: 1;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    .alert-gradient-success .alert-heading {
        color: #064e3b;
        margin: 0;
    }

    .alert-steps {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        margin-top: 12px;
    }

    .step-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .step-item.completed {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .step-item.completed i {
        color: #10b981;
        font-size: 1.1rem;
    }

    .step-item.next {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateX(0);
        }
        50% {
            transform: translateX(-5px);
        }
    }

    .step-item.next i {
        color: #f59e0b;
        font-size: 1.1rem;
    }

    .btn-lg {
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: 700;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .text-danger {
        color: #ef4444;
    }

    .docs-summary-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .summary-right {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
        flex-wrap: wrap;
    }

    .summary-progress {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .progress-ring {
        width: 46px;
        height: 46px;
        position: relative;
    }

    .progress-ring svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .ring-bg {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 3;
    }

    .ring-fill {
        fill: none;
        stroke: #d97706;
        stroke-width: 3;
        stroke-linecap: round;
        transition: stroke-dasharray 0.6s ease;
    }

    .summary-progress.done .ring-fill {
        stroke: #16a34a;
    }

    .ring-text {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
        color: #374151;
    }

    .summary-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .summary-text strong {
        font-size: 0.8rem;
        color: #1f2937;
    }

    .summary-text span {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .summary-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    .missing-docs-inline {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .missing-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .missing-tag i {
        font-size: 0.8rem;
    }

    .doc-type-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 700;
        vertical-align: middle;
        margin-inline-start: 8px;
    }

    .tag-required {
        background: #fef2f2;
        color: #dc2626;
    }

    .tag-optional {
        background: #eff6ff;
        color: #2563eb;
    }

    .document-group {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
    }

    .group-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1a5f4a;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }

    .documents-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .document-item {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        transition: all 0.2s ease;
    }

    .document-item:hover {
        border-color: #d1d5db;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .document-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .document-info i {
        font-size: 1.5rem;
    }

    .document-item.uploaded .document-info i {
        color: #10b981;
    }

    .document-item.pending .document-info i {
        color: #f59e0b;
    }

    .document-info h5 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 3px 0;
    }

    .document-info small {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .document-actions {
        display: flex;
        gap: 8px;
    }

    .btn-doc {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-view {
        background: #3b82f6;
        color: #ffffff;
    }

    .btn-view:hover {
        background: #2563eb;
    }

    .btn-edit {
        background: #f59e0b;
        color: #ffffff;
    }

    .btn-edit:hover {
        background: #d97706;
    }

    .btn-upload {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-upload:hover {
        background: #164538;
    }

    .btn-delete {
        background: #ef4444;
        color: #ffffff;
    }

    .btn-delete:hover {
        background: #dc2626;
    }

    .action-buttons {
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        min-width: 150px;
    }

    .submit-section {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
        text-align: center;
    }

    .btn-success {
        background: #10b981;
        color: #ffffff;
    }

    .btn-success:hover {
        background: #059669;
    }

    .upload-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .upload-modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .upload-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .upload-modal-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .close-modal {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .close-modal:hover {
        background: #f3f4f6;
    }

    .upload-modal-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .form-control option:disabled {
        color: #9ca3af;
        background: #f3f4f6;
    }

    .form-group small {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 5px;
    }

    .upload-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .btn-secondary {
        background: #6b7280;
        color: #ffffff;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    @media (max-width: 992px) {
        .auth-form {
            max-width: 100%;
            padding: 0 10px;
        }

        .dashboard-container {
            padding: 20px 15px;
            border-radius: 0;
        }

        .upload-modal-content {
            width: 95%;
            max-width: calc(100% - 1rem);
        }

        .docs-summary-bar {
            flex-direction: column;
            gap: 12px;
        }

        .summary-actions {
            width: 100%;
        }

        .summary-actions .btn {
            flex: 1;
            min-width: auto;
        }

        .missing-docs-inline {
            flex-wrap: wrap;
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch !important;
        }

        .action-buttons .btn {
            width: 100%;
            min-width: auto;
        }

        .document-actions {
            flex-direction: column;
            width: 100%;
        }

        .btn-doc {
            width: 100%;
            justify-content: center;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .page-header-content {
            width: 100%;
        }

        .page-header-content h1 {
            font-size: 1.25rem;
            word-break: break-word;
        }

        .header-actions {
            width: 100%;
        }

        .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px 15px 0;
        }

        .nav-tabs .nav-link {
            white-space: nowrap;
            font-size: 0.8125rem;
            padding: 8px 12px;
        }

        .card-body {
            padding: 15px;
        }

        .table {
            font-size: 0.8125rem;
        }

        .table th,
        .table td {
            padding: 8px 10px;
        }

        .section-title {
            font-size: 0.9375rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .alert-gradient-success {
            padding: 15px;
        }

        .alert-gradient-success .alert-icon-wrapper {
            font-size: 2rem;
        }

        .alert-gradient-success .alert-heading {
            font-size: 1rem;
        }

        .alert-steps {
            gap: 8px;
            padding: 10px;
        }

        .step-item {
            font-size: 0.75rem;
            padding: 6px 10px;
        }
    }

    @media (max-width: 576px) {
        .dashboard-container {
            padding: 15px 10px;
        }

        .page-header {
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .page-header-content h1 {
            font-size: 1.125rem;
        }

        .page-header-content p {
            font-size: 0.8125rem;
        }

        .back-to-home {
            width: 35px;
            height: 35px;
        }

        .nav-tabs .nav-link {
            font-size: 0.75rem;
            padding: 6px 10px;
        }

        .card-body {
            padding: 12px;
        }

        .table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 6px 8px;
            min-width: 100px;
        }

        .table th {
            font-size: 0.75rem;
        }

        .section-title {
            font-size: 0.875rem;
            margin-bottom: 12px;
        }

        .badge {
            font-size: 0.6875rem;
            padding: 4px 8px;
        }

        .alert {
            padding: 12px;
            font-size: 0.8125rem;
        }

        .alert-gradient-success {
            padding: 12px;
        }

        .alert-gradient-success .alert-icon-wrapper {
            font-size: 1.75rem;
        }

        .alert-gradient-success .alert-heading {
            font-size: 0.9rem;
        }

        .alert-gradient-success p {
            font-size: 0.8rem;
        }

        .alert-steps {
            flex-direction: column;
            gap: 6px;
            padding: 8px;
        }

        .step-item {
            width: 100%;
            justify-content: center;
            font-size: 0.7rem;
            padding: 6px 8px;
        }

        .docs-summary-bar {
            flex-direction: column;
            gap: 12px;
            padding: 12px;
        }

        .summary-actions {
            width: 100%;
        }

        .summary-actions .btn {
            flex: 1;
            min-width: auto;
        }

        .missing-docs-inline {
            flex-wrap: wrap;
        }
    }

    .uploaded-documents-list {
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .uploaded-document-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: white;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }

    .uploaded-document-row:last-child {
        margin-bottom: 0;
    }

    .document-details {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .document-details i {
        color: #0d6efd;
        font-size: 1.25rem;
    }

    .document-details span {
        font-weight: 500;
    }

    .document-details small {
        color: #6c757d;
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
        .uploaded-document-row {
            flex-direction: column;
            gap: 0.75rem;
            align-items: stretch;
        }

        .document-actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }

        .document-actions .btn-doc {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openUpdateRequestModal(docId, docName, docType) {
    var modal = document.getElementById('updateRequestModal');
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    document.getElementById('ur_documentable_id').value = docId;
    document.getElementById('ur_documentable_type').value = docType;
    document.getElementById('ur_doc_name').textContent = docName;
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    setTimeout(function() {
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.style.zIndex = '9998';
        modal.style.zIndex = '9999';
    }, 50);
}
</script>
<script>
    const uploadModal = document.getElementById('uploadModal');

    window.addEventListener('click', function(event) {
        if (event.target == uploadModal) {
            uploadModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            uploadModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });

    const productId = '{{ $pharmaceuticalProduct->id }}';
    const tabStorageKey = `pharmaceutical_product_active_tab_${productId}`;
    const productStatus = '{{ $pharmaceuticalProduct->status }}';

    document.addEventListener('DOMContentLoaded', function() {
        if (productStatus == 'rejected') {
            const documentsTab = document.querySelector('button[data-bs-target="#documents"]');
            if (documentsTab) {
                const tab = new bootstrap.Tab(documentsTab);
                tab.show();
            }
        } else {
            const savedTab = localStorage.getItem(tabStorageKey);
            if (savedTab) {
                const tabButton = document.querySelector(`button[data-bs-target="${savedTab}"]`);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }
        }
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const targetTab = event.target.getAttribute('data-bs-target');
            localStorage.setItem(tabStorageKey, targetTab);
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelector('.submit-details-form-inline')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '{{ __('products.submit_final_review_title') }}',
            html: '<p>{{ __('products.submit_final_review_confirm_msg') }}</p><p class="text-danger mt-2"><strong>{{ __('products.no_edit_after_submit_html') }}</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __('products.yes_submit_final') }}',
            cancelButtonText: '{{ __('general.cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    document.querySelector('.submit-review-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '{{ __('products.submit_review_title') }}',
            html: '<p>{{ __('products.submit_review_confirm_msg') }}</p><p class="text-danger mt-2"><strong>{{ __('products.no_edit_docs_after_submit_html') }}</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __('products.yes_submit_review') }}',
            cancelButtonText: '{{ __('general.cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    document.querySelectorAll('.delete-document-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;

            Swal.fire({
                title: '{{ __('products.delete_document_title') }}',
                text: '{{ __('products.delete_document_confirm_msg') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '{{ __('products.yes_delete_document') }}',
                cancelButtonText: '{{ __('general.cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    formElement.submit();
                }
            });
        });
    });

    document.querySelector('.resubmit-review-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '{{ __('products.resubmit_review_title') }}',
            html: '<p>{{ __('products.resubmit_review_confirm_msg') }}</p><p class="text-info mt-2"><strong>{{ __('products.rejection_cleared_msg') }}</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __('products.yes_resubmit') }}',
            cancelButtonText: '{{ __('general.cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    document.addEventListener('click', function(e) {
        var triggerBtn = e.target.closest('.btn-trigger-edit');
        if (triggerBtn) {
            e.preventDefault();
            var form = triggerBtn.closest('.inline-edit-form');
            form.querySelector('.edit-file-input').click();
            return;
        }

        var updateBtn = e.target.closest('.btn-update-request');
        if (updateBtn) {
            e.preventDefault();
            openUpdateRequestModal(
                updateBtn.getAttribute('data-doc-id'),
                updateBtn.getAttribute('data-doc-name'),
                updateBtn.getAttribute('data-doc-type')
            );
            return;
        }
    });

    var allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
    var maxSize = 10 * 1024 * 1024;

    function validateFile(file) {
        var ext = file.name.split('.').pop().toLowerCase();
        if (allowedExts.indexOf(ext) === -1) {
            Swal.fire({
                title: '{{ __("general.error") }}',
                text: '{{ __("documents.invalid_file_type") }}',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return false;
        }
        if (file.size > maxSize) {
            Swal.fire({
                title: '{{ __("general.error") }}',
                text: '{{ __("documents.file_too_large") }}',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return false;
        }
        return true;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('edit-file-input') && e.target.files.length > 0) {
            var file = e.target.files[0];
            if (!validateFile(file)) {
                e.target.value = '';
                return;
            }
            var form = e.target.closest('.inline-edit-form');
            Swal.fire({
                title: '{{ __("documents.edit_document") }}',
                html: '<p>{{ __("products.replace_current_note") }}</p><p><strong>' + file.name + '</strong></p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a5f4a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '{{ __("products.save_edit") }}',
                cancelButtonText: '{{ __("general.cancel") }}'
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    e.target.value = '';
                }
            });
        }
    });

    document.getElementById('uploadModal').querySelector('form').addEventListener('submit', function(e) {
        var fileInput = this.querySelector('input[type="file"]');
        if (fileInput.files.length > 0 && !validateFile(fileInput.files[0])) {
            e.preventDefault();
        }
    });
</script>
@endpush
