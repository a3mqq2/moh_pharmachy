@extends('layouts.auth')

@section('title', 'تفاصيل الفاتورة')

@push('styles')
<style>
    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-content {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .back-to-home {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1a5f4a;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .back-to-home:hover {
        color: #164439;
        gap: 12px;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .info-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .info-card .card-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-card .card-body {
        padding: 25px;
    }

    .info-group {
        margin-bottom: 20px;
    }

    .info-group label {
        display: block;
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .info-group p {
        font-size: 1rem;
        color: #1f2937;
        margin: 0;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-info {
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #164439;
        color: #ffffff;
    }

    .btn-outline-primary {
        background: transparent;
        color: #1a5f4a;
        border: 1px solid #1a5f4a;
    }

    .btn-outline-primary:hover {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-outline-danger {
        background: transparent;
        color: #dc2626;
        border: 1px solid #dc2626;
    }

    .btn-outline-danger:hover {
        background: #dc2626;
        color: #ffffff;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        color: #374151;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .text-muted {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .text-danger {
        color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.foreign-companies.show', $company) }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
                <span>رجوع إلى تفاصيل الشركة</span>
            </a>
            <h2 class="page-title">
                <i class="ti ti-file-invoice"></i>
                تفاصيل الفاتورة
            </h2>
            <p class="page-subtitle">عرض تفاصيل الفاتورة ورفع إيصال الدفع</p>
        </div>
    </div>

    

    <div class="info-card">
        <div class="card-header">
            <h3><i class="ti ti-file-invoice"></i> معلومات الفاتورة</h3>
            <div>
                <span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ $invoice->status === 'paid' ? 'مدفوعة' : ($invoice->status === 'pending' ? 'معلقة' : 'ملغاة') }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-group">
                        <label>رقم الفاتورة</label>
                        <p>{{ $invoice->invoice_number }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <label>المبلغ</label>
                        <p><strong>{{ number_format($invoice->amount, 2) }} د.ل</strong></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <label>الوصف</label>
                        <p>{{ $invoice->description }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <label>تاريخ الإصدار</label>
                        <p>{{ $invoice->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                @if($invoice->due_date)
                <div class="col-md-6">
                    <div class="info-group">
                        <label>تاريخ الاستحقاق</label>
                        <p>{{ $invoice->due_date->format('Y-m-d') }}</p>
                    </div>
                </div>
                @endif
                @if($invoice->notes)
                <div class="col-md-12">
                    <div class="info-group">
                        <label>ملاحظات</label>
                        <p>{{ $invoice->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($invoice->status === 'pending')
    <div class="info-card">
        <div class="card-header">
            <h3><i class="ti ti-upload"></i> رفع إيصال الدفع</h3>
        </div>
        <div class="card-body">
            @if($invoice->receipt_path)
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    @if($invoice->receipt_status === 'pending')
                        <strong>تم رفع إيصال الدفع بنجاح.</strong> جاري مراجعة الإيصال من قبل الإدارة.
                    @elseif($invoice->receipt_status === 'rejected')
                        <strong>تم رفض الإيصال.</strong>
                        <br>السبب: {{ $invoice->receipt_rejection_reason }}
                        <br><small class="text-muted">يرجى رفع إيصال جديد.</small>
                    @endif
                </div>

                @if($invoice->receipt_status === 'rejected' || $invoice->receipt_status === 'pending')
                <div class="mb-3">
                    <label class="form-label">الإيصال الحالي</label>
                    <div class="d-flex gap-2">
                        <a href="{{ route('representative.foreign-companies.invoices.download-receipt', [$company, $invoice]) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="ti ti-download"></i> تحميل الإيصال
                        </a>
                        @if($invoice->receipt_status === 'rejected')
                        <form action="{{ route('representative.foreign-companies.invoices.delete-receipt', [$company, $invoice]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف الإيصال؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="ti ti-trash"></i> حذف الإيصال
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endif

                @if($invoice->receipt_status === 'rejected')
                <form action="{{ route('representative.foreign-companies.invoices.upload-receipt', [$company, $invoice]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="receipt" class="form-label">رفع إيصال جديد <span class="text-danger">*</span></label>
                        <input type="file" name="receipt" id="receipt" class="form-control @error('receipt') is-invalid @enderror" required accept="image/*,application/pdf">
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">الأنواع المسموحة: PDF, JPG, PNG (الحجم الأقصى: 5MB)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload"></i> رفع الإيصال
                    </button>
                </form>
                @endif
            @else
                <form action="{{ route('representative.foreign-companies.invoices.upload-receipt', [$company, $invoice]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="receipt" class="form-label">إيصال الدفع <span class="text-danger">*</span></label>
                        <input type="file" name="receipt" id="receipt" class="form-control @error('receipt') is-invalid @enderror" required accept="image/*,application/pdf">
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">الأنواع المسموحة: PDF, JPG, PNG (الحجم الأقصى: 5MB)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload"></i> رفع الإيصال
                    </button>
                </form>
            @endif
        </div>
    </div>
    @elseif($invoice->status === 'paid')
    <div class="info-card">
        <div class="card-header">
            <h3><i class="ti ti-file-check"></i> إيصال الدفع</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <i class="ti ti-check me-2"></i>
                <strong>تم الموافقة على الإيصال بنجاح.</strong>
                @if($invoice->paid_at)
                    <br>تاريخ الدفع: {{ $invoice->paid_at->format('Y-m-d') }}
                @endif
            </div>
            @if($invoice->receipt_path)
            <a href="{{ route('representative.foreign-companies.invoices.download-receipt', [$company, $invoice]) }}" class="btn btn-primary" target="_blank">
                <i class="ti ti-download"></i> تحميل الإيصال
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
