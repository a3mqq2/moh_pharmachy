@extends('layouts.app')

@section('title', __('departments.department_members'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <i class="ti ti-users me-2"></i>{{ __('departments.department_members') }}: {{ $department->name }}
                        </h5>
                        @if($department->parent)
                            <small class="text-muted">{{ __('departments.belongs_to') }}: {{ $department->parent->name }}</small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="ti ti-user-plus me-1"></i>{{ __('departments.add_members') }}
                        </button>
                        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-right me-1"></i>{{ __('general.back') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('general.name') }}</th>
                                <th>{{ __('general.email') }}</th>
                                <th>{{ __('general.job_title') }}</th>
                                <th>{{ __('general.role') }}</th>
                                <th>{{ __('general.status') }}</th>
                                <th>{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($department->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td class="fw-bold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->job_title ?? '-' }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-light-primary">{{ $role->display_name ?? $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">{{ __('general.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('general.disabled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.departments.remove-member', [$department, $user]) }}" method="POST" class="remove-form d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('departments.remove_from_dept') }}">
                                            <i class="ti ti-user-minus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('departments.no_members') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($department->children->count() > 0)
                <hr>
                <h6 class="mb-3"><i class="ti ti-folder me-1"></i>{{ __('departments.sub_dept_members') }}</h6>
                @foreach($department->children as $child)
                    @if($child->users->count() > 0)
                    <div class="mb-3">
                        <span class="badge bg-light-warning mb-2">{{ $child->name }} ({{ $child->users->count() }})</span>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($child->users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->job_title ?? '-' }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-light-primary">{{ $role->display_name ?? $role->name }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $availableUsers = $allUsers->where('department_id', null);
@endphp

<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.departments.assign-members', $department) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>{{ __('departments.add_members_to_dept') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($availableUsers->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">{{ __('departments.select_users') }} <span class="text-danger">*</span></label>
                        <select name="user_ids[]" class="form-select select2" multiple required>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>{{ __('departments.all_assigned') }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    @if($availableUsers->count() > 0)
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('general.add') }}</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#addMemberModal').on('shown.bs.modal', function () {
    $(this).find('.select2').select2({
        dropdownParent: $('#addMemberModal'),
        width: '100%',
        placeholder: '{{ __("departments.select_users") }}'
    });
});

document.querySelectorAll('.remove-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ __("departments.remove_member") }}',
            text: '{{ __("departments.remove_confirm") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __("departments.yes_remove") }}',
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