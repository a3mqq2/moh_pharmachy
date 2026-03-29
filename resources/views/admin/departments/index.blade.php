@extends('layouts.app')

@section('title', __('departments.departments'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-sitemap me-2"></i>{{ __('departments.departments') }}</h5>
                    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>{{ __('departments.new_department') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @forelse($departments as $department)
                <div class="card mb-3 border {{ $department->is_active ? 'border-start border-3 border-start-primary' : 'border-start border-3 border-start-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-building fs-4 text-primary"></i>
                                <h5 class="mb-0">{{ $department->name }}</h5>
                                @if(!$department->is_active)
                                    <span class="badge bg-secondary">{{ __('general.disabled') }}</span>
                                @endif
                                <span class="badge bg-light-primary">{{ $department->users->count() }} {{ __('departments.user') }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.departments.members', $department) }}" class="btn btn-sm btn-outline-info" title="{{ __('departments.members') }}">
                                    <i class="ti ti-users"></i>
                                </a>
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-warning" title="{{ __('general.edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('admin.departments.toggle-status', $department) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $department->is_active ? 'secondary' : 'success' }}" title="{{ $department->is_active ? __('departments.deactivate') : __('departments.activate') }}">
                                        <i class="ti ti-{{ $department->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="delete-form d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('general.delete') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($department->description)
                            <p class="text-muted mb-2 small">{{ $department->description }}</p>
                        @endif

                        @if($department->children->count() > 0)
                        <div class="ms-4 mt-3">
                            @foreach($department->children as $child)
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-corner-left-down text-muted"></i>
                                    <i class="ti ti-folder text-warning"></i>
                                    <span class="fw-bold">{{ $child->name }}</span>
                                    @if(!$child->is_active)
                                        <span class="badge bg-secondary">{{ __('general.disabled') }}</span>
                                    @endif
                                    <span class="badge bg-light-info">{{ $child->users->count() }} {{ __('departments.user') }}</span>
                                    @if($child->description)
                                        <small class="text-muted">- {{ Str::limit($child->description, 40) }}</small>
                                    @endif
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.departments.members', $child) }}" class="btn btn-sm btn-outline-info" title="{{ __('departments.members') }}">
                                        <i class="ti ti-users"></i>
                                    </a>
                                    <a href="{{ route('admin.departments.edit', $child) }}" class="btn btn-sm btn-outline-warning" title="{{ __('general.edit') }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.departments.toggle-status', $child) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $child->is_active ? 'secondary' : 'success' }}" title="{{ $child->is_active ? __('departments.deactivate') : __('departments.activate') }}">
                                            <i class="ti ti-{{ $child->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.departments.destroy', $child) }}" method="POST" class="delete-form d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('general.delete') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="ti ti-sitemap fs-1 d-block mb-2"></i>
                    <p>{{ __('departments.no_departments') }}</p>
                    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i>{{ __('departments.create_first') }}
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __("departments.delete_department") }}',
            text: '{{ __("departments.delete_confirm") }}',
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