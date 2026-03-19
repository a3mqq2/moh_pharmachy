@extends('layouts.app')

@section('title', 'تعميم جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">التعميمات</a></li>
    <li class="breadcrumb-item active">تعميم جديد</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>إنشاء تعميم جديد</h5>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>رجوع
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.announcements.store') }}" method="POST" id="announcementForm">
            @csrf

            <div class="mb-4">
                <label class="form-label">عنوان التعميم <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="عنوان التعميم" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">نص التعميم <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control @error('body') is-invalid @enderror"
                          rows="8" placeholder="اكتب نص التعميم هنا..." required>{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">الأولوية <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>عادي</option>
                        <option value="important" {{ old('priority') == 'important' ? 'selected' : '' }}>مهم</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">الفئة المستهدفة <span class="text-danger">*</span></label>
                    <select name="target" class="form-select @error('target') is-invalid @enderror" required>
                        <option value="all" {{ old('target') == 'all' ? 'selected' : '' }}>جميع الشركات</option>
                        <option value="local" {{ old('target') == 'local' ? 'selected' : '' }}>الشركات المحلية فقط</option>
                        <option value="foreign" {{ old('target') == 'foreign' ? 'selected' : '' }}>الشركات الأجنبية فقط</option>
                    </select>
                    @error('target')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date') }}">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">اتركه فارغاً ليبدأ فوراً</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ الانتهاء</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">اتركه فارغاً ليبقى سارياً بدون حد</small>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="send_email" value="1"
                           id="sendEmail" {{ old('send_email') ? 'checked' : '' }}>
                    <label class="form-check-label" for="sendEmail">
                        إرسال التعميم عبر البريد الإلكتروني لمسؤولي الشركات
                    </label>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle me-1"></i>
                    سيتم إرسال الإيميلات عبر نظام الطوابير (Queue) لتجنب الضغط على الخادم
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-paper-plane me-1"></i>نشر التعميم
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('announcementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let form = this;
    let sendEmail = document.getElementById('sendEmail').checked;

    let message = 'سيتم نشر التعميم';
    if (sendEmail) {
        message += ' وإرسال بريد إلكتروني لجميع المسؤولين المستهدفين';
    }

    Swal.fire({
        title: 'تأكيد نشر التعميم',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a5f4a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'نعم، انشر',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري النشر...';
            form.submit();
        }
    });
});
</script>
@endpush
