@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('companies.company_details') . ': ' . $localCompany->company_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.local-companies.index') }}">{{ __('companies.local_companies') }}</a></li>
    <li class="breadcrumb-item active">{{ $localCompany->company_name }}</li>
@endsection

@section('content')
<div class="show-header mt-3 mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-2"><i class="ti ti-building-skyscraper me-2 text-primary"></i>{{ $localCompany->company_name }}</h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-{{ $localCompany->company_type == 'distributor' ? 'info' : 'primary' }}">{{ $localCompany->company_type_name }}</span>
                <span class="badge bg-{{ $localCompany->status_color }}">{{ $localCompany->status_name }}</span>
                @if($localCompany->registration_number)
                    <span class="badge bg-dark">{{ __('companies.reg_number_display') }} {{ $localCompany->registration_number }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($localCompany->status == 'pending')
                @php
                    $missingDocs = $localCompany->getMissingDocuments();
                    $hasUnpaidInvoices = $localCompany->hasUnpaidInvoices();
                    $canApprove = count($missingDocs) == 0 && !$hasUnpaidInvoices;
                @endphp
                @if($canApprove)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="ti ti-check me-1"></i>{{ __('companies.accept') }}
                    </button>
                @else
                    @php
                        $disabledReason = count($missingDocs) > 0 ? __('companies.must_upload_all_docs_short') : __('companies.unpaid_invoices');
                    @endphp
                    <button type="button" class="btn btn-success" disabled title="{{ $disabledReason }}">
                        <i class="ti ti-check me-1"></i>{{ __('companies.accept') }}
                    </button>
                @endif
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>{{ __('companies.reject') }}
                </button>
            @elseif($localCompany->status == 'rejected')
                <form action="{{ route('admin.local-companies.restore-pending', $localCompany) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.return_review') }}</button>
                </form>
            @elseif(in_array($localCompany->status, ['approved', 'active']))
                <a href="{{ route('admin.local-companies.certificate', $localCompany) }}" class="btn btn-info" target="_blank">
                    <i class="ti ti-certificate me-1"></i>{{ __('companies.print_certificate') }}
                </a>
                @if(in_array($localCompany->status, ['active']))
                    <form action="{{ route('admin.local-companies.request-renewal', $localCompany) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.renewal_request') }}</button>
                    </form>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                        <i class="ti ti-ban me-1"></i>{{ __('companies.suspend') }}
                    </button>
                @endif
            @elseif($localCompany->status == 'expired')
                <form action="{{ route('admin.local-companies.request-renewal', $localCompany) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>{{ __('companies.renewal_request') }}</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                    <i class="ti ti-ban me-1"></i>{{ __('companies.suspend') }}
                </button>
            @elseif($localCompany->status == 'suspended')
                <form action="{{ route('admin.local-companies.unsuspend', $localCompany) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="ti ti-player-play me-1"></i>{{ __('companies.unsuspend') }}</button>
                </form>
            @endif
            <a href="{{ route('admin.local-companies.edit', $localCompany) }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>{{ __('general.edit') }}</a>
            <a href="{{ route('admin.local-companies.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-right me-1"></i>{{ __('general.back') }}</a>
        </div>
    </div>
</div>



@if($localCompany->status == 'active' && $localCompany->expires_at && $localCompany->isExpired())
@php $expiredDays = (int) abs(now()->diffInDays($localCompany->expires_at)); @endphp
<div class="alert alert-danger d-flex align-items-center justify-content-between">
    <div>
        <i class="ti ti-alert-octagon me-2 fs-4"></i>
        <strong>{{ __('general.warning') }}:</strong> {{ __('companies.expiry_warning') }} <strong>{{ $expiredDays }} {{ __('general.day') }}</strong> ({{ $localCompany->expires_at->format('Y-m-d') }}).
        {{ __('companies.must_create_renewal') }}
    </div>
    <form action="{{ route('admin.local-companies.request-renewal', $localCompany) }}" method="POST" class="d-inline ms-3">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm"><i class="ti ti-refresh me-1"></i>{{ __('companies.renew_now') }}</button>
    </form>
</div>
@elseif($localCompany->status == 'active' && $localCompany->expires_at && !$localCompany->isExpired())
@php $daysUntilExpiry = (int) now()->diffInDays($localCompany->expires_at, false); @endphp
@if($daysUntilExpiry <= 90)
<div class="alert alert-warning">
    <i class="ti ti-clock-exclamation me-2"></i>
    <strong>{{ __('general.warning') }}:</strong> {{ __('companies.expiry_soon_warning') }} <strong>{{ $daysUntilExpiry }} {{ __('general.day') }}</strong> ({{ $localCompany->expires_at->format('Y-m-d') }}).
</div>
@endif
@endif

@if($localCompany->status == 'expired')
<div class="alert alert-danger">
    <i class="ti ti-alert-octagon me-2 fs-4"></i>
    <strong>{{ __('companies.company_expired') }}.</strong> {{ __('companies.must_create_renewal_invoice') }}
</div>
@endif

@if($localCompany->status == 'suspended' && $localCompany->suspension_reason)
<div class="alert alert-secondary">
    <i class="ti ti-ban me-2"></i>
    <strong>{{ __('companies.company_suspended_reason') }}:</strong> {{ $localCompany->suspension_reason }}
</div>
@endif

@if($localCompany->status == 'rejected' && $localCompany->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>{{ __('companies.rejection_reason') }}:</strong> {{ $localCompany->rejection_reason }}
</div>
@endif

@if($localCompany->status == 'pending' && count($localCompany->getMissingDocuments()) > 0)
<div class="alert alert-warning">
    <strong><i class="ti ti-alert-triangle me-1"></i>{{ __('companies.missing_documents') }}:</strong>
    <ul class="mb-0 mt-2">
        @foreach($localCompany->getMissingDocuments() as $type => $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($localCompany->status == 'pending' && $localCompany->hasUnpaidInvoices())
<div class="alert alert-danger">
    <strong><i class="ti ti-cash me-1"></i>{{ __('companies.unpaid_invoices') }}:</strong>
    {{ __('companies.has_unpaid') }} {{ $localCompany->invoices()->where('status', 'unpaid')->count() }} {{ __('companies.unpaid_total') }} {{ number_format($localCompany->getUnpaidInvoicesTotal(), 2) }} {{ __('general.currency') }}
    <a href="#tab-invoices" class="alert-link" onclick="document.querySelector('[data-bs-target=\'#tab-invoices\']').click()">{{ __('companies.view_invoices') }}</a>
</div>
@endif

<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="companyTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-company"><i class="ti ti-building me-1"></i>{{ __('companies.company_data') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-license"><i class="ti ti-license me-1"></i>{{ __('companies.license_data') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-manager"><i class="ti ti-user me-1"></i>{{ __('companies.manager_data') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-representative"><i class="ti ti-id me-1"></i>{{ __('companies.company_representative') }}</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>{{ __('companies.files') }}
                    <span class="badge {{ $localCompany->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }} rounded-pill ms-1">
                        {{ $localCompany->documents->count() }}/{{ count(\App\Models\LocalCompanyDocument::requiredDocumentTypes()) }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                    <i class="ti ti-file-invoice me-1"></i>{{ __('invoices.invoices') }}
                    @if($localCompany->hasUnpaidInvoices())
                        <span class="badge bg-danger rounded-pill ms-1">{{ $localCompany->invoices()->where('status', 'unpaid')->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activities">
                    <i class="ti ti-history me-1"></i>{{ __('companies.record') }}
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-company">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('companies.company_name') }}</th><td>{{ $localCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.company_type') }}</th><td>{{ $localCompany->company_type_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.email') }}</th><td>{{ $localCompany->email }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.phone') }}</th><td dir="ltr" class="text-end">{{ $localCompany->phone }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.mobile') }}</th><td dir="ltr" class="text-end">{{ $localCompany->mobile ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('general.address') }}</th><td>{{ $localCompany->company_address ?? '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.street') }}</th><td>{{ $localCompany->street ?? '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('general.city') }}</th><td>{{ $localCompany->city }}</td></tr>
                        </table>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="ti ti-certificate me-2"></i>{{ __('companies.registration_details') }}</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <tr>
                                        <th class="bg-light" width="20%">{{ __('general.registration_number') }}</th>
                                        <td width="30%">
                                            @if($localCompany->registration_number)
                                                <span class="fw-bold text-primary fs-6">{{ $localCompany->registration_number }}</span>
                                            @else
                                                <span class="text-muted">{{ __('general.not_issued_yet') }}</span>
                                            @endif
                                        </td>
                                        <th class="bg-light" width="20%">{{ __('general.registration_date') }}</th>
                                        <td width="30%">{{ $localCompany->registration_date?->format('Y-m-d') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('companies.last_renewal_date') }}</th>
                                        <td>{{ $localCompany->last_renewal_date?->format('Y-m-d') ?? '-' }}</td>
                                        <th class="bg-light">{{ __('companies.expiry_date') }}</th>
                                        <td>
                                            @if($localCompany->expires_at)
                                                @php
                                                    $daysLeft = (int) now()->diffInDays($localCompany->expires_at, false);
                                                @endphp
                                                {{ $localCompany->expires_at->format('Y-m-d') }}
                                                @if($localCompany->isExpired())
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
                </div>

                @if($localCompany->latitude && $localCompany->longitude)
                <div class="mt-3">
                    <h6><i class="ti ti-map-pin me-1"></i>{{ __('companies.company_location') }}</h6>
                    <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #d1d5db;"></div>
                </div>
                @endif

                @if($localCompany->is_pre_registered)
                <div class="alert alert-info mt-3">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>{{ __('companies.previously_registered') }}</strong>
                    @if($localCompany->pre_registration_number)
                        <br>
                        <small>{{ __('companies.reg_number_display') }} <strong>{{ $localCompany->pre_registration_number }}</strong></small>
                    @endif
                    @if($localCompany->pre_registration_year)
                        <br>
                        <small>{{ __('companies.reg_year') }}: <strong>{{ $localCompany->pre_registration_year }}</strong></small>
                    @endif
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-license">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('companies.license_type') }}</th><td>{{ $localCompany->license_type_name }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.license_specialty') }}</th><td>{{ $localCompany->license_specialty_name }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-license me-2"></i>{{ __('companies.official_licenses') }}</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">{{ __('companies.license_number') }}</th><td>{{ $localCompany->license_number ?? '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.issuing_authority') }}</th><td>{{ $localCompany->license_issuer ?? '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.food_drug_reg') }}</th><td>{{ $localCompany->food_drug_registration_number ?? '-' }}</td></tr>
                            <tr><th class="bg-light">{{ __('companies.chamber_commerce_reg') }}</th><td>{{ $localCompany->chamber_of_commerce_number ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-manager">
                <table class="table table-striped info-table">
                    <tr>
                        <th class="bg-light" width="15%">{{ __('general.name') }}</th>
                        <td width="35%">{{ $localCompany->manager_name }}</td>
                        <th class="bg-light" width="15%">{{ __('companies.manager_position_short') }}</th>
                        <td width="35%">{{ $localCompany->manager_position ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">{{ __('general.phone') }}</th>
                        <td dir="ltr" class="text-end">{{ $localCompany->manager_phone }}</td>
                        <th class="bg-light">{{ __('general.email') }}</th>
                        <td>{{ $localCompany->manager_email ?? '-' }}</td>
                    </tr>
                    @if($localCompany->user_id)
                    <tr>
                        <th class="bg-light">{{ __('companies.verification_status') }}</th>
                        <td colspan="3"><span class="badge bg-success">{{ __('companies.manager_account_note') }}</span></td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="tab-pane fade" id="tab-representative">
                @if($localCompany->representative)
                <div class="table-responsive">
                    <table class="table table-striped info-table">
                        <tr>
                            <th class="bg-light" width="15%">{{ __('general.name') }}</th>
                            <td width="35%">{{ $localCompany->representative->name }}</td>
                            <th class="bg-light" width="15%">{{ __('general.email') }}</th>
                            <td width="35%">{{ $localCompany->representative->email }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.phone') }}</th>
                            <td dir="ltr" class="text-end">{{ $localCompany->representative->phone ?? '-' }}</td>
                            <th class="bg-light">{{ __('general.country') }}</th>
                            <td>{{ $localCompany->representative->nationality ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">{{ __('general.registration_date') }}</th>
                            <td>{{ $localCompany->representative->created_at->format('Y-m-d h:i A') }}</td>
                            <th class="bg-light">{{ __('companies.verification_status') }}</th>
                            <td>
                                @if($localCompany->representative->email_verified_at)
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-folder me-1"></i>
                        {{ __('documents.documents') }}
                        @if($localCompany->hasAllRequiredDocuments())
                            <span class="badge bg-success ms-2"><i class="ti ti-check me-1"></i>{{ __('documents.complete') }}</span>
                        @endif
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="ti ti-upload me-1"></i> {{ __('documents.upload_new') }}
                    </button>
                </div>

                @if($localCompany->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">{{ __('general.view') }}</th>
                                <th width="30%">{{ __('documents.document_type') }}</th>
                                <th width="15%">{{ __('documents.document_name') }}</th>
                                <th width="10%">{{ __('documents.file_size') }}</th>
                                <th width="15%">{{ __('general.date') }}</th>
                                <th width="15%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->documents as $index => $document)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    @if($document->isImage())
                                        <a href="#" class="btn-doc-preview" data-file-url="{{ Storage::url($document->file_path) }}" data-file-name="{{ $document->display_name }}" data-download-url="{{ Storage::url($document->file_path) }}">
                                            <img src="{{ Storage::url($document->file_path) }}" alt="{{ $document->display_name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        </a>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 5px;">
                                            <i class="ti {{ $document->file_icon }} fs-3"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $document->display_name }}</strong>
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
                                        <button type="button" class="btn btn-outline-info btn-doc-preview" data-file-url="{{ Storage::url($document->file_path) }}" data-file-name="{{ $document->original_name ?? $document->display_name }}" data-download-url="{{ route('admin.local-companies.documents.download', [$localCompany, $document]) }}" title="{{ __('general.view') }}">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.local-companies.documents.download', [$localCompany, $document]) }}" class="btn btn-outline-primary" title="{{ __('general.download') }}">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-doc" data-id="{{ $document->id }}" data-name="{{ $document->display_name }}" title="{{ __('general.delete') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $document->id }}" action="{{ route('admin.local-companies.documents.destroy', [$localCompany, $document]) }}" method="POST" style="display: none;">
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
                    <i class="ti ti-folder-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">{{ __('documents.no_documents') }}</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="ti ti-upload me-1"></i> {{ __('documents.upload_first') }}
                    </button>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoices">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('invoices.invoices') }}
                        @if($localCompany->hasUnpaidInvoices())
                            <span class="badge bg-danger ms-2">{{ __('invoices.status_unpaid') }}: {{ number_format($localCompany->getUnpaidInvoicesTotal(), 2) }} {{ __('general.currency') }}</span>
                        @endif
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> {{ __('invoices.invoice') }}
                    </button>
                </div>

                @if($localCompany->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="12%">{{ __('invoices.invoice_number') }}</th>
                                <th width="15%">{{ __('general.type') }}</th>
                                <th width="20%">{{ __('general.description') }}</th>
                                <th width="10%">{{ __('general.amount') }}</th>
                                <th width="10%">{{ __('general.status') }}</th>
                                <th width="13%">{{ __('general.date') }}</th>
                                <th width="20%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>{{ $invoice->type_name }}</td>
                                <td>
                                    {{ $invoice->description }}
                                    @if($invoice->notes)
                                        <br><small class="text-muted">{{ Str::limit($invoice->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> {{ __('general.currency') }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_name }}</span>
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
                                        @if($invoice->receipt_path && $invoice->status == 'pending_review')
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-company-id="{{ $localCompany->id }}">
                                                <i class="ti ti-check me-1"></i>{{ __('invoices.approve_receipt') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                                <i class="ti ti-x me-1"></i>{{ __('invoices.reject_receipt') }}
                                            </button>
                                        @elseif($invoice->status == 'paid')
                                            <button type="button" class="btn btn-outline-warning btn-sm btn-mark-unpaid" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-x me-1"></i>{{ __('invoices.cancel_invoice') }}
                                            </button>
                                        @endif
                                        @if($invoice->receipt_path)
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-doc-preview" data-file-url="{{ Storage::url($invoice->receipt_path) }}" data-file-name="{{ __('invoices.payment_receipt') }}_{{ $invoice->invoice_number }}" data-download-url="{{ route('admin.local-companies.invoices.download-receipt', [$localCompany, $invoice]) }}">
                                                <i class="ti ti-eye me-1"></i>{{ __('general.view') }}
                                            </button>
                                            <a href="{{ route('admin.local-companies.invoices.download-receipt', [$localCompany, $invoice]) }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-download me-1"></i>{{ __('invoices.download_receipt') }}
                                            </a>
                                        @elseif($invoice->status == 'unpaid')
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-upload-receipt" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-upload me-1"></i>{{ __('invoices.upload_receipt') }}
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit-invoice" data-id="{{ $invoice->id }}" data-type="{{ $invoice->type }}" data-description="{{ $invoice->description }}" data-amount="{{ $invoice->amount }}" data-due_date="{{ $invoice->due_date?->format('Y-m-d') }}" data-notes="{{ $invoice->notes }}">
                                            <i class="ti ti-edit me-1"></i>{{ __('general.edit') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-invoice" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                            <i class="ti ti-trash me-1"></i>{{ __('general.delete') }}
                                        </button>
                                    </div>

                                    <form id="mark-paid-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.mark-paid', [$localCompany, $invoice]) }}" method="POST" style="display: none;" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="receipt" id="receipt-input-{{ $invoice->id }}" class="d-none">
                                    </form>
                                    <form id="mark-unpaid-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.mark-unpaid', [$localCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    <form id="delete-invoice-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.destroy', [$localCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <form id="upload-receipt-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.upload-receipt', [$localCompany, $invoice]) }}" method="POST" style="display: none;" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="receipt" id="upload-receipt-input-{{ $invoice->id }}" class="d-none">
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
                        <i class="ti ti-plus me-1"></i> {{ __('invoices.invoice') }}
                    </button>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-activities">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-history me-1"></i>
                    {{ __('invoices.timeline') }}
                </h6>

                @if($localCompany->activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="45%">{{ __('general.description') }}</th>
                                <th width="25%">{{ __('general.date') }}</th>
                                <th width="25%">{{ __('general.by') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->activities as $index => $activity)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $activity->action_color }}">
                                        <i class="ti {{ $activity->action_icon }}"></i>
                                    </span>
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    <small>{{ $activity->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $activity->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($activity->user)
                                        <small>{{ $activity->user->name }}</small>
                                    @else
                                        <small class="text-muted">{{ __('companies.system_reg_date') }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-history-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">{{ __('general.no_results') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.approve', $localCompany) }}" method="POST" class="approve-form">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">{{ __('companies.accept') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('companies.confirm_register_msg') }}</p>
                    @if($localCompany->is_pre_registered)
                    <div class="alert alert-warning py-2">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>{{ __('companies.previously_registered') }}</strong>
                        @if($localCompany->pre_registration_number)
                            <br><small>{{ __('companies.reg_number_display') }} <strong>{{ $localCompany->pre_registration_number }}</strong></small>
                        @endif
                        @if($localCompany->pre_registration_year)
                            <br><small>{{ __('companies.reg_year') }}: <strong>{{ $localCompany->pre_registration_year }}</strong></small>
                        @endif
                    </div>
                    @endif
                    <hr>
                    @php
                        $isPreReg = $localCompany->is_pre_registered;
                        $existingSeq = $localCompany->pre_registration_number ? (int) last(explode('-', $localCompany->pre_registration_number)) : '';
                    @endphp
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_pre_registered" value="1" id="isPreRegistered" {{ $isPreReg ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPreRegistered">{{ __('companies.previously_registered') }}</label>
                        </div>
                    </div>
                    <div id="preRegistrationFields" style="{{ $isPreReg ? '' : 'display:none;' }}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.reg_year') }} <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_year" class="form-control" min="1990" max="{{ date('Y') }}" placeholder="{{ __('companies.reg_year_example') }}" value="{{ $localCompany->pre_registration_year }}" {{ $isPreReg ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('companies.serial_number') }} <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_sequence" class="form-control" min="1" placeholder="{{ __('companies.serial_example') }}" value="{{ $existingSeq }}" {{ $isPreReg ? 'required' : '' }}>
                            </div>
                        </div>
                        <div class="alert alert-light py-2">
                            <small>{{ __('companies.reg_number_display') }} <strong id="localPreRegPreview">-</strong></small>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="create_renewal_invoice" value="1" id="createRenewalInvoice">
                                <label class="form-check-label" for="createRenewalInvoice">{{ __('companies.renewal_request') }}</label>
                            </div>
                        </div>
                        <div id="renewalDateField">
                            <div class="mb-3">
                                <label class="form-label">{{ __('companies.last_renewal_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="last_renewal_date" class="form-control" max="{{ date('Y-m-d') }}" {{ $isPreReg ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('general.confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.reject', $localCompany) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('companies.reject') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __('companies.rejection_reason') }} <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('general.confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.documents.store', $localCompany) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-upload me-2"></i>{{ __('documents.upload_new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.document_type') }} <span class="text-danger">*</span></label>
                        <select name="document_type" id="document_type" class="form-select" required>
                            <option value="">{{ __('documents.select_type') }}</option>
                            @php
                                $uploadedTypes = $localCompany->documents->pluck('document_type')->toArray();
                            @endphp
                            @foreach(\App\Models\LocalCompanyDocument::documentTypes() as $key => $value)
                                @php
                                    $isUploaded = in_array($key, $uploadedTypes) && $key != 'other';
                                @endphp
                                <option value="{{ $key }}" {{ $isUploaded ? 'disabled' : '' }}>
                                    {{ $value }}
                                    @if($isUploaded) ({{ __('documents.already_uploaded') }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="custom_name_wrapper">
                        <label class="form-label">{{ __('documents.document_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="custom_name" id="custom_name" class="form-control" placeholder="{{ __('documents.enter_name') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.file') }} <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                        <small class="text-muted">{{ __('documents.file_limit') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('documents.notes_optional') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i>{{ __('documents.upload_document') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.invoices.store', $localCompany) }}" method="POST">
                @csrf
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-plus me-2"></i>{{ __('invoices.invoice') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('general.description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="invoice_description" class="form-control" placeholder="{{ __('invoices.description_placeholder') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('general.amount') }} <span class="text-danger">*</span></label>
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
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('documents.notes_optional') }}"></textarea>
                    </div>

                    <input type="hidden" name="type" value="other">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i>{{ __('general.add') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editInvoiceForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>{{ __('invoices.edit_invoice') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('general.description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="edit_invoice_description" class="form-control" placeholder="{{ __('invoices.description_placeholder') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('general.amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="edit_invoice_amount" class="form-control" step="0.01" min="0" required>
                            <span class="input-group-text">{{ __('general.currency') }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.due_date') }}</label>
                        <input type="date" name="due_date" id="edit_invoice_due_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('general.notes') }}</label>
                        <textarea name="notes" id="edit_invoice_notes" class="form-control" rows="2" placeholder="{{ __('documents.notes_optional') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('general.save_changes') }}</button>
                </div>
            </form>
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
                        <strong>{{ __('general.warning') }}:</strong> {{ __('invoices.receipt_rejected') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('invoices.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="receipt_rejection_reason" class="form-control" rows="4" required placeholder="{{ __('invoices.rejection_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i>{{ __('general.confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(in_array($localCompany->status, ['active', 'expired']))
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.local-companies.suspend', $localCompany) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ti ti-ban me-2"></i>{{ __('companies.suspend') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('companies.company_suspended_reason') }} <strong>{{ $localCompany->company_name }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('companies.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="suspension_reason" class="form-control" rows="3" required></textarea>
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

@push('styles')
@if($localCompany->latitude && $localCompany->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endif
@endpush

@push('scripts')
@if($localCompany->latitude && $localCompany->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([{{ $localCompany->latitude }}, {{ $localCompany->longitude }}], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([{{ $localCompany->latitude }}, {{ $localCompany->longitude }}]).addTo(map);
    });
</script>
@endif
<script>
const tabKey = 'localCompanyTab_{{ $localCompany->id }}';
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

document.getElementById('isPreRegistered')?.addEventListener('change', function() {
    document.getElementById('preRegistrationFields').style.display = this.checked ? '' : 'none';
    var yearInput = document.querySelector('input[name="pre_registration_year"]');
    var seqInput = document.querySelector('input[name="pre_registration_sequence"]');
    var dateInput = document.querySelector('input[name="last_renewal_date"]');
    if (yearInput) yearInput.required = this.checked;
    if (seqInput) seqInput.required = this.checked;
    if (!this.checked) {
        document.getElementById('createRenewalInvoice').checked = false;
        document.getElementById('renewalDateField').style.display = 'none';
        if (dateInput) dateInput.required = false;
    } else {
        if (dateInput) dateInput.required = !document.getElementById('createRenewalInvoice')?.checked;
    }
});

function updateLocalPreRegPreview() {
    const year = document.querySelector('#approveModal input[name="pre_registration_year"]')?.value;
    const seq = document.querySelector('#approveModal input[name="pre_registration_sequence"]')?.value;
    const preview = document.getElementById('localPreRegPreview');
    if (preview) {
        preview.textContent = (year && seq) ? year + '-' + seq : '-';
    }
}
document.querySelector('#approveModal input[name="pre_registration_year"]')?.addEventListener('input', updateLocalPreRegPreview);
document.querySelector('#approveModal input[name="pre_registration_sequence"]')?.addEventListener('input', updateLocalPreRegPreview);

document.getElementById('createRenewalInvoice')?.addEventListener('change', function() {
    var field = document.getElementById('renewalDateField');
    var input = field.querySelector('input[name="last_renewal_date"]');
    field.style.display = this.checked ? 'none' : 'block';
    input.required = !this.checked;
});

document.querySelector('.restore-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '{{ __("companies.return_review") }}',
        text: '{{ __("general.are_you_sure") }}',
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

document.getElementById('document_type')?.addEventListener('change', function() {
    const customNameWrapper = document.getElementById('custom_name_wrapper');
    const customNameInput = document.getElementById('custom_name');
    if (this.value == 'other') {
        customNameWrapper.classList.remove('d-none');
        customNameInput.setAttribute('required', 'required');
    } else {
        customNameWrapper.classList.add('d-none');
        customNameInput.removeAttribute('required');
    }
});

document.querySelectorAll('.btn-delete-doc').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const docId = this.getAttribute('data-id');
        const docName = this.getAttribute('data-name');
        Swal.fire({
            title: '{{ __("general.confirm_delete") }}',
            text: docName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("general.yes_delete") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + docId).submit();
            }
        });
    });
});

document.querySelectorAll('.btn-mark-paid').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        Swal.fire({
            title: '{{ __("general.confirm") }}',
            text: '{{ __("invoices.upload_receipt") }}',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#198754',
            denyButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("general.yes") }}',
            denyButtonText: '{{ __("general.no") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('receipt-input-' + invoiceId).click();
            } else if (result.isDenied) {
                document.getElementById('mark-paid-form-' + invoiceId).submit();
            }
        });
    });
});

document.querySelectorAll('[id^="receipt-input-"]').forEach(function(input) {
    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            this.closest('form').submit();
        }
    });
});

document.querySelectorAll('.btn-mark-unpaid').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        Swal.fire({
            title: '{{ __("invoices.cancel_invoice") }}',
            text: '{{ __("general.are_you_sure") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("general.yes") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('mark-unpaid-form-' + invoiceId).submit();
            }
        });
    });
});

document.querySelectorAll('.btn-upload-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        document.getElementById('upload-receipt-input-' + invoiceId).click();
    });
});

document.querySelectorAll('[id^="upload-receipt-input-"]').forEach(function(input) {
    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            this.closest('form').submit();
        }
    });
});

document.querySelectorAll('.btn-edit-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const description = this.getAttribute('data-description');
        const amount = this.getAttribute('data-amount');
        const dueDate = this.getAttribute('data-due_date');
        const notes = this.getAttribute('data-notes');

        document.getElementById('editInvoiceForm').action = '{{ url("admin/local-companies/" . $localCompany->id . "/invoices") }}/' + invoiceId;
        document.getElementById('edit_invoice_description').value = description;
        document.getElementById('edit_invoice_amount').value = amount;
        document.getElementById('edit_invoice_due_date').value = dueDate || '';
        document.getElementById('edit_invoice_notes').value = notes || '';

        new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
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
                form.action = '{{ url("admin/local-companies") }}/' + companyId + '/invoices/' + invoiceId + '/approve-receipt';

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
        const invoiceNumber = this.getAttribute('data-number');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/local-companies/" . $localCompany->id . "/invoices") }}/' + invoiceId + '/reject-receipt';
        document.getElementById('receipt_rejection_reason').value = '';

        new bootstrap.Modal(document.getElementById('rejectReceiptModal')).show();
    });
});

document.querySelectorAll('.btn-delete-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const invoiceNumber = this.getAttribute('data-number');
        Swal.fire({
            title: '{{ __("general.confirm_delete") }}',
            text: invoiceNumber,
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