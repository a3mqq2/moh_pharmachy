@extends('layouts.app')

@section('title', 'تعديل الفاتورة - ' . $invoice->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.index') }}">فواتير الشركات الأجنبية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')


<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ti ti-edit me-2"></i>
                    تعديل الفاتورة
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>ملاحظة:</strong> يمكن تعديل الفاتورة فقط إذا كانت في حالة "قيد الانتظار" ولم يتم رفع إيصال دفع لها.
                </div>

                <form action="{{ route('admin.foreign-company-invoices.update', $invoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Company Information (Read-only) -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="ti ti-building me-2"></i>
                                معلومات الشركة
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="text-muted small">اسم الشركة</label>
                                        <div class="fw-bold">{{ $invoice->foreignCompany->localCompany->name_ar }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="text-muted small">رقم الفاتورة</label>
                                        <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Editable Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    المبلغ <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount', $invoice->amount) }}"
                                           min="0"
                                           step="0.01"
                                           required>
                                    <span class="input-group-text">د.ل</span>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">أدخل مبلغ الفاتورة بالدينار الليبي</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">حالة الفاتورة</label>
                                <input type="text"
                                       class="form-control"
                                       value="قيد الانتظار"
                                       readonly
                                       disabled>
                                <small class="text-muted">لا يمكن تغيير حالة الفاتورة</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">الوصف (اختياري)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="أدخل وصف الفاتورة...">{{ old('description', $invoice->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">الحد الأقصى 500 حرف</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}"
                           class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change History Info -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="ti ti-clock me-2"></i>
                    معلومات الفاتورة
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">تاريخ الإصدار</label>
                            <div>{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">أصدرت بواسطة</label>
                            <div>{{ $invoice->issuedBy->name ?? 'غير معروف' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="text-muted small">آخر تحديث</label>
                            <div>{{ $invoice->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: '{{ session('success') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: '{{ session('error') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#dc3545'
        });
    @endif
</script>
@endpush
