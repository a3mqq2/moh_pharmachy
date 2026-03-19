@extends('layouts.app')

@section('title', 'التعميمات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التعميمات</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>التعميمات</h5>
                <span class="badge bg-secondary">{{ $announcements->total() }} تعميم</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> الفلاتر
                </button>
                @can('create_announcement')
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> تعميم جديد
                </a>
                @endcan
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'priority', 'target', 'from_date', 'to_date']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="عنوان التعميم..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الأولوية</label>
                        <select name="priority" class="form-select">
                            <option value="">الكل</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادي</option>
                            <option value="important" {{ request('priority') == 'important' ? 'selected' : '' }}>مهم</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الفئة المستهدفة</label>
                        <select name="target" class="form-select">
                            <option value="">الكل</option>
                            <option value="all" {{ request('target') == 'all' ? 'selected' : '' }}>جميع الشركات</option>
                            <option value="local" {{ request('target') == 'local' ? 'selected' : '' }}>محلية</option>
                            <option value="foreign" {{ request('target') == 'foreign' ? 'selected' : '' }}>أجنبية</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request()->hasAny(['search', 'priority', 'target', 'from_date', 'to_date']))
                                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الأولوية</th>
                        <th>الفئة المستهدفة</th>
                        <th>الفترة</th>
                        <th>الحالة</th>
                        <th>البريد</th>
                        <th>بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                    <tr onclick="window.location='{{ route('admin.announcements.show', $announcement) }}'" style="cursor: pointer;">
                        <td>
                            <span class="badge bg-dark">{{ $loop->iteration + ($announcements->currentPage() - 1) * $announcements->perPage() }}</span>
                        </td>
                        <td>
                            <strong>{{ Str::limit($announcement->title, 50) }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-{{ $announcement->priority_color }}">{{ $announcement->priority_name }}</span>
                        </td>
                        <td>{{ $announcement->target_name }}</td>
                        <td>
                            <small>{{ $announcement->start_date ? $announcement->start_date->format('Y-m-d') : 'فوري' }}</small><br>
                            <small class="text-muted">{{ $announcement->end_date ? $announcement->end_date->format('Y-m-d') : 'بدون حد' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $announcement->status_color }}">{{ $announcement->status_label }}</span>
                        </td>
                        <td>
                            @if($announcement->send_email)
                                @if($announcement->is_sent)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>تم الإرسال</span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>جاري الإرسال</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">بدون إرسال</span>
                            @endif
                        </td>
                        <td>{{ $announcement->creator->name ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('delete_announcement')
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-bullhorn fs-1 d-block mb-2"></i>
                                لا توجد تعميمات
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($announcements->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $announcements->withQueryString()->links() }}
        </div>
    </div>
    @endif
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
