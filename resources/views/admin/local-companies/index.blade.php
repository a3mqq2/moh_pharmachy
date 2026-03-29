@extends('layouts.app')

@php
    $pageTitle = __('companies.local_companies');
    if (request('company_type') == 'distributor') {
        $pageTitle = __('companies.distributor_companies');
    } elseif (request('company_type') == 'supplier') {
        $pageTitle = __('companies.supplier_companies');
    }
@endphp
@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('companies.local_companies') }}</li>
@endsection

@section('content')


<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>{{ $pageTitle }}</h5>
                <span class="badge bg-secondary">{{ $companies->total() }} {{ __('companies.company') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filter') }}
                </button>
                <a href="{{ route('admin.local-companies.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-print me-1"></i> {{ __('general.print_report') }}
                </a>
                <a href="{{ route('admin.local-companies.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('companies.add_new') }}
                </a>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'company_type', 'license_type', 'license_specialty', 'city', 'date_from', 'date_to', 'missing_docs']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('companies.search_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('companies.company_classification') }}</label>
                        <select name="company_type" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach(\App\Models\LocalCompany::companyTypes() as $key => $value)
                                <option value="{{ $key }}" {{ request('company_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach(\App\Models\LocalCompany::statuses() as $key => $value)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('companies.license_type') }}</label>
                        <select name="license_type" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach(\App\Models\LocalCompany::licenseTypes() as $key => $value)
                                <option value="{{ $key }}" {{ request('license_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.specialty') }}</label>
                        <select name="license_specialty" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach(\App\Models\LocalCompany::licenseSpecialties() as $key => $value)
                                <option value="{{ $key }}" {{ request('license_specialty') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.city') }}</label>
                        <input type="text" name="city" class="form-control" placeholder="{{ __('companies.city_name') }}" value="{{ request('city') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.from_date') }}</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.to_date') }}</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="missing_docs" value="1" class="form-check-input" id="missing_docs" {{ request('missing_docs') ? 'checked' : '' }}>
                            <label class="form-check-label" for="missing_docs">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                {{ __('companies.with_missing_docs') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                            </button>
                            @if(request()->hasAny(['search', 'status', 'company_type', 'license_type', 'license_specialty', 'city', 'date_from', 'date_to', 'missing_docs']))
                                <a href="{{ route('admin.local-companies.index') }}" class="btn btn-outline-secondary">
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
                        <th>{{ __('general.classification') }}</th>
                        <th>{{ __('companies.responsible_manager') }}</th>
                        <th>{{ __('general.city') }}</th>
                        <th>{{ __('companies.license') }}</th>
                        <th>{{ __('general.status') }}</th>
                        <th>{{ __('companies.added_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr onclick="window.location='{{ route('admin.local-companies.show', $company) }}'" style="cursor: pointer;">
                        <td>
                            @if($company->registration_number)
                                <span class="badge bg-dark">{{ $company->registration_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-{{ $company->company_type == 'distributor' ? 'info' : 'primary' }}">{{ $company->company_type_name }}</span>
                        </td>
                        <td>
                            <div>{{ $company->manager_name }}</div>
                            <div class="d-flex gap-2 mt-1">
                                <a href="tel:{{ $company->manager_phone }}" class="text-decoration-none" onclick="event.stopPropagation();" title="{{ __('general.call') }}">
                                    <i class="ti ti-phone text-primary"></i>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->manager_phone) }}" target="_blank" class="text-decoration-none" onclick="event.stopPropagation();" title="{{ __('general.whatsapp') }}">
                                    <i class="ti ti-brand-whatsapp text-success"></i>
                                </a>
                                <small class="text-muted" dir="ltr">{{ $company->manager_phone }}</small>
                            </div>
                        </td>
                        <td>{{ $company->city }}</td>
                        <td>
                            <small>{{ $company->license_type_name }}</small><br>
                            <small class="text-muted">{{ $company->license_specialty_name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $company->status_color }}">{{ $company->status_name }}</span>
                        </td>
                        <td>
                            <small>{{ $company->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $company->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-building-store fs-1 d-block mb-2"></i>
                                {{ __('companies.no_companies') }}
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