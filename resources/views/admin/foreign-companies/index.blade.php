@extends('layouts.app')

@section('title', __('companies.foreign_companies'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('companies.foreign_companies') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-globe-americas me-2"></i>{{ __('companies.foreign_companies') }}</h5>
                <span class="badge bg-secondary">{{ $companies->total() }} {{ __('companies.company') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filter') }}
                </button>
                <a href="{{ route('admin.foreign-companies.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-print me-1"></i> {{ __('general.print_report') }}
                </a>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'entity_type', 'activity_type', 'country', 'date_from', 'date_to']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('companies.search_foreign_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>{{ __('companies.status_uploading_docs') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('companies.status_pending_review') }}</option>
                            <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>{{ __('companies.status_pending_payment') }}</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('companies.status_accepted') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('companies.status_active') }}</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('companies.status_rejected') }}</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('companies.status_suspended') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('companies.entity_type') }}</label>
                        <select name="entity_type" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="company" {{ request('entity_type') == 'company' ? 'selected' : '' }}>{{ __('companies.entity_company') }}</option>
                            <option value="factory" {{ request('entity_type') == 'factory' ? 'selected' : '' }}>{{ __('companies.entity_factory') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('companies.activity_type') }}</label>
                        <select name="activity_type" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="medicines" {{ request('activity_type') == 'medicines' ? 'selected' : '' }}>{{ __('companies.activity_medicines') }}</option>
                            <option value="medical_supplies" {{ request('activity_type') == 'medical_supplies' ? 'selected' : '' }}>{{ __('companies.activity_medical_supplies') }}</option>
                            <option value="both" {{ request('activity_type') == 'both' ? 'selected' : '' }}>{{ __('companies.activity_both_short') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.country') }}</label>
                        <input type="text" name="country" class="form-control" placeholder="{{ __('general.country_name_placeholder') }}" value="{{ request('country') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.from_date') }}</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.to_date') }}</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                            </button>
                            @if(request()->hasAny(['search', 'status', 'entity_type', 'activity_type', 'country', 'date_from', 'date_to']))
                                <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> {{ __('general.clear_filters') }}
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
                        <th>{{ __('general.registration_number') }}</th>
                        <th>{{ __('companies.company_name') }}</th>
                        <th>{{ __('general.country') }}</th>
                        <th>{{ __('general.type') }}</th>
                        <th>{{ __('companies.activity_type') }}</th>
                        <th>{{ __('companies.local_company') }}</th>
                        <th>{{ __('documents.documents') }}</th>
                        <th>{{ __('general.status') }}</th>
                        <th>{{ __('general.registration_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr onclick="window.location='{{ route('admin.foreign-companies.show', $company) }}'" style="cursor: pointer;">
                        <td>
                            @if($company->registration_number)
                                <span class="badge bg-dark">{{ $company->registration_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                            @if($company->email)
                                <br><small class="text-muted">{{ $company->email }}</small>
                            @endif
                        </td>
                        <td>{{ $company->country }}</td>
                        <td>
                            <span class="badge bg-{{ $company->entity_type == 'factory' ? 'info' : 'primary' }}">{{ $company->entity_type_name }}</span>
                        </td>
                        <td>{{ $company->activity_type_name }}</td>
                        <td>
                            @if($company->localCompany)
                                <strong>{{ $company->localCompany->company_name }}</strong>
                                @if($company->localCompany->registration_number)
                                    <br><span class="badge bg-dark">{{ $company->localCompany->registration_number }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $company->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }}">
                                {{ $company->documents->count() }} {{ __('general.document') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ str_replace('badge-', 'bg-', $company->status_badge_class) }}">{{ $company->status_name }}</span>
                        </td>
                        <td>
                            <small>{{ $company->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $company->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-globe-americas fs-1 d-block mb-2"></i>
                                {{ __('companies.no_foreign_companies') }}
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($companies->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $companies->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection