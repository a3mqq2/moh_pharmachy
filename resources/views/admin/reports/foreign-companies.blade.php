@extends('layouts.app')

@section('title', __('reports.foreign_companies_report'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('reports.reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('companies.foreign_companies') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-globe-americas me-2"></i>{{ __('reports.foreign_companies_report') }}</h5>
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="columnToggleBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="fas fa-columns me-1"></i> {{ __('reports.customize_columns') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="min-width: 260px; max-height: 400px; overflow-y: auto;" id="columnDropdown">
                        <div class="d-flex gap-2 mb-2 pb-2 border-bottom">
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="toggleAllCols(true)">{{ __('reports.select_all') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="toggleAllCols(false)">{{ __('reports.deselect_all') }}</button>
                        </div>
                        <div id="columnCheckboxes"></div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm" id="printBtn">
                    <i class="fas fa-print me-1"></i> {{ __('general.print') }}
                </button>
                <a href="{{ route('admin.reports.foreign-companies', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm" id="exportBtn">
                    <i class="fas fa-file-excel me-1"></i> {{ __('general.export_excel') }}
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.foreign-companies') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('general.all') }}</option>
                        <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>{{ __('companies.status_uploading_docs') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('companies.status_pending_review') }}</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('companies.status_accepted') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('companies.status_active') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('companies.status_rejected') }}</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('companies.suspended_label') }}</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('companies.expired_label') }}</option>
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
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.origin') }}</label>
                    <input type="text" name="country" class="form-control" value="{{ request('country') }}" placeholder="{{ __('general.country_name_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.foreign-companies') }}" class="btn btn-outline-secondary">
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
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th data-col="0">#</th>
                        <th data-col="1">{{ __('companies.company_name') }}</th>
                        <th data-col="2">{{ __('companies.entity_type') }}</th>
                        <th data-col="3">{{ __('general.origin') }}</th>
                        <th data-col="4">{{ __('general.email') }}</th>
                        <th data-col="5">{{ __('companies.production_line') }}</th>
                        <th data-col="6">{{ __('companies.local_company') }}</th>
                        <th data-col="7">{{ __('companies.representative') }}</th>
                        <th data-col="8">{{ __('general.registration_number') }}</th>
                        <th data-col="9">{{ __('companies.meeting_number') }}</th>
                        <th data-col="10">{{ __('companies.meeting_date') }}</th>
                        <th data-col="11">{{ __('general.status') }}</th>
                        <th data-col="12">{{ __('general.registration_date') }}</th>
                        <th data-col="13">{{ __('companies.expiry_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td data-col="0"><span class="badge bg-dark">{{ method_exists($companies, 'currentPage') ? ($companies->currentPage() - 1) * $companies->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                        <td data-col="1"><strong>{{ $company->company_name }}</strong></td>
                        <td data-col="2">{{ $company->entity_type_name }}</td>
                        <td data-col="3">{{ $company->country }}</td>
                        <td data-col="4">{{ $company->email ?? '-' }}</td>
                        <td data-col="5">{{ $company->activity_type_name }}</td>
                        <td data-col="6">{{ $company->localCompany?->company_name ?? '-' }}</td>
                        <td data-col="7">{{ $company->representative?->full_name ?? '-' }}</td>
                        <td data-col="8">{{ $company->registration_number ?? '-' }}</td>
                        <td data-col="9">{{ $company->meeting_number ?? '-' }}</td>
                        <td data-col="10">{{ $company->meeting_date ? \Carbon\Carbon::parse($company->meeting_date)->format('Y-m-d') : '-' }}</td>
                        <td data-col="11"><span class="badge {{ $company->status_badge_class }}">{{ $company->status_name }}</span></td>
                        <td data-col="12"><small>{{ $company->created_at->format('Y-m-d') }}</small></td>
                        <td data-col="13">
                            @if($company->expires_at)
                                <span class="{{ $company->expires_at->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $company->expires_at->format('Y-m-d') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-globe-americas fs-1 d-block mb-2"></i>
                                {{ __('general.no_results') }}
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($companies, 'hasPages') && $companies->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $companies->withQueryString()->links() }}
        </div>
    </div>
    @endif
    @else
    <div class="card-body text-center py-5">
        <div class="text-muted">
            <i class="fas fa-filter fs-1 d-block mb-3"></i>
            <h5>{{ __('general.use_filters_above') }}</h5>
            <p>{{ __('general.select_status_or_date') }}</p>
        </div>
    </div>
    @endif
</div>

@endsection

@if($filtered)
@push('scripts')
<script>
var STORAGE_KEY = 'report_foreign_cols';
var columns = [
    { index: 0, name: '#', locked: true },
    { index: 1, name: '{{ __("companies.company_name") }}', locked: true },
    { index: 2, name: '{{ __("companies.entity_type") }}' },
    { index: 3, name: '{{ __("general.origin") }}' },
    { index: 4, name: '{{ __("general.email") }}' },
    { index: 5, name: '{{ __("companies.production_line") }}' },
    { index: 6, name: '{{ __("companies.local_company") }}' },
    { index: 7, name: '{{ __("companies.representative") }}' },
    { index: 8, name: '{{ __("general.registration_number") }}' },
    { index: 9, name: '{{ __("companies.meeting_number") }}' },
    { index: 10, name: '{{ __("companies.meeting_date") }}' },
    { index: 11, name: '{{ __("general.status") }}' },
    { index: 12, name: '{{ __("general.registration_date") }}' },
    { index: 13, name: '{{ __("companies.expiry_date") }}' }
];

function getVisibleCols() {
    try {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (saved) return JSON.parse(saved);
    } catch(e) {}
    return columns.map(function(c) { return c.index; });
}

function saveVisibleCols(visible) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
}

