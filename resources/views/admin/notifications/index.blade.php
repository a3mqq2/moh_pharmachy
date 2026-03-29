@extends('layouts.app')

@section('title', __('notifications.notifications'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('dashboard.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('notifications.notifications') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('notifications.all_notifications') }}</h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('admin.notifications.mark-all-as-read') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-check"></i> {{ __('notifications.mark_all_read') }}
                        </button>
                    </form>
                    @endif
                    @if(auth()->user()->notifications->count() > 0)
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAll()">
                        <i class="ti ti-trash"></i> {{ __('notifications.delete_all') }}
                    </button>
                    <form id="delete-all-form" action="{{ route('admin.notifications.delete-all') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @forelse($notifications as $notification)
                    <div class="card mb-3 {{ $notification->read_at ? 'bg-light' : 'border-primary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="avtar avtar-m bg-light-{{ isset($notification->data['company_type']) && $notification->data['company_type'] == 'local' ? 'primary' : 'success' }}">
                                        <i class="ti {{ $notification->data['icon'] ?? 'ti-bell' }} fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $notification->data['message'] ?? __('notifications.new_notification') }}
                                            </h6>
                                            <p class="text-muted mb-2" style="font-size: 0.875rem;">
                                                <i class="ti ti-clock"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                                <span class="mx-2">•</span>
                                                {{ $notification->created_at->format('Y-m-d H:i') }}
                                            </p>
                                            @if(isset($notification->data['additional_data']))
                                            <div class="mb-2">
                                                @foreach($notification->data['additional_data'] as $key => $value)
                                                <span class="badge bg-light-secondary me-1">
                                                    {{ $key }}: {{ $value }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            @if(!$notification->read_at)
                                            <span class="badge bg-danger">{{ __('notifications.new') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{ $notification->data['url'] ?? '#' }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-eye"></i> {{ __('general.view') }}
                                        </a>
                                        @if(!$notification->read_at)
                                        <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti ti-check"></i> {{ __('notifications.mark_read') }}
                                            </button>
                                        </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ $notification->id }}')">
                                            <i class="ti ti-trash"></i> {{ __('general.delete') }}
                                        </button>
                                        <form id="delete-form-{{ $notification->id }}" action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="ti ti-bell-off" style="font-size: 5rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">{{ __('notifications.no_notifications') }}</h5>
                        <p class="text-muted">{{ __('notifications.no_notifications_desc') }}</p>
                    </div>
                @endforelse

                @if($notifications->hasPages())
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(notificationId) {
    Swal.fire({
        title: '{{ __("notifications.confirm_delete") }}',
        text: '{{ __("notifications.confirm_delete_text") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("general.yes_delete") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + notificationId).submit();
        }
    });
}

function confirmDeleteAll() {
    Swal.fire({
        title: '{{ __("notifications.confirm_delete_all") }}',
        text: '{{ __("notifications.confirm_delete_all_text") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("notifications.yes_delete_all") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-all-form').submit();
        }
    });
}
</script>
@endpush
