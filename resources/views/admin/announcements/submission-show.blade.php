@extends('layouts.app')

@section('title', __('announcements.submission_details'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">{{ __('announcements.announcements') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.submissions', $announcement) }}">{{ __('announcements.submissions') }}</a></li>
    <li class="breadcrumb-item active">{{ __('announcements.submission_details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('announcements.representative_info') }}</h6>
            </div>
            <div class="card-body">
                <p><strong>{{ __('general.name') }}:</strong> {{ $submission->representative->full_name ?? '-' }}</p>
                <p><strong>{{ __('general.email') }}:</strong> {{ $submission->representative->email ?? '-' }}</p>
                <p><strong>{{ __('general.phone') }}:</strong> {{ $submission->representative->phone ?? '-' }}</p>
                <p class="mb-0"><strong>{{ __('announcements.submitted_at') }}:</strong> {{ $submission->submitted_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.announcements.submissions', $announcement) }}" class="btn btn-outline-secondary w-100">
            <i class="fas fa-arrow-right me-1"></i>{{ __('announcements.back_to_submissions') }}
        </a>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>{{ __('announcements.submission_data') }}</h6>
            </div>
            <div class="card-body">
                @php $formFields = $announcement->form_fields ?? []; @endphp
                @foreach($formFields as $field)
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label fw-bold text-muted small text-uppercase">
                            {{ $field['label'] }}
                            <span class="badge bg-light text-dark ms-1" style="font-weight: normal; text-transform: none;">{{ __('announcements.field_types.' . $field['type']) }}</span>
                        </label>

                        @if($field['type'] === 'file')
                            @php
                                $file = $submission->files->where('field_name', $field['name'])->first();
                            @endphp
                            @if($file)
                                <div>
                                    <a href="{{ route('admin.announcements.submission-files.download', $file) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i>{{ $file->original_name }}
                                    </a>
                                </div>
                            @else
                                <p class="text-muted">-</p>
                            @endif
                        @elseif($field['type'] === 'checkbox')
                            @php $val = $submission->data[$field['name']] ?? []; @endphp
                            @if(is_array($val) && count($val))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($val as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">-</p>
                            @endif
                        @else
                            <p class="mb-0">{{ $submission->data[$field['name']] ?? '-' }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
