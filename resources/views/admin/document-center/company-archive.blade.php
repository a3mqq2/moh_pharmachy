@extends('layouts.app')

@section('title', __('documents.company_archive'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item active">{{ __('documents.company_archive') }}</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti ti-archive me-2"></i>{{ __('documents.company_archive') }}</h5>
            <form method="GET" action="{{ route('admin.document-center.company-archive') }}" class="d-flex gap-2 flex-wrap align-items-center">
                <select name="company_type" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $companyType == 'all' ? 'selected' : '' }}>{{ __('documents.all_companies') }}</option>
                    <option value="local" {{ $companyType == 'local' ? 'selected' : '' }}>{{ __('documents.local') }}</option>
                    <option value="foreign" {{ $companyType == 'foreign' ? 'selected' : '' }}>{{ __('documents.foreign') }}</option>
                </select>
                <select name="doc_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $docStatus == 'all' ? 'selected' : '' }}>{{ __('documents.all_statuses') }}</option>
                    <option value="complete" {{ $docStatus == 'complete' ? 'selected' : '' }}>{{ __('documents.docs_complete') }}</option>
                    <option value="incomplete" {{ $docStatus == 'incomplete' ? 'selected' : '' }}>{{ __('documents.docs_incomplete') }}</option>
                    <option value="has_pending" {{ $docStatus == 'has_pending' ? 'selected' : '' }}>{{ __('documents.has_pending') }}</option>
                </select>
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('documents.search_company') }}" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
                </div>
                @if(request('search') || request('company_type') || request('doc_status'))
                <a href="{{ route('admin.document-center.company-archive') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
                </a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        @forelse($companies as $company)
        <div class="company-archive-item border-bottom">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 company-toggle" role="button" data-bs-toggle="collapse" data-bs-target="#company-{{ $company['type'] }}-{{ $company['id'] }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 44px; height: 44px; background: {{ $company['type'] == 'local' ? '#e8f0fe' : '#f3e8ff' }};">
                        <i class="ti {{ $company['type'] == 'local' ? 'ti-building-skyscraper text-primary' : 'ti-world text-purple' }} fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="mb-0 fw-bold">{{ $company['name'] }}</h6>
                            <span class="badge {{ $company['type'] == 'local' ? 'bg-light-primary' : 'bg-light-secondary' }}" style="font-size: 0.7rem;">{{ $company['type_label'] }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <small class="text-muted">
                                <i class="ti ti-files me-1"></i>{{ $company['total_docs'] }} {{ __('documents.document') }}
                            </small>
                            @if($company['pending_docs'] > 0)
                                <span class="badge bg-warning" style="font-size: 0.65rem;">{{ $company['pending_docs'] }} {{ __('documents.pending') }}</span>
                            @endif
                            @if($company['missing_count'] > 0)
                                <span class="badge bg-light-danger text-danger" style="font-size: 0.65rem;">{{ $company['missing_count'] }} {{ __('documents.missing') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center" style="min-width: 120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar {{ $company['completion'] == 100 ? 'bg-success' : ($company['completion'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width: {{ $company['completion'] }}%; border-radius: 4px;"></div>
                            </div>
                            <small class="fw-bold {{ $company['completion'] == 100 ? 'text-success' : ($company['completion'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $company['completion'] }}%</small>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $company['uploaded_required'] }}/{{ $company['required_count'] }} {{ __('documents.required') }}</small>
                    </div>
                    <i class="ti ti-chevron-down text-muted company-chevron"></i>
                </div>
            </div>
            <div class="collapse" id="company-{{ $company['type'] }}-{{ $company['id'] }}">
                <div class="px-3 pb-3">
                    @if($company['documents']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('documents.document_type') }}</th>
                                    <th>{{ __('documents.file_name') }}</th>
                                    <th>{{ __('documents.file_size') }}</th>
                                    <th>{{ __('general.status') }}</th>
                                    <th>{{ __('general.date') }}</th>
                                    <th style="width: 100px;">{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company['documents'] as $doc)
                                <tr>
                                    <td>{{ $doc['id'] }}</td>
                                    <td class="fw-medium">{{ $doc['type_name'] }}</td>
                                    <td><small class="text-muted">{{ Str::limit($doc['original_name'], 35) }}</small></td>
                                    <td>
                                        @if($doc['file_size'])
                                            <small>{{ $doc['file_size'] >= 1048576 ? number_format($doc['file_size'] / 1048576, 1) . ' MB' : number_format($doc['file_size'] / 1024, 1) . ' KB' }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc['status'] == 'pending')
                                            <span class="badge bg-warning">{{ __('documents.status_pending') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('documents.status_approved') }}</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $doc['created_at']->format('Y-m-d') }}</small></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" title="{{ __('general.view') }}"
                                                data-file-url="{{ $doc['file_url'] ?? $doc['download_route'] }}"
                                                data-file-name="{{ $doc['original_name'] ?? $doc['type_name'] }}"
                                                data-download-url="{{ $doc['download_route'] }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            <a href="{{ $doc['download_route'] }}" class="btn btn-sm btn-outline-success" title="{{ __('general.download') }}">
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
                    <div class="text-center text-muted py-3">
                        <i class="ti ti-folder-off fs-3 d-block mb-2"></i>
                        <small>{{ __('documents.no_documents_uploaded') }}</small>
                    </div>
                    @endif
                    <div class="mt-2 text-end">
                        <a href="{{ $company['route'] }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-external-link me-1"></i>{{ __('documents.view_company') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="ti ti-folder-off fs-1 d-block mb-3"></i>
            <h6>{{ __('documents.no_matching_companies') }}</h6>
            <small>{{ __('documents.try_different_search') }}</small>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('css')
<style>
.company-archive-item:last-child { border-bottom: none !important; }
.company-toggle { cursor: pointer; transition: background 0.2s; }
.company-toggle:hover { background: #f8fafc; }
.company-toggle[aria-expanded="true"] .company-chevron { transform: rotate(180deg); }
.company-chevron { transition: transform 0.2s; }
.text-purple { color: #7c3aed; }
.bg-light-primary { background-color: #e8f0fe !important; color: #1a56db !important; }
.bg-light-secondary { background-color: #f3e8ff !important; color: #7c3aed !important; }
.bg-light-danger { background-color: #fef2f2 !important; }
</style>
@endpush
