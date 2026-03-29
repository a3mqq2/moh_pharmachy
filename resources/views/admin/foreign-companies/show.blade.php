@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('companies.company_details') . ': ' . $foreignCompany->company_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-companies.index') }}">{{ __('companies.foreign_companies') }}</a></li>
    <li class="breadcrumb-item active">{{ $foreignCompany->company_name }}</li>
@endsection

@section('content')
<div class="show-header mt-3 mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-2"><i class="ti ti-world me-2 text-primary"></i>{{ $foreignCompany->company_name }}</h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-{{ $foreignCompany->entity_type == 'factory' ? 'info' : 'primary' }}">{{ $foreignCompany->entity_type_name }}</span>
                <span class="badge {{ str_replace('badge-', 'bg-', $foreignCompany->status_badge_class) }}">{{ $foreignCompany->status_name }}</span>
                <span class="badge bg-dark">{{ $foreignCompany->country }}</span>
                @if($foreignCompany->registration_number)
                    <span class="badge bg-dark">{{ __('general.registration_number') }}: {{ $foreignCompany->registration_number }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($foreignCompany->status, ['approved', 'active']))
                <a href="{{ route('admin.foreign-companies.certificate', $foreignCompany) }}" target="_blank" class="btn btn-primary">
                    <i class="ti ti-printer me-1"></i>{{ __('companies.print_cert') }}
                </a>
            @endif
            @if(in_array($foreignCompany->status, ['approved', 'active', 'pending']))
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cgmpModal">
                    <i class="ti ti-certificate me-1"></i>{{ __('companies.factory_inspection') }}
                    @if($foreignCompany->cgmp_certificate_path)
                        <i class="ti ti-check text-success ms-1"></i>
                    @endif
                </button>
            @endif
            @if($foreignCompany->status == 'pending')
                @if($foreignCompany->hasAllRequiredDocuments())
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="ti ti-check me-1"></i>{{ __('companies.accept') }}
                    </button>
                @else
                    <button type="button" class="btn btn-success" disabled title="{{ __('companies.must_upload_all_docs_short') }}">
                        <i class="ti ti-check me-1"></i>{{ __('companies.accept') }}
                    </button>
                @endif
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>{{ __('companies.reject') }}
                </button>
            @elseif($foreignCompany->status == 'rejected')
                <form action="{{ route('admin.foreign-companies.restore-pending', $foreignCompany) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.return_review') }}</button>
                </form>
            @elseif(in_array($foreignCompany->status, ['active', 'expired']))
                <form action="{{ route('admin.foreign-companies.request-renewal', $foreignCompany) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.renewal_request') }}</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                    <i class="ti ti-ban me-1"></i>{{ __('companies.suspend') }}
                </button>
            @elseif($foreignCompany->status == 'suspended')
                <form action="{{ route('admin.foreign-companies.unsuspend', $foreignCompany) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="ti ti-player-play me-1"></i>{{ __('companies.unsuspend') }}</button>
                </form>
            @endif
            <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-right me-1"></i>{{ __('general.back') }}</a>
        </div>
    </div>
</div>


@if($foreignCompany->status == 'active' && $foreignCompany->expires_at && $foreignCompany->isExpired())
@php $expiredDays = (int) abs(now()->diffInDays($foreignCompany->expires_at)); @endphp
<div class="alert alert-danger d-flex align-items-center justify-content-between">
    <div>
        <i class="ti ti-alert-octagon me-2 fs-4"></i>
        <strong>{{ __('general.warning') }}:</strong> {{ __('companies.expiry_warning') }} <strong>{{ $expiredDays }} {{ __('general.day') }}</strong> ({{ $foreignCompany->expires_at->format('Y-m-d') }}).
        {{ __('companies.must_create_renewal') }}
    </div>
    <form action="{{ route('admin.foreign-companies.request-renewal', $foreignCompany) }}" method="POST" class="d-inline ms-3">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm"><i class="ti ti-refresh me-1"></i>{{ __('companies.renew_now') }}</button>
    </form>
