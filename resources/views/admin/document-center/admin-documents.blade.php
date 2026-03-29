@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('documents.admin_documents'))

@section('content')
@php
    $hasFilters = request('search') || request('category') || request('visibility') || request('file_type') || request('uploaded_by') || request('date_from') || request('date_to') || request('department_id');
@endphp

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1"><i class="ti ti-files me-2 text-primary"></i>{{ __('documents.admin_documents') }}</h5>
                        <small class="text-muted">{{ __('documents.admin_documents_desc', ['count' => $documents->total()]) }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                            <i class="ti ti-filter me-1"></i>{{ __('general.filters') }}
                            @if($hasFilters)
                                <span class="badge bg-danger rounded-circle p-1 ms-1" style="width:8px;height:8px"></span>
                            @endif
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="ti ti-upload me-1"></i>{{ __('documents.upload_new') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="collapse {{ $hasFilters ? 'show' : '' }}" id="filtersCollapse">
                <div class="card-body border-bottom bg-light py-3">
                    <form method="GET" action="{{ route('admin.document-center.admin-documents') }}" id="filterForm">
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label f-12 text-muted mb-1">{{ __('general.search') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="ti ti-search f-14"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="{{ __('documents.search_placeholder') }}" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('documents.visibility') }}</label>
                                <select name="visibility" class="form-select form-select-sm">
                                    <option value="">{{ __('general.all') }}</option>
                                    @foreach(\App\Models\AdminDocument::visibilityOptions() as $key => $label)
                                        <option value="{{ $key }}" {{ request('visibility') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('documents.file_type') }}</label>
                                <select name="file_type" class="form-select form-select-sm">
                                    <option value="">{{ __('general.all') }}</option>
                                    <option value="pdf" {{ request('file_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="word" {{ request('file_type') == 'word' ? 'selected' : '' }}>Word</option>
                                    <option value="excel" {{ request('file_type') == 'excel' ? 'selected' : '' }}>Excel</option>
                                    <option value="presentation" {{ request('file_type') == 'presentation' ? 'selected' : '' }}>PowerPoint</option>
                                    <option value="image" {{ request('file_type') == 'image' ? 'selected' : '' }}>{{ __('documents.images') }}</option>
                                    <option value="archive" {{ request('file_type') == 'archive' ? 'selected' : '' }}>{{ __('documents.archive') }}</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('documents.uploaded_by') }}</label>
                                <select name="uploaded_by" class="form-select form-select-sm">
                                    <option value="">{{ __('general.all') }}</option>
                                    @foreach($uploaders as $uploader)
                                        <option value="{{ $uploader->id }}" {{ request('uploaded_by') == $uploader->id ? 'selected' : '' }}>{{ $uploader->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mt-1 align-items-end">
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('documents.department') }}</label>
                                <select name="department_id" class="form-select form-select-sm">
                                    <option value="">{{ __('general.all') }}</option>
                                    @foreach($departments->whereNull('parent_id') as $dept)
                                        <optgroup label="{{ $dept->name }}">
                                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                            @foreach($dept->children as $child)
                                                <option value="{{ $child->id }}" {{ request('department_id') == $child->id ? 'selected' : '' }}>&nbsp;↳ {{ $child->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('general.from_date') }}</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label f-12 text-muted mb-1">{{ __('general.to_date') }}</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-lg-6 col-md-3">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-sm btn-primary" type="submit"><i class="ti ti-search me-1"></i>{{ __('general.apply') }}</button>
                                    @if($hasFilters)
                                        <a href="{{ route('admin.document-center.admin-documents') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-x me-1"></i>{{ __('general.clear_filters') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('category') ? 'active' : '' }}" href="{{ route('admin.document-center.admin-documents', request()->except('category', 'page')) }}">
                            {{ __('general.all') }} <span class="badge bg-light-secondary ms-1">{{ $categoryCounts['all'] }}</span>
                        </a>
                    </li>
                    @foreach($categories as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ request('category') == $key ? 'active' : '' }}" href="{{ route('admin.document-center.admin-documents', array_merge(request()->except('page'), ['category' => $key])) }}">
                                {{ $label }} <span class="badge bg-light-secondary ms-1">{{ $categoryCounts[$key] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted f-12" style="width: 50px">#</th>
                                <th class="text-muted f-12">{{ __('documents.document') }}</th>
                                <th class="text-muted f-12">{{ __('documents.category') }}</th>
                                <th class="text-muted f-12">{{ __('documents.visibility') }}</th>
                                <th class="text-muted f-12">{{ __('documents.file_type') }}</th>
                                <th class="text-muted f-12">{{ __('documents.file_size') }}</th>
                                <th class="text-muted f-12">{{ __('documents.uploaded_by') }}</th>
                                <th class="text-muted f-12">{{ __('general.date') }}</th>
                                <th class="text-muted f-12" style="width: 100px">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $document)
                            <tr>
                                <td class="text-muted">{{ $document->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avtar avtar-xs bg-light-primary rounded">
                                            @if(in_array($document->file_extension, ['pdf']))
                                                <i class="ti ti-file-type-pdf text-danger f-16"></i>
                                            @elseif(in_array($document->file_extension, ['doc', 'docx']))
                                                <i class="ti ti-file-type-doc text-primary f-16"></i>
                                            @elseif(in_array($document->file_extension, ['xls', 'xlsx']))
                                                <i class="ti ti-file-spreadsheet text-success f-16"></i>
                                            @elseif(in_array($document->file_extension, ['jpg', 'jpeg', 'png']))
                                                <i class="ti ti-photo text-info f-16"></i>
                                            @else
                                                <i class="ti ti-file text-secondary f-16"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block">{{ $document->title }}</span>
                                            @if($document->notes)
                                                <small class="text-muted f-12">{{ Str::limit($document->notes, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light-primary rounded-pill">{{ $document->category_name }}</span></td>
                                <td>
                                    @if($document->visibility === 'all')
                                        <span class="badge bg-light-success rounded-pill"><i class="ti ti-world me-1"></i>{{ __('documents.visibility_all') }}</span>
                                    @elseif($document->visibility === 'department')
                                        <span class="badge bg-light-info rounded-pill"><i class="ti ti-building me-1"></i>{{ $document->department->name ?? '-' }}</span>
                                    @elseif($document->visibility === 'specific')
                                        <span class="badge bg-light-warning rounded-pill" data-bs-toggle="tooltip" title="{{ $document->authorizedUsers->pluck('name')->join('، ') }}">
                                            <i class="ti ti-users me-1"></i>{{ $document->authorizedUsers->count() }} {{ __('documents.authorized') }}
                                        </span>
                                    @endif
                                </td>
                                <td><span class="text-uppercase badge bg-light-secondary rounded-pill f-10">{{ $document->file_extension }}</span></td>
                                <td class="text-muted f-13">{{ $document->file_size_formatted }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avtar avtar-xs bg-light-secondary rounded-circle">
                                            <i class="ti ti-user f-14"></i>
                                        </div>
                                        <small>{{ $document->uploader->name ?? '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">{{ $document->created_at->format('Y-m-d') }}</small>
                                    <small class="text-muted f-10">{{ $document->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-light-info rounded-circle avtar avtar-xs btn-doc-preview" data-file-url="{{ Storage::url($document->file_path) }}" data-file-name="{{ $document->original_name }}" data-download-url="{{ route('admin.document-center.admin-documents.download', $document) }}" title="{{ __('general.view') }}">
                                            <i class="ti ti-eye f-16"></i>
                                        </button>
                                        <a href="{{ route('admin.document-center.admin-documents.download', $document) }}" class="btn btn-sm btn-light-success rounded-circle avtar avtar-xs" title="{{ __('general.download') }}">
                                            <i class="ti ti-download f-16"></i>
                                        </a>
                                        <form action="{{ route('admin.document-center.admin-documents.destroy', $document) }}" method="POST" class="delete-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger rounded-circle avtar avtar-xs" title="{{ __('general.delete') }}">
                                                <i class="ti ti-trash f-16"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="avtar avtar-l bg-light-secondary rounded-circle mb-3">
                                            <i class="ti ti-file-off f-28 text-muted"></i>
                                        </div>
                                        <h6 class="text-muted mb-1">{{ __('documents.no_documents') }}</h6>
                                        <small class="text-muted">{{ __('documents.upload_to_start') }}</small>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documents->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $documents->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.document-center.admin-documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="ti ti-upload me-2"></i>{{ __('documents.upload_new') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="step-indicator active" data-step="1">
                                <span class="step-number">1</span>
                                <span class="step-label">{{ __('documents.step_doc_info') }}</span>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-indicator" data-step="2">
                                <span class="step-number">2</span>
                                <span class="step-label">{{ __('documents.step_file_visibility') }}</span>
                            </div>
                            <div class="step-connector"></div>
                            <div class="step-indicator" data-step="3">
                                <span class="step-number">3</span>
                                <span class="step-label">{{ __('documents.step_review') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="step-content" id="step1">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">{{ __('documents.doc_title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="docTitle" class="form-control" required placeholder="{{ __('documents.enter_doc_title') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('documents.category') }} <span class="text-danger">*</span></label>
                                <select name="category" id="docCategory" class="form-select" required>
                                    <option value="">{{ __('documents.select_category') }}</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('general.notes') }}</label>
                                <textarea name="notes" id="docNotes" class="form-control" rows="1" placeholder="{{ __('documents.notes_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="step-content d-none" id="step2">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">{{ __('documents.file') }} <span class="text-danger">*</span></label>
                                <div class="upload-zone p-4 text-center rounded border-2 border-dashed" id="uploadZone">
                                    <i class="ti ti-cloud-upload f-36 text-muted mb-2 d-block"></i>
                                    <p class="text-muted mb-1">{{ __('documents.drag_or_click') }}</p>
                                    <small class="text-muted">{{ __('documents.file_types_limit') }}</small>
                                    <input type="file" name="file" id="fileInput" class="d-none" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar">
                                </div>
                                <div class="file-preview d-none mt-2 p-2 bg-light rounded" id="filePreview">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-file f-20 text-primary"></i>
                                            <span id="fileName" class="fw-semibold"></span>
                                            <small class="text-muted" id="fileSize"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">{{ __('documents.visibility') }} <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check card card-body p-3 mb-0 visibility-option active" data-value="all">
                                            <input class="form-check-input d-none" type="radio" name="visibility" id="visAll" value="all" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="visAll">
                                                <div class="text-center">
                                                    <i class="ti ti-world f-24 text-success d-block mb-1"></i>
                                                    <span class="fw-semibold d-block">{{ __('documents.visibility_all') }}</span>
                                                    <small class="text-muted">{{ __('documents.visibility_all_desc') }}</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check card card-body p-3 mb-0 visibility-option" data-value="department">
                                            <input class="form-check-input d-none" type="radio" name="visibility" id="visDept" value="department">
                                            <label class="form-check-label w-100 cursor-pointer" for="visDept">
                                                <div class="text-center">
                                                    <i class="ti ti-building f-24 text-info d-block mb-1"></i>
                                                    <span class="fw-semibold d-block">{{ __('documents.visibility_department') }}</span>
                                                    <small class="text-muted">{{ __('documents.visibility_department_desc') }}</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check card card-body p-3 mb-0 visibility-option" data-value="specific">
                                            <input class="form-check-input d-none" type="radio" name="visibility" id="visSpecific" value="specific">
                                            <label class="form-check-label w-100 cursor-pointer" for="visSpecific">
                                                <div class="text-center">
                                                    <i class="ti ti-lock f-24 text-warning d-block mb-1"></i>
                                                    <span class="fw-semibold d-block">{{ __('documents.visibility_specific') }}</span>
                                                    <small class="text-muted">{{ __('documents.visibility_specific_desc') }}</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3 d-none" id="departmentField">
                                <label class="form-label fw-semibold">{{ __('documents.department') }} <span class="text-danger">*</span></label>
                                <select name="department_id" id="deptSelect" class="form-select">
                                    <option value="">{{ __('documents.select_department') }}</option>
                                    @foreach($departments->whereNull('parent_id') as $dept)
                                        <optgroup label="{{ $dept->name }}">
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @foreach($dept->children as $child)
                                                <option value="{{ $child->id }}">&nbsp;&nbsp;↳ {{ $child->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3 d-none" id="usersField">
                                <label class="form-label fw-semibold">{{ __('documents.authorized_users') }} <span class="text-danger">*</span></label>
                                <select name="authorized_users[]" id="usersSelect" class="form-select select2" multiple>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="step-content d-none" id="step3">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="ti ti-clipboard-check me-1"></i>{{ __('documents.review_data') }}</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">{{ __('documents.doc_title') }}</small>
                                        <span class="fw-semibold" id="reviewTitle">-</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">{{ __('documents.category') }}</small>
                                        <span class="fw-semibold" id="reviewCategory">-</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">{{ __('documents.file') }}</small>
                                        <span class="fw-semibold" id="reviewFile">-</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">{{ __('documents.visibility') }}</small>
                                        <span class="fw-semibold" id="reviewVisibility">-</span>
                                    </div>
                                    <div class="col-12 mb-0" id="reviewNotesRow">
                                        <small class="text-muted d-block">{{ __('general.notes') }}</small>
                                        <span id="reviewNotes">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary d-none" id="prevBtn" onclick="changeStep(-1)">
                        <i class="ti ti-arrow-right me-1"></i>{{ __('general.previous') }}
                    </button>
                    <div></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                            {{ __('general.next') }}<i class="ti ti-arrow-left ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-primary d-none" id="submitBtn">
                            <i class="ti ti-upload me-1"></i>{{ __('documents.upload_document') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 0 0 auto;
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        transition: all 0.3s;
    }
    .step-label {
        font-size: 11px;
        color: #6c757d;
        white-space: nowrap;
        transition: all 0.3s;
    }
    .step-indicator.active .step-number,
    .step-indicator.completed .step-number {
        background: var(--bs-primary, #1a5f4a);
        color: #fff;
    }
    .step-indicator.active .step-label {
        color: var(--bs-primary, #1a5f4a);
        font-weight: 600;
    }
    .step-connector {
        flex: 1;
        height: 2px;
        background: #e9ecef;
        align-self: flex-start;
        margin-top: 16px;
        margin-inline: 8px;
    }
    .upload-zone {
        cursor: pointer;
        border-color: #d1d5db !important;
        transition: all 0.2s;
        background: #fafbfc;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--bs-primary, #1a5f4a) !important;
        background: #f0fdf4;
    }
    .visibility-option {
        cursor: pointer;
        border: 2px solid #e9ecef !important;
        transition: all 0.2s;
    }
    .visibility-option:hover {
        border-color: #c5ccd4 !important;
    }
    .visibility-option.active {
        border-color: var(--bs-primary, #1a5f4a) !important;
        background: #f0fdf4;
    }
    .cursor-pointer { cursor: pointer; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentStep = 1;
const totalSteps = 3;
const categoryLabels = @json($categories);
const visibilityLabels = { all: '{{ __("documents.visibility_all") }}', department: '{{ __("documents.visibility_department") }}', specific: '{{ __("documents.visibility_specific") }}' };

document.querySelectorAll('.visibility-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.visibility-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input[type=radio]').checked = true;

        const val = this.dataset.value;
        document.getElementById('departmentField').classList.toggle('d-none', val !== 'department');
        document.getElementById('usersField').classList.toggle('d-none', val !== 'specific');
    });
});

const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');

uploadZone.addEventListener('click', () => fileInput.click());
uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFilePreview();
    }
});
fileInput.addEventListener('change', showFilePreview);

function showFilePreview() {
    if (fileInput.files.length) {
        const file = fileInput.files[0];
        document.getElementById('fileName').textContent = file.name;
        const sizeMB = (file.size / 1048576).toFixed(1);
        document.getElementById('fileSize').textContent = sizeMB > 1 ? sizeMB + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('filePreview').classList.remove('d-none');
        uploadZone.classList.add('d-none');
    }
}

function removeFile() {
    fileInput.value = '';
    document.getElementById('filePreview').classList.add('d-none');
    uploadZone.classList.remove('d-none');
}

function changeStep(dir) {
    if (dir === 1 && !validateStep(currentStep)) return;

    currentStep += dir;
    currentStep = Math.max(1, Math.min(totalSteps, currentStep));

    document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
    document.getElementById('step' + currentStep).classList.remove('d-none');

    document.querySelectorAll('.step-indicator').forEach((el, i) => {
        el.classList.remove('active', 'completed');
        if (i + 1 === currentStep) el.classList.add('active');
        else if (i + 1 < currentStep) el.classList.add('completed');
    });

    document.getElementById('prevBtn').classList.toggle('d-none', currentStep === 1);
    document.getElementById('nextBtn').classList.toggle('d-none', currentStep === totalSteps);
    document.getElementById('submitBtn').classList.toggle('d-none', currentStep !== totalSteps);

    if (currentStep === 3) populateReview();
}

function validateStep(step) {
    if (step === 1) {
        const title = document.getElementById('docTitle').value.trim();
        const category = document.getElementById('docCategory').value;
        if (!title || !category) {
            Swal.fire({ icon: 'warning', title: '{{ __("general.warning") }}', text: '{{ __("general.fill_required") }}', confirmButtonColor: '#1a5f4a', confirmButtonText: '{{ __("general.ok") }}' });
            return false;
        }
    }
    if (step === 2) {
        if (!fileInput.files.length) {
            Swal.fire({ icon: 'warning', title: '{{ __("general.warning") }}', text: '{{ __("documents.select_file") }}', confirmButtonColor: '#1a5f4a', confirmButtonText: '{{ __("general.ok") }}' });
            return false;
        }
        const vis = document.querySelector('input[name=visibility]:checked').value;
        if (vis === 'department' && !document.getElementById('deptSelect').value) {
            Swal.fire({ icon: 'warning', title: '{{ __("general.warning") }}', text: '{{ __("documents.select_department_required") }}', confirmButtonColor: '#1a5f4a', confirmButtonText: '{{ __("general.ok") }}' });
            return false;
        }
        if (vis === 'specific') {
            const selected = $('#usersSelect').val();
            if (!selected || !selected.length) {
                Swal.fire({ icon: 'warning', title: '{{ __("general.warning") }}', text: '{{ __("documents.select_users_required") }}', confirmButtonColor: '#1a5f4a', confirmButtonText: '{{ __("general.ok") }}' });
                return false;
            }
        }
    }
    return true;
}

function populateReview() {
    document.getElementById('reviewTitle').textContent = document.getElementById('docTitle').value;
    document.getElementById('reviewCategory').textContent = categoryLabels[document.getElementById('docCategory').value] || '-';
    document.getElementById('reviewFile').textContent = fileInput.files.length ? fileInput.files[0].name : '-';
    const vis = document.querySelector('input[name=visibility]:checked').value;
    let visText = visibilityLabels[vis];
    if (vis === 'department') {
        const deptEl = document.getElementById('deptSelect');
        visText += ' - ' + (deptEl.options[deptEl.selectedIndex]?.text || '');
    }
    document.getElementById('reviewVisibility').textContent = visText;
    const notes = document.getElementById('docNotes').value.trim();
    document.getElementById('reviewNotes').textContent = notes || '{{ __("documents.none") }}';
}

$('#uploadModal').on('shown.bs.modal', function () {
    $(this).find('.select2').select2({
        dropdownParent: $('#uploadModal'),
        width: '100%',
        placeholder: '{{ __("documents.select_users") }}'
    });
});

$('#uploadModal').on('hidden.bs.modal', function () {
    currentStep = 1;
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
    document.getElementById('step1').classList.remove('d-none');
    document.querySelectorAll('.step-indicator').forEach((el, i) => {
        el.classList.remove('active', 'completed');
        if (i === 0) el.classList.add('active');
    });
    document.getElementById('prevBtn').classList.add('d-none');
    document.getElementById('nextBtn').classList.remove('d-none');
    document.getElementById('submitBtn').classList.add('d-none');
});

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __("documents.delete_document") }}',
            text: '{{ __("documents.delete_confirm") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __("general.yes_delete") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: '{{ __("general.success") }}',
        text: '{{ session('success') }}',
        confirmButtonText: '{{ __("general.ok") }}',
        confirmButtonColor: '#1a5f4a',
        timer: 3000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: '{{ __("general.error") }}',
        text: '{{ session('error') }}',
        confirmButtonText: '{{ __("general.ok") }}',
        confirmButtonColor: '#1a5f4a'
    });
@endif
</script>
@endpush