function applyColumns() {
    var visible = getVisibleCols();
    columns.forEach(function(col) {
        var show = visible.indexOf(col.index) !== -1 || col.locked;
        document.querySelectorAll('[data-col="' + col.index + '"]').forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
    });
}

function buildCheckboxes() {
    var container = document.getElementById('columnCheckboxes');
    var visible = getVisibleCols();
    var html = '';
    columns.forEach(function(col) {
        if (col.locked) return;
        var checked = visible.indexOf(col.index) !== -1 ? 'checked' : '';
        html += '<div class="form-check mb-1">' +
            '<input class="form-check-input col-check" type="checkbox" value="' + col.index + '" id="col_' + col.index + '" ' + checked + ' onchange="onColChange()">' +
            '<label class="form-check-label small" for="col_' + col.index + '">' + col.name + '</label>' +
            '</div>';
    });
    container.innerHTML = html;
}

function onColChange() {
    var visible = [0, 1];
    document.querySelectorAll('.col-check').forEach(function(cb) {
        if (cb.checked) visible.push(parseInt(cb.value));
    });
    saveVisibleCols(visible);
    applyColumns();
    updateActionUrls();
}

function toggleAllCols(state) {
    document.querySelectorAll('.col-check').forEach(function(cb) {
        cb.checked = state;
    });
    onColChange();
}

buildCheckboxes();
applyColumns();

var baseUrl = '{{ route("admin.reports.foreign-companies") }}';
var baseParams = {!! json_encode(request()->only(['status', 'country', 'from_date', 'to_date'])) !!};

function buildUrl(extra) {
    var params = new URLSearchParams(baseParams);
    for (var k in extra) params.set(k, extra[k]);
    params.set('cols', getVisibleCols().join(','));
    return baseUrl + '?' + params.toString();
}

document.getElementById('printBtn').addEventListener('click', function() {
    window.open(buildUrl({print: 1}), '_blank');
});

document.getElementById('exportBtn').addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = buildUrl({export: 1});
});
</script>
@endpush
@endif
