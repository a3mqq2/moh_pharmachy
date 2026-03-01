@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة - ' . $invoice->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-company-invoices.index') }}">فواتير الشركات الأجنبية</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.foreign-company-invoices.download', $invoice->id) }}"
               class="btn btn-primary"
               target="_blank">
                <i class="ti ti-printer me-1"></i>
                طباعة الفاتورة
            </a>

            @if($invoice->receipt_path && $invoice->receipt_status === 'pending')
            <button type="button"
                    class="btn btn-success"
                    onclick="approveReceipt()">
                <i class="ti ti-check me-1"></i>
                الموافقة على الإيصال
            </button>
            <button type="button"
                    class="btn btn-danger"
                    onclick="showRejectModal()">
                <i class="ti ti-x me-1"></i>
                رفض الإيصال
            </button>
            @endif

            @if($invoice->status === 'pending' && !$invoice->receipt_path)
            <a href="{{ route('admin.foreign-company-invoices.edit', $invoice->id) }}"
               class="btn btn-warning">
                <i class="ti ti-edit me-1"></i>
                تعديل الفاتورة
            </a>
            @endif

            @if($invoice->status === 'pending')
            <button type="button"
                    class="btn btn-outline-danger"
                    onclick="showCancelModal()">
                <i class="ti ti-ban me-1"></i>
                إلغاء الفاتورة
            </button>
            @endif

            <a href="{{ route('admin.foreign-company-invoices.index') }}"
               class="btn btn-outline-secondary ms-auto">
                <i class="ti ti-arrow-right me-1"></i>
                العودة للقائمة
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice-content" type="button" role="tab">
                    <i class="ti ti-file-invoice me-1"></i>
                    معلومات الفاتورة
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="company-tab" data-bs-toggle="tab" data-bs-target="#company-content" type="button" role="tab">
                    <i class="ti ti-building me-1"></i>
                    معلومات الشركة
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="receipt-tab" data-bs-toggle="tab" data-bs-target="#receipt-content" type="button" role="tab">
                    <i class="ti ti-file-text me-1"></i>
                    إيصال الدفع
                    @if($invoice->receipt_status === 'pending')
                        <span class="badge bg-warning ms-1">قيد المراجعة</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-content" type="button" role="tab">
                    <i class="ti ti-timeline me-1"></i>
                    السجل الزمني
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="invoice-content" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="40%">رقم الفاتورة</th>
                                <td>
                                    <span class="badge bg-dark fs-6">{{ $invoice->invoice_number }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">المبلغ</th>
                                <td>
                                    <h4 class="text-primary mb-0">{{ number_format($invoice->amount, 2) }} د.ل</h4>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">الحالة</th>
                                <td>
                                    @if($invoice->status === 'pending')
                                        <span class="badge bg-warning">قيد الانتظار</span>
                                    @elseif($invoice->status === 'paid')
                                        <span class="badge bg-success">مدفوعة</span>
                                    @elseif($invoice->status === 'cancelled')
                                        <span class="badge bg-danger">ملغاة</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="40%">تاريخ الإصدار</th>
                                <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">أصدرت بواسطة</th>
                                <td>{{ $invoice->issuedBy->name ?? 'غير معروف' }}</td>
                            </tr>
                            @if($invoice->status === 'paid')
                            <tr>
                                <th class="bg-light">تاريخ الدفع</th>
                                <td>{{ $invoice->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($invoice->description)
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle me-1"></i>
                        الوصف
                    </h6>
                    <p class="mb-0">{{ $invoice->description }}</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="company-content" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="40%">اسم الشركة</th>
                                <td><strong>{{ $invoice->foreignCompany->company_name }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">حالة الشركة</th>
                                <td>
                                    @php
                                        $statusName = match($invoice->foreignCompany->status) {
                                            'pending' => 'قيد المراجعة',
                                            'approved' => 'مقبولة',
                                            'active' => 'مفعلة',
                                            'rejected' => 'مرفوضة',
                                            'suspended' => 'موقوفة',
                                            'pending_payment' => 'بانتظار الدفع',
                                            'expired' => 'منتهية الصلاحية',
                                            default => $invoice->foreignCompany->status,
                                        };
                                        $statusColor = match($invoice->foreignCompany->status) {
                                            'active' => 'success',
                                            'pending', 'pending_payment' => 'warning',
                                            'approved' => 'info',
                                            'rejected', 'suspended', 'expired' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ $statusName }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="40%">ممثل الشركة</th>
                                <td><strong>{{ $invoice->foreignCompany->representative->name ?? 'غير معروف' }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">البريد الإلكتروني</th>
                                <td>{{ $invoice->foreignCompany->representative->email ?? 'غير متوفر' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">رقم الهاتف</th>
                                <td>{{ $invoice->foreignCompany->representative->phone ?? 'غير متوفر' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.foreign-companies.show', $invoice->foreign_company_id) }}"
                       class="btn btn-primary">
                        <i class="ti ti-external-link me-1"></i>
                        عرض تفاصيل الشركة
                    </a>
                </div>
            </div>

            <div class="tab-pane fade" id="receipt-content" role="tabpanel">
                @if($invoice->receipt_path)
                    <div class="row">
                        <div class="col-md-8">
                            @if($invoice->receipt_status === 'pending')
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="ti ti-clock me-1"></i>
                                    الإيصال في انتظار المراجعة
                                </h6>
                                <p class="mb-0">تم رفع إيصال الدفع من قبل ممثل الشركة ويحتاج إلى مراجعتك للموافقة أو الرفض.</p>
                            </div>
                            @elseif($invoice->receipt_status === 'approved')
                            <div class="alert alert-success">
                                <h6 class="alert-heading">
                                    <i class="ti ti-check me-1"></i>
                                    تمت الموافقة على الإيصال
                                </h6>
                                @if($invoice->receiptReviewedBy)
                                <p class="mb-1"><strong>بواسطة:</strong> {{ $invoice->receiptReviewedBy->name }}</p>
                                <p class="mb-0"><strong>في:</strong> {{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</p>
                                @endif
                            </div>
                            @elseif($invoice->receipt_status === 'rejected')
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="ti ti-x me-1"></i>
                                    تم رفض الإيصال
                                </h6>
                                @if($invoice->receiptReviewedBy)
                                <p class="mb-1"><strong>بواسطة:</strong> {{ $invoice->receiptReviewedBy->name }}</p>
                                <p class="mb-1"><strong>في:</strong> {{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</p>
                                @endif
                                @if($invoice->receipt_rejection_reason)
                                <hr>
                                <p class="mb-0"><strong>السبب:</strong> {{ $invoice->receipt_rejection_reason }}</p>
                                @endif
                            </div>
                            @endif

                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th class="bg-light" width="30%">تاريخ الرفع</th>
                                    <td>{{ $invoice->receipt_uploaded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">حالة الإيصال</th>
                                    <td>
                                        @if($invoice->receipt_status === 'pending')
                                            <span class="badge bg-warning">قيد المراجعة</span>
                                        @elseif($invoice->receipt_status === 'approved')
                                            <span class="badge bg-success">موافق عليه</span>
                                        @elseif($invoice->receipt_status === 'rejected')
                                            <span class="badge bg-danger">مرفوض</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div class="mt-3">
                                <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}"
                                   class="btn btn-primary"
                                   target="_blank">
                                    <i class="ti ti-download me-1"></i>
                                    تحميل الإيصال
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-file-off" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">لم يتم رفع إيصال الدفع بعد</h4>
                        <p class="text-muted">في انتظار قيام ممثل الشركة برفع إيصال الدفع</p>
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="timeline-content" role="tabpanel">
                <div class="timeline">
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-success">
                                    <i class="ti ti-circle-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">إصدار الفاتورة</h6>
                                <p class="text-muted mb-1">تم إصدار الفاتورة رقم {{ $invoice->invoice_number }}</p>
                                <small class="text-muted">{{ $invoice->created_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>

                    @if($invoice->receipt_uploaded_at)
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-info">
                                    <i class="ti ti-upload"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">رفع الإيصال</h6>
                                <p class="text-muted mb-1">تم رفع إيصال الدفع من قبل ممثل الشركة</p>
                                <small class="text-muted">{{ $invoice->receipt_uploaded_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->receipt_reviewed_at)
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-{{ $invoice->receipt_status === 'approved' ? 'success' : 'danger' }}">
                                    <i class="ti ti-eye"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">مراجعة الإيصال</h6>
                                <p class="text-muted mb-1">
                                    تمت {{ $invoice->receipt_status === 'approved' ? 'الموافقة' : 'الرفض' }} من قبل {{ $invoice->receiptReviewedBy->name ?? 'غير معروف' }}
                                </p>
                                <small class="text-muted">{{ $invoice->receipt_reviewed_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->status === 'paid')
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-success">
                                    <i class="ti ti-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">تم الدفع</h6>
                                <p class="text-muted mb-1">تم إكمال عملية الدفع بنجاح</p>
                                <small class="text-muted">{{ $invoice->paid_at?->format('Y-m-d H:i') ?? $invoice->updated_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($invoice->status === 'cancelled')
                    <div class="timeline-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-danger">
                                    <i class="ti ti-ban"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">إلغاء الفاتورة</h6>
                                <p class="text-muted mb-1">تم إلغاء الفاتورة</p>
                                <small class="text-muted">{{ $invoice->updated_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.invoices.reject-receipt', [$invoice->foreign_company_id, $invoice->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">رفض إيصال الدفع</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="اكتب سبب رفض الإيصال..."></textarea>
                        <small class="text-muted">الحد الأدنى 10 أحرف</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>
                        رفض الإيصال
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-company-invoices.cancel', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إلغاء الفاتورة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        هل أنت متأكد من إلغاء هذه الفاتورة؟ هذا الإجراء لا يمكن التراجع عنه.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب الإلغاء (اختياري)</label>
                        <textarea name="cancellation_reason"
                                  class="form-control"
                                  rows="3"
                                  placeholder="اكتب سبب إلغاء الفاتورة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-ban me-1"></i>
                        إلغاء الفاتورة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function approveReceipt() {
        Swal.fire({
            title: 'الموافقة على إيصال الدفع',
            text: 'سيتم الموافقة على الإيصال وتفعيل الشركة تلقائياً. هل تريد المتابعة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، الموافقة',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.foreign-companies.invoices.approve-receipt', [$invoice->foreign_company_id, $invoice->id]) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function showRejectModal() {
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    function showCancelModal() {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
</script>
@endpush