</div>
@elseif($foreignCompany->status == 'active' && $foreignCompany->expires_at && !$foreignCompany->isExpired())
@php $daysUntilExpiry = (int) now()->diffInDays($foreignCompany->expires_at, false); @endphp
@if($daysUntilExpiry <= 90)
<div class="alert alert-warning">
    <i class="ti ti-clock-exclamation me-2"></i>
    <strong>{{ __('general.warning') }}:</strong> {{ __('companies.expiry_soon_warning') }} <strong>{{ $daysUntilExpiry }} {{ __('general.day') }}</strong> ({{ $foreignCompany->expires_at->format('Y-m-d') }}).
</div>
@endif
@endif

@if($foreignCompany->status == 'expired')
<div class="alert alert-danger">
    <i class="ti ti-alert-octagon me-2 fs-4"></i>
    <strong>{{ __('companies.company_expired') }}.</strong> {{ __('companies.must_create_renewal_invoice') }}
</div>
@endif

@if($foreignCompany->status == 'suspended' && $foreignCompany->suspension_reason)
<div class="alert alert-secondary">
    <i class="ti ti-ban me-2"></i>
    <strong>{{ __('companies.company_suspended_reason') }}:</strong> {{ $foreignCompany->suspension_reason }}
</div>
@endif

@if($foreignCompany->status == 'rejected' && $foreignCompany->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>{{ __('companies.rejection_reason') }}:</strong> {{ $foreignCompany->rejection_reason }}
</div>
@endif

@if($foreignCompany->status == 'pending' && !$foreignCompany->hasAllRequiredDocuments())
<div class="alert alert-warning">
    <strong><i class="ti ti-alert-triangle me-1"></i>{{ __('companies.docs_incomplete') }}:</strong>
    <p class="mb-0 mt-2">{{ __('companies.docs_incomplete_msg') }}</p>
</div>
@endif


<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="companyTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-company"><i class="ti ti-building me-1"></i>{{ __('companies.company_data') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-representative"><i class="ti ti-user me-1"></i>{{ __('companies.company_representative') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>{{ __('documents.documents') }}
                    <span class="badge {{ $foreignCompany->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }} rounded-pill ms-1">
                        {{ $foreignCompany->documents->count() }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                    <i class="ti ti-file-invoice me-1"></i>{{ __('invoices.invoices') }}
                    @if($foreignCompany->invoices()->where('status', 'pending')->count() > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $foreignCompany->invoices()->where('status', 'pending')->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-company">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-building me-2"></i>{{ __('companies.company_info') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-striped info-table">
                                <tr><th class="bg-light" width="40%">{{ __('companies.company_name') }}</th><td>{{ $foreignCompany->company_name }}</td></tr>
                                <tr><th class="bg-light">{{ __('general.country') }}</th><td>{{ $foreignCompany->country }}</td></tr>
                                <tr><th class="bg-light">{{ __('companies.entity_type') }}</th><td>{{ $foreignCompany->entity_type_name }}</td></tr>
                                <tr><th class="bg-light">{{ __('companies.activity_type') }}</th><td>{{ $foreignCompany->activity_type_name }}</td></tr>
                                <tr><th class="bg-light">{{ __('companies.product_count') }}</th><td>{{ $foreignCompany->products_count ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">
                                    <i class="ti ti-certificate me-2"></i>{{ __('companies.registration_details') }}
                                    @if($foreignCompany->is_pre_registered)
                                        <span class="badge bg-info ms-2">{{ __('companies.previously_registered_label') }}</span>
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <tr>
                                        <th class="bg-light" width="20%">{{ __('general.registration_number') }}</th>
                                        <td width="30%">
                                            @if($foreignCompany->registration_number)
                                                <span class="fw-bold text-primary fs-6">{{ $foreignCompany->registration_number }}</span>
                                            @else
                                                <span class="text-muted">{{ __('general.not_issued_yet') }}</span>
                                            @endif
                                        </td>
                                        <th class="bg-light" width="20%">{{ __('companies.meeting_number') }}</th>
                                        <td width="30%">{{ $foreignCompany->meeting_number ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('companies.meeting_date') }}</th>
                                        <td>{{ $foreignCompany->meeting_date?->format('Y-m-d') ?? '-' }}</td>
                                        <th class="bg-light">{{ __('companies.last_renewal_date') }}</th>
                                        <td>{{ $foreignCompany->last_renewed_at?->format('Y-m-d') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('companies.expiry_date') }}</th>
                                        <td colspan="3">
                                            @if($foreignCompany->expires_at)
                                                @php
                                                    $daysLeft = (int) now()->diffInDays($foreignCompany->expires_at, false);
                                                @endphp
                                                {{ $foreignCompany->expires_at->format('Y-m-d') }}
                                                @if($foreignCompany->isExpired())
                                                    <span class="badge bg-danger ms-1">{{ __('companies.expired_label') }} {{ abs($daysLeft) }} {{ __('general.day') }}</span>
                                                @elseif($daysLeft <= 90)
                                                    <span class="badge bg-warning ms-1">{{ __('general.days_remaining') }}: {{ $daysLeft }}</span>
                                                @else
                                                    <span class="badge bg-success ms-1">{{ __('general.days_remaining') }}: {{ $daysLeft }}</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-phone me-2"></i>{{ __('companies.contact_info') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-striped info-table">
                                <tr><th class="bg-light" width="40%">{{ __('general.address') }}</th><td>{{ $foreignCompany->address ?? '-' }}</td></tr>
                                <tr><th class="bg-light">{{ __('general.email') }}</th><td>{{ $foreignCompany->email ?? '-' }}</td></tr>
                                <tr>
                                    <th class="bg-light">{{ __('companies.local_company') }}</th>
                                    <td>
                                        @if($foreignCompany->localCompany)
                                            <a href="{{ route('admin.local-companies.show', $foreignCompany->localCompany) }}">
                                                {{ $foreignCompany->localCompany->company_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('companies.registered_countries') }}</th>
                                    <td>
                                        @if($foreignCompany->registered_countries && is_array($foreignCompany->registered_countries) && count($foreignCompany->registered_countries) > 0)
                                            {{ implode(', ', $foreignCompany->registered_countries) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-representative">
                @if($foreignCompany->representative)
                <div class="table-responsive">
                    <table class="table table-striped info-table">
                        <tr>
                            <th class="bg-light" width="15%">{{ __('general.name') }}</th>
                            <td width="35%">{{ $foreignCompany->representative->name }}</td>
                            <th class="bg-light" width="15%">{{ __('general.email') }}</th>
                            <td width="35%">{{ $foreignCompany->representative->email }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.phone') }}</th>
                            <td dir="ltr" class="text-end">{{ $foreignCompany->representative->phone ?? '-' }}</td>
                            <th class="bg-light">{{ __('general.country') }}</th>
                            <td>{{ $foreignCompany->representative->nationality ?? '-' }}</td>
                        </tr>
                    <tr>
                        <th class="bg-light">{{ __('general.registration_date') }}</th>
                        <td>{{ $foreignCompany->representative->created_at->format('Y-m-d h:i A') }}</td>
                        <th class="bg-light">{{ __('general.status') }}</th>
                        <td>
                            @if($foreignCompany->representative->email_verified_at)
                                <span class="badge bg-success">{{ __('general.enabled') }}</span>
                            @else
                                <span class="badge bg-warning">{{ __('general.disabled') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-user-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">{{ __('companies.no_representatives') }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-documents">
                @php
                    $allDocTypes = \App\Models\ForeignCompanyDocument::getDocumentTypes();
                    $requiredTypes = \App\Models\ForeignCompanyDocument::getRequiredDocumentTypes();
                    $optionalTypes = \App\Models\ForeignCompanyDocument::getOptionalDocumentTypes();
                    $uploadedTypes = $foreignCompany->documents->pluck('document_type')->unique()->toArray();
                    $uploadedRequired = array_intersect($uploadedTypes, $requiredTypes);
                    $uploadedOptional = $foreignCompany->documents->filter(fn($d) => in_array($d->document_type, $optionalTypes));
                    $missingRequired = array_diff($requiredTypes, $uploadedTypes);
                    $allRequiredDone = count($missingRequired) === 0;
                    $progressPercent = count($requiredTypes) > 0 ? (count($uploadedRequired) / count($requiredTypes)) * 100 : 0;
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-folder me-1"></i>
                        {{ __('documents.documents') }}
                        @if($allRequiredDone)
                            <span class="badge bg-success ms-2"><i class="ti ti-check me-1"></i>{{ __('documents.complete') }}</span>
                        @else
                            <span class="badge bg-warning ms-2"><i class="ti ti-alert-triangle me-1"></i>{{ __('documents.incomplete_docs') }}</span>
                        @endif
                    </h6>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #ef4444 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                                        <i class="ti ti-lock me-1"></i>{{ __('documents.mandatory_label') }}
                                    </span>
                                    <span class="fw-bold text-muted" style="font-size: 0.85rem;">{{ count($uploadedRequired) }}/{{ count($requiredTypes) }}</span>
                                </div>
                                <p class="text-muted mb-3" style="font-size: 0.78rem;">{{ __('documents.mandatory_docs_note') }}</p>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar {{ $allRequiredDone ? 'bg-success' : 'bg-warning' }}" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                @foreach($requiredTypes as $type)
                                    @php $isUploaded = in_array($type, $uploadedTypes); @endphp
                                    <div class="d-flex align-items-center gap-2 py-1 px-2 rounded mb-1" style="background: {{ $isUploaded ? '#f0fdf4' : '#fef2f2' }}; font-size: 0.85rem;">
                                        <i class="ti ti-{{ $isUploaded ? 'circle-check-filled' : 'circle-dashed' }}" style="color: {{ $isUploaded ? '#16a34a' : '#dc2626' }};"></i>
                                        <span style="color: {{ $isUploaded ? '#166534' : '#991b1b' }}; font-weight: 500;">{{ $allDocTypes[$type] ?? $type }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #3b82f6 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                        <i class="ti ti-lock-open me-1"></i>{{ __('documents.optional_label') }}
                                    </span>
                                    <span class="fw-bold text-muted" style="font-size: 0.85rem;">{{ $uploadedOptional->count() }}</span>
                                </div>
                                <p class="text-muted mb-3" style="font-size: 0.78rem;">{{ __('documents.optional_docs_note') }}</p>
                                @foreach($optionalTypes as $type)
                                    @php $isUploaded = in_array($type, $uploadedTypes); @endphp
                                    <div class="d-flex align-items-center gap-2 py-1 px-2 rounded mb-1" style="background: {{ $isUploaded ? '#f0fdf4' : '#f9fafb' }}; font-size: 0.85rem;">
                                        <i class="ti ti-{{ $isUploaded ? 'circle-check-filled' : 'circle-dashed' }}" style="color: {{ $isUploaded ? '#16a34a' : '#d1d5db' }};"></i>
                                        <span style="color: {{ $isUploaded ? '#166534' : '#6b7280' }}; font-weight: 500;">{{ $allDocTypes[$type] ?? $type }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                @if($foreignCompany->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">{{ __('documents.document_type') }}</th>
                                <th width="15%">{{ __('documents.file_name') }}</th>
                                <th width="10%">{{ __('documents.file_size') }}</th>
                                <th width="20%">{{ __('general.date') }}</th>
                                <th width="20%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($foreignCompany->documents as $index => $document)
                            @php $isMandatory = in_array($document->document_type, $requiredTypes); @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $document->document_type_name }}</strong>
                                        <span class="badge {{ $isMandatory ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 {{ $isMandatory ? 'text-danger' : 'text-primary' }} ms-1" style="font-size: 0.65rem;">
                                            {{ $isMandatory ? __('documents.mandatory_label') : __('documents.optional_label') }}
                                        </span>
                                        @if($document->notes)
                                            <br><small class="text-muted">{{ Str::limit($document->notes, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $document->document_name }}">
                                        {{ Str::limit($document->document_name, 20) }}
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
                                        <button type="button" class="btn btn-outline-info btn-doc-preview" data-file-url="{{ Storage::url($document->file_path) }}" data-file-name="{{ $document->document_name }}" data-download-url="{{ route('admin.foreign-companies.documents.download', [$foreignCompany, $document]) }}" title="{{ __('general.view') }}">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.foreign-companies.documents.download', [$foreignCompany, $document]) }}" class="btn btn-outline-primary" title="{{ __('general.download') }}">
                                            <i class="ti ti-download"></i>
                                        </a>
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
                    <p class="text-muted mb-3">{{ __('documents.no_documents') }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoices">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('invoices.invoices') }}
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> {{ __('invoices.add_invoice') }}
                    </button>
                </div>

                @if($foreignCompany->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="12%">{{ __('invoices.invoice_number') }}</th>
                                <th width="20%">{{ __('invoices.invoice_description') }}</th>
                                <th width="10%">{{ __('invoices.invoice_amount') }}</th>
                                <th width="12%">{{ __('general.status') }}</th>
                                <th width="13%">{{ __('general.date') }}</th>
                                <th width="20%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($foreignCompany->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>
                                    {{ $invoice->description }}
                                    @if($invoice->notes)
                                        <br><small class="text-muted">{{ Str::limit($invoice->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> {{ __('general.currency') }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">
                                        {{ $invoice->status == 'paid' ? __('invoices.status_paid') : __('invoices.status_pending') }}
                                    </span>
                                    @if($invoice->paid_at)
                                        <br><small class="text-muted">{{ $invoice->paid_at->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $invoice->created_at->format('Y-m-d') }}</small>
                                    @if($invoice->due_date)
                                        <br><small class="text-muted">{{ __('invoices.due_date') }}: {{ $invoice->due_date->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($invoice->receipt_path && $invoice->receipt_status == 'pending')
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-company-id="{{ $foreignCompany->id }}">
                                                <i class="ti ti-check me-1"></i>{{ __('invoices.approve_receipt') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-x me-1"></i>{{ __('invoices.reject_receipt') }}
                                            </button>
                                        @endif
                                        @if($invoice->receipt_path)
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-doc-preview" data-file-url="{{ Storage::url($invoice->receipt_path) }}" data-file-name="{{ __('invoices.payment_receipt') }}_{{ $invoice->invoice_number }}" data-download-url="{{ route('admin.foreign-companies.invoices.download-receipt', [$foreignCompany, $invoice]) }}">
                                                <i class="ti ti-eye me-1"></i>{{ __('invoices.view_receipt') }}
                                            </button>
                                            <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$foreignCompany, $invoice]) }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-download me-1"></i>{{ __('invoices.download_receipt') }}
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-invoice" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                            <i class="ti ti-trash me-1"></i>{{ __('general.delete') }}
                                        </button>
                                    </div>
                                    <form id="delete-invoice-form-{{ $invoice->id }}" action="{{ route('admin.foreign-companies.invoices.destroy', [$foreignCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
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
                    <p class="text-muted mb-3">{{ __('invoices.no_invoices_yet') }}</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> {{ __('invoices.add_invoice') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.reject', $foreignCompany) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('companies.reject') }} {{ __('companies.foreign_company') }}</h5>
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

<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.approve', $foreignCompany) }}" method="POST" class="approve-form">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">{{ __('companies.accept') }} {{ __('companies.foreign_company') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('companies.approve_foreign_confirm') }}</p>
                    @if($foreignCompany->is_pre_registered)
                    <div class="alert alert-warning py-2">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>{{ __('companies.rep_marked_pre_registered') }}</strong>
                        @if($foreignCompany->pre_registration_number)
                            <br><small>{{ __('general.registration_number') }}: <strong>{{ $foreignCompany->pre_registration_number }}</strong></small>
                        @endif
                        @if($foreignCompany->pre_registration_year)
                            <br><small>{{ __('companies.reg_year') }}: <strong>{{ $foreignCompany->pre_registration_year }}</strong></small>
                        @endif
                    </div>
                    @endif
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">{{ __('companies.meeting_number') }}</label>
                        <input type="text" name="meeting_number" class="form-control" placeholder="{{ __('companies.meeting_number_example') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('companies.meeting_date') }}</label>
                        <input type="date" name="meeting_date" class="form-control">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_pre_registered" value="1" id="isPreRegistered" {{ $foreignCompany->is_pre_registered ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPreRegistered">{{ __('companies.pre_registered_before_system') }}</label>
                        </div>
                    </div>
                    <div id="preRegistrationFields" style="{{ $foreignCompany->is_pre_registered ? '' : 'display:none;' }}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.reg_year') }} <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_year" class="form-control" min="1990" max="{{ date('Y') }}" placeholder="{{ __('companies.reg_year_example') }}" value="{{ $foreignCompany->pre_registration_year }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.serial_number') }} <span class="text-danger">*</span></label>
                                @php $existingForeignSeq = $foreignCompany->pre_registration_number ? (int) last(explode('-', $foreignCompany->pre_registration_number)) : ''; @endphp
                                <input type="number" name="pre_registration_sequence" class="form-control" min="1" placeholder="{{ __('companies.serial_example') }}" value="{{ $existingForeignSeq }}">
                            </div>
                        </div>
                        <div class="alert alert-light py-2">
                            <small>{{ __('companies.reg_number_display') }} <strong id="preRegPreview">-</strong></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('companies.confirm_accept') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.invoices.store', $foreignCompany) }}" method="POST">
                @csrf
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-plus me-2"></i>{{ __('invoices.add_invoice') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.invoice_description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="invoice_description" class="form-control" placeholder="{{ __('invoices.description_placeholder') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.invoice_amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="invoice_amount" class="form-control" step="0.01" min="0" required>
                            <span class="input-group-text">{{ __('general.currency') }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.due_date') }}</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('general.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('invoices.notes_optional') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i>{{ __('invoices.add_invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cgmpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="ti ti-certificate me-2"></i>{{ __('companies.cgmp_certificate') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($foreignCompany->cgmp_certificate_path)
                    <div class="alert alert-success d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <i class="ti ti-circle-check me-2 f-20"></i>
                            <strong>{{ __('companies.cgmp_uploaded') }}</strong>
                            <br>
                            <small class="text-muted">{{ $foreignCompany->cgmp_certificate_name }}</small>
                            <br>
                            <small class="text-muted">{{ $foreignCompany->cgmp_uploaded_at?->format('Y-m-d H:i') }}</small>
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" data-file-url="{{ Storage::url($foreignCompany->cgmp_certificate_path) }}" data-file-name="{{ $foreignCompany->cgmp_certificate_name }}" data-download-url="{{ route('admin.foreign-companies.cgmp-download', $foreignCompany) }}">
                                <i class="ti ti-eye me-1"></i>{{ __('general.view') }}
                            </button>
                            <a href="{{ route('admin.foreign-companies.cgmp-download', $foreignCompany) }}" class="btn btn-sm btn-outline-success">
                                <i class="ti ti-download me-1"></i>{{ __('general.download') }}
                            </a>
                            <form action="{{ route('admin.foreign-companies.cgmp-delete', $foreignCompany) }}" method="POST" class="cgmp-delete-form d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="ti ti-trash me-1"></i>{{ __('general.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.foreign-companies.cgmp-upload', $foreignCompany) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ $foreignCompany->cgmp_certificate_path ? __('companies.cgmp_replace') : __('companies.cgmp_upload') }} <span class="text-danger">*</span></label>
                        <input type="file" name="cgmp_certificate" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">{{ __('companies.cgmp_file_note') }}</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-upload me-1"></i>{{ $foreignCompany->cgmp_certificate_path ? __('companies.cgmp_replace') : __('companies.cgmp_upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectReceiptForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ti ti-x me-2"></i>{{ __('invoices.reject_receipt_modal') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>{{ __('general.warning') }}:</strong> {{ __('invoices.reject_receipt_warning') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="receipt_rejection_reason" class="form-control" rows="4" required placeholder="{{ __('invoices.rejection_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i>{{ __('general.confirm') }} {{ __('invoices.reject_receipt') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(in_array($foreignCompany->status, ['active', 'expired']))
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.foreign-companies.suspend', $foreignCompany) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ti ti-ban me-2"></i>{{ __('companies.suspend') }} {{ __('companies.foreign_company') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('companies.suspend_confirm_msg') }} <strong>{{ $foreignCompany->company_name }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('companies.suspension_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="suspension_reason" class="form-control" rows="3" required minlength="10" placeholder="{{ __('companies.suspension_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-ban me-1"></i>{{ __('companies.suspend') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const tabKey = 'foreignCompanyTab_{{ $foreignCompany->id }}';
const savedTab = sessionStorage.getItem(tabKey);
if (savedTab) {
    const tabButton = document.querySelector('[data-bs-target="' + savedTab + '"]');
    if (tabButton) {
        const tab = new bootstrap.Tab(tabButton);
        tab.show();
    }
}

document.querySelectorAll('#companyTabs button[data-bs-toggle="tab"]').forEach(function(tabButton) {
    tabButton.addEventListener('shown.bs.tab', function(e) {
        sessionStorage.setItem(tabKey, e.target.getAttribute('data-bs-target'));
    });
});

document.querySelector('.restore-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '{{ __("companies.return_review") }}',
        text: '{{ __("companies.return_review_confirm") }}',
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
        const companyId = this.getAttribute('data-company-id');
        Swal.fire({
            title: '{{ __("invoices.approve_receipt_title") }}',
            text: '{{ __("invoices.approve_receipt_msg") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("invoices.yes_approve") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("admin/foreign-companies") }}/' + companyId + '/invoices/' + invoiceId + '/approve-receipt';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

document.querySelectorAll('.btn-reject-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/foreign-companies/" . $foreignCompany->id . "/invoices") }}/' + invoiceId + '/reject-receipt';
        document.getElementById('receipt_rejection_reason').value = '';

        new bootstrap.Modal(document.getElementById('rejectReceiptModal')).show();
    });
});

document.querySelector('.cgmp-delete-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '{{ __("companies.cgmp_delete_confirm") }}',
        text: '{{ __("companies.cgmp_delete_msg") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("general.yes_delete") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
});

document.getElementById('isPreRegistered')?.addEventListener('change', function() {
    document.getElementById('preRegistrationFields').style.display = this.checked ? '' : 'none';
});

function updatePreRegPreview() {
    const year = document.querySelector('input[name="pre_registration_year"]')?.value;
    const seq = document.querySelector('input[name="pre_registration_sequence"]')?.value;
    const preview = document.getElementById('preRegPreview');
    if (preview) {
        preview.textContent = (year && seq) ? year + '-' + seq : '-';
    }
}
document.querySelector('input[name="pre_registration_year"]')?.addEventListener('input', updatePreRegPreview);
document.querySelector('input[name="pre_registration_sequence"]')?.addEventListener('input', updatePreRegPreview);

document.querySelectorAll('.btn-delete-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const invoiceNumber = this.getAttribute('data-number');
        Swal.fire({
            title: '{{ __("general.confirm_delete") }}',
            text: '{{ __("invoices.delete_invoice_confirm") }} ' + invoiceNumber,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("general.yes_delete") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-invoice-form-' + invoiceId).submit();
            }
        });
    });
});
</script>
@endpush
