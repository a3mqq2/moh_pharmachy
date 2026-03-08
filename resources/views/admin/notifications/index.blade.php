@extends('layouts.app')

@section('title', 'الإشعارات')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
<li class="breadcrumb-item active">الإشعارات</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">جميع الإشعارات</h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('admin.notifications.mark-all-as-read') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-check"></i> تعليم الكل كمقروء
                        </button>
                    </form>
                    @endif
                    @if(auth()->user()->notifications->count() > 0)
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAll()">
                        <i class="ti ti-trash"></i> حذف الكل
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
                                                {{ $notification->data['message'] ?? 'إشعار جديد' }}
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
                                            <span class="badge bg-danger">جديد</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{ $notification->data['url'] ?? '#' }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-eye"></i> عرض
                                        </a>
                                        @if(!$notification->read_at)
                                        <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti ti-check"></i> تعليم كمقروء
                                            </button>
                                        </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ $notification->id }}')">
                                            <i class="ti ti-trash"></i> حذف
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
                        <h5 class="mt-3 text-muted">لا توجد إشعارات</h5>
                        <p class="text-muted">سيتم عرض الإشعارات هنا عندما تكون هناك تحديثات جديدة</p>
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
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من حذف هذا الإشعار؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + notificationId).submit();
        }
    });
}

function confirmDeleteAll() {
    Swal.fire({
        title: 'تأكيد حذف جميع الإشعارات',
        text: 'هل أنت متأكد من حذف جميع الإشعارات؟ لا يمكن التراجع عن هذا الإجراء.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، احذف الكل',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-all-form').submit();
        }
    });
}
</script>
@endpush
