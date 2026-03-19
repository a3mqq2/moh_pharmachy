@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        إضافة مستخدم جديد
                    </h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- الاسم -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-1 text-muted"></i>
                                اسم المستخدم <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- البريد الإلكتروني -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1 text-muted"></i>
                                البريد الإلكتروني <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- كلمة المرور -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1 text-muted"></i>
                                كلمة المرور <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">يجب أن تكون 8 أحرف على الأقل</small>
                        </div>

                        <!-- تأكيد كلمة المرور -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-1 text-muted"></i>
                                تأكيد كلمة المرور <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">
                                <i class="fas fa-sitemap me-1 text-muted"></i>
                                القسم
                            </label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">بدون قسم</option>
                                @foreach($departments->whereNull('parent_id') as $dept)
                                    <optgroup label="{{ $dept->name }}">
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @foreach($dept->children as $child)
                                            <option value="{{ $child->id }}" {{ old('department_id') == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;↳ {{ $child->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="job_title" class="form-label">
                                <i class="fas fa-briefcase me-1 text-muted"></i>
                                المسمى الوظيفي
                            </label>
                            <input type="text" name="job_title" id="job_title" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title') }}">
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-label mb-0 fw-bold">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    الصلاحيات
                                </label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll"><i class="fas fa-check-double me-1"></i>تحديد الكل</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll"><i class="fas fa-times me-1"></i>إلغاء الكل</button>
                                </div>
                            </div>

                            <div class="row g-2">
                                @foreach($permissions as $group => $groupPermissions)
                                    @php
                                        $groupInfo = $groupLabels[$group] ?? ['label' => $group, 'icon' => 'fas fa-lock', 'color' => 'secondary'];
                                        $checkedCount = collect($groupPermissions)->filter(fn($p) => in_array($p->name, old('permissions', [])))->count();
                                        $totalCount = $groupPermissions->count();
                                    @endphp
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="border rounded-2 h-100">
                                            <div class="d-flex align-items-center justify-content-between px-2 py-2 bg-light border-bottom">
                                                <div class="d-flex align-items-center gap-1">
                                                    <i class="{{ $groupInfo['icon'] }} text-{{ $groupInfo['color'] }}" style="font-size:14px;width:18px;text-align:center;"></i>
                                                    <span class="fw-semibold" style="font-size:12px;">{{ $groupInfo['label'] }}</span>
                                                    <span class="badge bg-{{ $groupInfo['color'] }} rounded-pill perm-counter" data-group="{{ $group }}" style="font-size:10px;padding:2px 6px;">{{ $checkedCount }}/{{ $totalCount }}</span>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input group-toggle" type="checkbox" data-group="{{ $group }}" id="group_{{ $group }}" role="switch" style="width:2em;height:1em;">
                                                </div>
                                            </div>
                                            <div class="px-2 py-1">
                                                @foreach($groupPermissions as $permission)
                                                    <div class="d-flex align-items-center justify-content-between py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                        <label class="mb-0" for="perm_{{ $permission->id }}" style="cursor:pointer;font-size:11px;line-height:1.3;">
                                                            {{ $permission->display_name ?? $permission->name }}
                                                        </label>
                                                        <div class="form-check form-switch mb-0 ms-1">
                                                            <input class="form-check-input perm-checkbox perm-{{ $group }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" role="switch" style="width:1.8em;height:.9em;" {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ المستخدم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCounter(group) {
    var total = document.querySelectorAll('.perm-' + group).length;
    var checked = document.querySelectorAll('.perm-' + group + ':checked').length;
    var counter = document.querySelector('.perm-counter[data-group="' + group + '"]');
    if (counter) counter.textContent = checked + '/' + total;
    var toggle = document.getElementById('group_' + group);
    if (toggle) toggle.checked = (checked === total && total > 0);
}

function updateAllCounters() {
    document.querySelectorAll('.group-toggle').forEach(function(t) { updateCounter(t.dataset.group); });
}

document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) { cb.checked = true; });
    document.querySelectorAll('.group-toggle').forEach(function(cb) { cb.checked = true; });
    updateAllCounters();
});

document.getElementById('deselectAll').addEventListener('click', function() {
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.group-toggle').forEach(function(cb) { cb.checked = false; });
    updateAllCounters();
});

document.querySelectorAll('.group-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        var group = this.dataset.group;
        document.querySelectorAll('.perm-' + group).forEach(function(cb) { cb.checked = toggle.checked; });
        updateCounter(group);
    });
});

document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var group = this.className.match(/perm-(\S+)/);
        if (group && group[1] !== 'checkbox') updateCounter(group[1]);
    });
});

updateAllCounters();
</script>
@endpush
