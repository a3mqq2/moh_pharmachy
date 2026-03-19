@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'الملفات المشتركة')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-share me-2"></i>الملفات المشتركة</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="ti ti-share me-1"></i>مشاركة ملف
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $view == 'sent' ? 'active' : '' }}" href="{{ route('admin.document-center.shared-files', ['view' => 'sent']) }}">
                            <i class="ti ti-send me-1"></i>المرسلة
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $view == 'received' ? 'active' : '' }}" href="{{ route('admin.document-center.shared-files', ['view' => 'received']) }}">
                            <i class="ti ti-inbox me-1"></i>المستلمة
                        </a>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العنوان</th>
                                <th>نوع الملف</th>
                                <th>الحجم</th>
                                @if($view == 'sent')
                                    <th>مشارك مع</th>
                                @else
                                    <th>من</th>
                                @endif
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
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
                                                    <i class="ti ti-checks text-success ms-1" title="تم الاطلاع {{ $user->pivot->seen_at }}"></i>
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
                                            <span class="badge bg-success"><i class="ti ti-checks me-1"></i>تم الاطلاع</span>
                                        @else
                                            <span class="badge bg-warning"><i class="ti ti-eye-off me-1"></i>جديد</span>
                                        @endif
                                    @else
                                        @php
                                            $seenCount = $file->users->whereNotNull('pivot.seen_at')->count();
                                            $totalCount = $file->users->count();
                                        @endphp
                                        <small>{{ $seenCount }}/{{ $totalCount }} اطلعوا</small>
                                    @endif
                                </td>
                                <td><small>{{ $file->created_at->format('Y-m-d H:i') }}</small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info btn-doc-preview" data-file-url="{{ Storage::url($file->file_path) }}" data-file-name="{{ $file->original_name }}" data-download-url="{{ route('admin.document-center.shared-files.download', $file) }}" title="عرض">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.document-center.shared-files.download', $file) }}" class="btn btn-sm btn-outline-success" title="تحميل">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        @if($view == 'sent')
                                        <form action="{{ route('admin.document-center.shared-files.destroy', $file) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
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
                                        لم تقم بمشاركة أي ملفات بعد
                                    @else
                                        لا توجد ملفات مشتركة معك
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
                    <h5 class="modal-title"><i class="ti ti-share me-2"></i>مشاركة ملف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">العنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الملف <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar">
                        <small class="text-muted">الحد الأقصى 20 ميجابايت</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مشاركة مع <span class="text-danger">*</span></label>
                        <select name="users[]" class="form-select select2" multiple required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-share me-1"></i>مشاركة</button>
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
        placeholder: 'اختر المستخدمين'
    });
});

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'حذف الملف',
            text: 'هل أنت متأكد من حذف هذا الملف المشترك؟',
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
