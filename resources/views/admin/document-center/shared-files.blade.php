@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', __('documents.shared_files'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-share me-2"></i>{{ __('documents.shared_files') }}</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="ti ti-share me-1"></i>{{ __('documents.share_file') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $view == 'sent' ? 'active' : '' }}" href="{{ route('admin.document-center.shared-files', ['view' => 'sent']) }}">
                            <i class="ti ti-send me-1"></i>{{ __('documents.sent') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $view == 'received' ? 'active' : '' }}" href="{{ route('admin.document-center.shared-files', ['view' => 'received']) }}">
                            <i class="ti ti-inbox me-1"></i>{{ __('documents.received') }}
                        </a>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('documents.title') }}</th>
                                <th>{{ __('documents.file_type') }}</th>
                                <th>{{ __('documents.file_size') }}</th>
                                @if($view == 'sent')
                                    <th>{{ __('documents.shared_with') }}</th>
                                @else
                                    <th>{{ __('documents.from') }}</th>
                                @endif
                                <th>{{ __('general.status') }}</th>
                                <th>{{ __('general.date') }}</th>
                                <th>{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($files as $file)
                            <tr>
                                <td>{{ $file->id }}</td>
                                <td>
                                    <div>
                                        <span class="fw-bold">{{ $file->title }}</span>
                                        @if($file->notes)
                                            <br><small class="text-muted">{{ Str::limit($file->notes, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="text-uppercase badge bg-light-secondary">{{ $file->file_extension }}</span></td>
                                <td>{{ $file->file_size_formatted }}</td>
                                @if($view == 'sent')
                                    <td>
                                        @foreach($file->users as $user)
                                            <span class="badge bg-light-info mb-1">
                                                {{ $user->name }}
                                                @if($user->pivot->seen_at)
                                                    <i class="ti ti-checks text-success ms-1" title="{{ __('documents.seen') }} {{ $user->pivot->seen_at }}"></i>
                                                @endif
                                            </span>
                                        @endforeach
                                    </td>
                                @else
                                    <td>{{ $file->sharer->name ?? '-' }}</td>
                                @endif
                                <td>
                                    @if($view == 'received')
                                        @php
                                            $pivot = $file->users->where('id', auth()->id())->first()?->pivot;
                                        @endphp
                                        @if($pivot && $pivot->seen_at)
                                            <span class="badge bg-success"><i class="ti ti-checks me-1"></i>{{ __('documents.seen') }}</span>
                                        @else
                                            <span class="badge bg-warning"><i class="ti ti-eye-off me-1"></i>{{ __('documents.new_file_label') }}</span>
                                        @endif
                                    @else
                                        @php
                                            $seenCount = $file->users->whereNotNull('pivot.seen_at')->count();
                                            $totalCount = $file->users->count();
                                        @endphp
                                        <small>{{ $seenCount }}/{{ $totalCount }} {{ __('documents.viewed_count') }}</small>
                                    @endif
                                </td>
                                <td><small>{{ $file->created_at->format('Y-m-d H:i') }}</small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" data-file-url="{{ Storage::url($file->file_path) }}" data-file-name="{{ $file->original_name }}" data-download-url="{{ route('admin.document-center.shared-files.download', $file) }}" title="{{ __('general.view') }}">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.document-center.shared-files.download', $file) }}" class="btn btn-sm btn-outline-success" title="{{ __('general.download') }}">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        @if($view == 'sent')
                                        <form action="{{ route('admin.document-center.shared-files.destroy', $file) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('general.delete') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    @if($view == 'sent')
                                        {{ __('documents.no_sent_files') }}
                                    @else
                                        {{ __('documents.no_received_files') }}
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $files->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.document-center.shared-files.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-share me-2"></i>{{ __('documents.share_file') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.file') }} <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar">
                        <small class="text-muted">{{ __('documents.max_file_size_20') }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.shared_with') }} <span class="text-danger">*</span></label>
                        <select name="users[]" class="form-select select2" multiple required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('documents.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-share me-1"></i>{{ __('documents.share') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#shareModal').on('shown.bs.modal', function () {
    $(this).find('.select2').select2({
        dropdownParent: $('#shareModal'),
        width: '100%',
        placeholder: '{{ __('documents.select_users') }}'
    });
});

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __('documents.delete_file') }}',
            text: '{{ __('documents.confirm_delete_shared') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __('general.yes_delete') }}',
            cancelButtonText: '{{ __('general.cancel') }}'
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
        title: '{{ __('general.success') }}',
        text: '{{ session('success') }}',
        confirmButtonText: '{{ __('general.ok') }}',
        confirmButtonColor: '#1a5f4a',
        timer: 3000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: '{{ __('general.error') }}',
        text: '{{ session('error') }}',
        confirmButtonText: '{{ __('general.ok') }}',
        confirmButtonColor: '#1a5f4a'
    });
@endif
</script>
@endpush
