@extends('layouts.app')

@section('title', 'تعميم: ' . $announcement->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">التعميمات</a></li>
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
                    <span class="badge bg-secondary">{{ $announcement->target_name }}</span>
                    @if($announcement->send_email)
                        @if($announcement->is_sent)
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>تم إرسال البريد</span>
                        @else
                            <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>جاري إرسال البريد</span>
                        @endif
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i>رجوع
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="bg-light rounded p-4 mb-4" style="white-space: pre-line; line-height: 1.9;">{{ $announcement->body }}</div>

        <div class="row text-muted mb-3 g-2">
            <div class="col-md-3">
                <small><i class="fas fa-user me-1"></i>بواسطة: {{ $announcement->creator->name ?? '-' }}</small>
            </div>
            <div class="col-md-3">
                <small><i class="fas fa-calendar me-1"></i>تاريخ الإنشاء: {{ $announcement->created_at->format('Y-m-d H:i') }}</small>
            </div>
            <div class="col-md-3">
                <small><i class="fas fa-calendar-check me-1"></i>من: {{ $announcement->start_date ? $announcement->start_date->format('Y-m-d') : 'فوري' }}
                    - إلى: {{ $announcement->end_date ? $announcement->end_date->format('Y-m-d') : 'بدون حد' }}</small>
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
                        <i class="fas fa-redo me-1"></i>إعادة إرسال البريد
                    </button>
                </form>
            @endif
            @can('delete_announcement')
            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash me-1"></i>حذف التعميم
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
            title: 'حذف التعميم',
            text: 'هل أنت متأكد من حذف هذا التعميم؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
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
            title: 'إعادة إرسال التعميم',
            text: 'سيتم إعادة إرسال التعميم عبر البريد الإلكتروني لجميع المسؤولين المستهدفين',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، أرسل',
            cancelButtonText: 'إلغاء'
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
        title: 'تم بنجاح',
        text: '{{ session('success') }}',
        confirmButtonText: 'حسناً',
        confirmButtonColor: '#1a5f4a',
        timer: 3000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: '{{ session('error') }}',
        confirmButtonText: 'حسناً',
        confirmButtonColor: '#1a5f4a'
    });
@endif
</script>
@endpush
