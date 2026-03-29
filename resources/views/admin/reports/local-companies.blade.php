@extends('layouts.app')

@section('title', __('reports.local_companies_report'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('reports.reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('companies.local_companies') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>{{ __('reports.local_companies_report') }}</h5>
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
                <a href="{{ route('admin.reports.local-companies', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm" id="exportBtn">
                    <i class="fas fa-file-excel me-1"></i> {{ __('general.export_excel') }}
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.local-companies') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('general.all') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('companies.status_pending_review') }}</option>
                        <option value="uploading_documents" {{ request('status') == 'uploading_documents' ? 'selected' : '' }}>{{ __('companies.status_uploading_docs') }}</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('companies.approved_label') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('companies.status_active') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('companies.status_rejected') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.from_date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('general.to_date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.local-companies') }}" class="btn btn-outline-secondary">
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
                        <th data-col="2">{{ __('companies.company_type') }}</th>
                        <th data-col="3">{{ __('general.city') }}</th>
                        <th data-col="4">{{ __('general.phone') }}</th>
                        <th data-col="5">{{ __('general.email') }}</th>
                        <th data-col="6">{{ __('companies.license_type') }}</th>
                        <th data-col="7">{{ __('companies.license_specialty') }}</th>
                        <th data-col="8">{{ __('companies.manager_name') }}</th>
                        <th data-col="9">{{ __('companies.representative') }}</th>
                        <th data-col="10">{{ __('general.status') }}</th>
                        <th data-col="11">{{ __('general.registration_date') }}</th>
                        <th data-col="12">{{ __('companies.expiry_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td data-col="0"><span class="badge bg-dark">{{ method_exists($companies, 'currentPage') ? ($companies->currentPage() - 1) * $companies->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                        <td data-col="1"><strong>{{ $company->company_name }}</strong></td>
                        <td data-col="2">{{ $company->company_type_name }}</td>
                        <td data-col="3">{{ $company->city ?? '-' }}</td>
                        <td data-col="4">{{ $company->phone ?? '-' }}</td>
                        <td data-col="5">{{ $company->email ?? '-' }}</td>
                        <td data-col="6">{{ $company->license_type_name }}</td>
                        <td data-col="7">{{ $company->license_specialty_name }}</td>
                        <td data-col="8">{{ $company->manager_name ?? '-' }}</td>
                        <td data-col="9">{{ $company->representative?->full_name ?? '-' }}</td>
                        <td data-col="10"><span class="badge bg-{{ $company->status_color }}">{{ $company->status_name }}</span></td>
                        <td data-col="11"><small>{{ $company->created_at->format('Y-m-d') }}</small></td>
                        <td data-col="12">
                            @if($company->expires_at)
                                <small class="{{ $company->expires_at->isPast() ? 'text-danger fw-bold' : '' }}">{{ $company->expires_at->format('Y-m-d') }}</small>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-building fs-1 d-block mb-2"></i>
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
var STORAGE_KEY = 'report_local_cols';
var columns = [
    { index: 0, name: '#', locked: true },
    { index: 1, name: '{{ __("companies.company_name") }}', locked: true },
    { index: 2, name: '{{ __("companies.company_type") }}' },
    { index: 3, name: '{{ __("general.city") }}' },
    { index: 4, name: '{{ __("general.phone") }}' },
    { index: 5, name: '{{ __("general.email") }}' },
    { index: 6, name: '{{ __("companies.license_type") }}' },
    { index: 7, name: '{{ __("companies.license_specialty") }}' },
    { index: 8, name: '{{ __("companies.manager_name") }}' },
    { index: 9, name: '{{ __("companies.representative") }}' },
    { index: 10, name: '{{ __("general.status") }}' },
    { index: 11, name: '{{ __("general.registration_date") }}' },
    { index: 12, name: '{{ __("companies.expiry_date") }}' }
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

var baseUrl = '{{ route("admin.reports.local-companies") }}';
var baseParams = {!! json_encode(request()->only(['status', 'from_date', 'to_date'])) !!};

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
