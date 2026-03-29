@extends('layouts.app')

@section('title', __('announcements.announcement') . ': ' . $announcement->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">{{ __('announcements.announcements') }}</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($announcement->title, 30) }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1"><i class="fas fa-bullhorn me-2"></i>{{ $announcement->title }}</h5>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-{{ $announcement->priority_color }}">{{ $announcement->priority_name }}</span>
                    <span class="badge bg-{{ $announcement->isForm() ? 'primary' : 'dark' }}">{{ $announcement->isForm() ? __('announcements.type_form') : __('announcements.type_message') }}</span>
                    <span class="badge bg-secondary">{{ $announcement->target_name }}</span>
                    @if($announcement->send_email)
                        @if($announcement->is_sent)
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('announcements.email_sent') }}</span>
                        @else
                            <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>{{ __('announcements.email_sending') }}</span>
                        @endif
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i>{{ __('general.back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="bg-light rounded p-4 mb-4" style="white-space: pre-line; line-height: 1.9;">{{ $announcement->body }}</div>

        @if($announcement->isForm() && $announcement->form_fields)
        <div class="card border-primary mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>{{ __('announcements.form_preview') }}</h6>
                <a href="{{ route('admin.announcements.submissions', $announcement) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-list me-1"></i>{{ __('announcements.view_submissions') }} ({{ $announcement->submissions_count ?? 0 }})
                </a>
            </div>
            <div class="card-body">
                @foreach($announcement->form_fields as $field)
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        {{ $field['label'] }}
                        @if($field['required']) <span class="text-danger">*</span> @endif
                        <span class="badge bg-light text-dark ms-1" style="font-weight: normal;">{{ __('announcements.field_types.' . $field['type']) }}</span>
                    </label>
                    @if($field['type'] === 'text')
                        <input type="text" class="form-control" disabled placeholder="{{ $field['placeholder'] ?? '' }}">
                    @elseif($field['type'] === 'textarea')
                        <textarea class="form-control" rows="2" disabled placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                    @elseif($field['type'] === 'number')
                        <input type="number" class="form-control" disabled placeholder="{{ $field['placeholder'] ?? '' }}">
                    @elseif($field['type'] === 'email')
                        <input type="email" class="form-control" disabled placeholder="{{ $field['placeholder'] ?? '' }}">
                    @elseif($field['type'] === 'date')
                        <input type="date" class="form-control" disabled>
                    @elseif($field['type'] === 'select')
                        <select class="form-select" disabled>
                            <option>{{ $field['placeholder'] ?? '---' }}</option>
                            @foreach($field['options'] ?? [] as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'radio')
                        @foreach($field['options'] ?? [] as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled>
                                <label class="form-check-label">{{ $option }}</label>
                            </div>
                        @endforeach
                    @elseif($field['type'] === 'checkbox')
                        @foreach($field['options'] ?? [] as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled>
                                <label class="form-check-label">{{ $option }}</label>
                            </div>
                        @endforeach
                    @elseif($field['type'] === 'file')
                        <input type="file" class="form-control" disabled>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="row text-muted mb-3 g-2">
            <div class="col-md-3">
                <small><i class="fas fa-user me-1"></i>{{ __('announcements.created_by') }}: {{ $announcement->creator->name ?? '-' }}</small>
            </div>
            <div class="col-md-3">
                <small><i class="fas fa-calendar me-1"></i>{{ __('general.created_at') }}: {{ $announcement->created_at->format('Y-m-d H:i') }}</small>
            </div>
            <div class="col-md-3">
                <small><i class="fas fa-calendar-check me-1"></i>{{ __('announcements.from') }}: {{ $announcement->start_date ? $announcement->start_date->format('Y-m-d') : __('announcements.immediate') }}
                    - {{ __('announcements.to') }}: {{ $announcement->end_date ? $announcement->end_date->format('Y-m-d') : __('announcements.no_limit') }}</small>
            </div>
            <div class="col-md-3">
                <span class="badge bg-{{ $announcement->status_color }}">{{ $announcement->status_label }}</span>
                @if($announcement->sent_at)
                    <small class="ms-1"><i class="fas fa-paper-plane me-1"></i>{{ $announcement->sent_at->format('Y-m-d H:i') }}</small>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2 pt-3 border-top">
            @if($announcement->send_email)
                <form action="{{ route('admin.announcements.resend', $announcement) }}" method="POST" class="resend-form">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-redo me-1"></i>{{ __('announcements.resend_email') }}
                    </button>
                </form>
            @endif
            @can('delete_announcement')
            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash me-1"></i>{{ __('announcements.delete_announcement') }}
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __("announcements.delete_announcement") }}',
            text: '{{ __("announcements.delete_confirm") }}',
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

document.querySelectorAll('.resend-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __("announcements.resend_confirm_title") }}',
            text: '{{ __("announcements.resend_confirm_text") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __("announcements.yes_send") }}',
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
