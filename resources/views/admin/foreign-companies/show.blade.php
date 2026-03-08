@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'تفاصيل الشركة: ' . $foreignCompany->company_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-companies.index') }}">الشركات الأجنبية</a></li>
    <li class="breadcrumb-item active">{{ $foreignCompany->company_name }}</li>
@endsection

@section('content')
<div class="card mt-3 mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">{{ $foreignCompany->company_name }}</h4>
            <span class="badge bg-{{ $foreignCompany->entity_type == 'factory' ? 'info' : 'primary' }} me-1">{{ $foreignCompany->entity_type_name }}</span>
            <span class="badge {{ str_replace('badge-', 'bg-', $foreignCompany->status_badge_class) }} me-1">{{ $foreignCompany->status_name }}</span>
            <span class="badge bg-dark">{{ $foreignCompany->country }}</span>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($foreignCompany->status, ['approved', 'active']))
                <a href="{{ route('admin.foreign-companies.certificate', $foreignCompany) }}" target="_blank" class="btn btn-primary">
                    <i class="ti ti-printer me-1"></i>طباعة الشهادة
                </a>
            @endif
            @if($foreignCompany->status == 'pending')
                @if($foreignCompany->hasAllRequiredDocuments())
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="ti ti-check me-1"></i>قبول
                    </button>
                @else
                    <button type="button" class="btn btn-success" disabled title="يجب رفع جميع المستندات المطلوبة">
                        <i class="ti ti-check me-1"></i>قبول
                    </button>
                @endif
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>رفض
                </button>
            @elseif($foreignCompany->status == 'rejected')
                <form action="{{ route('admin.foreign-companies.restore-pending', $foreignCompany) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>إعادة للمراجعة</button>
                </form>
            @endif
            <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-right me-1"></i>رجوع</a>
        </div>
    </div>
</div>


@if($foreignCompany->status == 'rejected' && $foreignCompany->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>سبب الرفض:</strong> {{ $foreignCompany->rejection_reason }}
</div>
@endif

@if($foreignCompany->status == 'pending' && !$foreignCompany->hasAllRequiredDocuments())
<div class="alert alert-warning">
    <strong><i class="ti ti-alert-triangle me-1"></i>المستندات غير مكتملة:</strong>
    <p class="mb-0 mt-2">الشركة لم ترفع جميع المستندات المطلوبة بعد.</p>
</div>
@endif


<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="companyTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-company">بيانات الشركة</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-representative">ممثل الشركة</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>المستندات
                    <span class="badge {{ $foreignCompany->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }} rounded-pill ms-1">
                        {{ $foreignCompany->documents->count() }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                    <i class="ti ti-file-invoice me-1"></i>الفواتير
                    @if($foreignCompany->invoices()->where('status', 'pending')->count() > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $foreignCompany->invoices()->where('status', 'pending')->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-company">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">معلومات الشركة</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr><th class="bg-light" width="40%">اسم الشركة</th><td>{{ $foreignCompany->company_name }}</td></tr>
                                <tr><th class="bg-light">الدولة</th><td>{{ $foreignCompany->country }}</td></tr>
                                <tr><th class="bg-light">نوع الكيان</th><td>{{ $foreignCompany->entity_type_name }}</td></tr>
                                <tr><th class="bg-light">نوع النشاط</th><td>{{ $foreignCompany->activity_type_name }}</td></tr>
                                <tr><th class="bg-light">عدد المنتجات</th><td>{{ $foreignCompany->products_count ?? '-' }}</td></tr>
                                @if($foreignCompany->expires_at)
                                <tr>
                                    <th class="bg-light">تاريخ انتهاء الصلاحية</th>
                                    <td>
                                        <span class="badge bg-{{ $foreignCompany->isExpired() ? 'danger' : 'info' }}">
                                            {{ $foreignCompany->expires_at->format('Y-m-d') }}
                                        </span>
                                        @if($foreignCompany->isExpired())
                                            <small class="text-danger d-block mt-1">منتهية الصلاحية</small>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($foreignCompany->last_renewed_at)
                                <tr>
                                    <th class="bg-light">آخر تاريخ تجديد</th>
                                    <td>
                                        <span class="badge bg-success">{{ $foreignCompany->last_renewed_at->format('Y-m-d') }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">معلومات الاتصال</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr><th class="bg-light" width="40%">العنوان</th><td>{{ $foreignCompany->address ?? '-' }}</td></tr>
                                <tr><th class="bg-light">البريد الإلكتروني</th><td>{{ $foreignCompany->email ?? '-' }}</td></tr>
                                <tr>
                                    <th class="bg-light">الشركة المحلية</th>
                                    <td>
                                        @if($foreignCompany->localCompany)
                                            <a href="{{ route('admin.local-companies.show', $foreignCompany->localCompany) }}">
                                                {{ $foreignCompany->localCompany->company_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الدول المسجلة</th>
                                    <td>
                                        @if($foreignCompany->registered_countries && is_array($foreignCompany->registered_countries) && count($foreignCompany->registered_countries) > 0)
                                            {{ implode(', ', $foreignCompany->registered_countries) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-representative">
                @if($foreignCompany->representative)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="bg-light" width="15%">الاسم</th>
                            <td width="35%">{{ $foreignCompany->representative->name }}</td>
                            <th class="bg-light" width="15%">البريد الإلكتروني</th>
                            <td width="35%">{{ $foreignCompany->representative->email }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">رقم الهاتف</th>
                            <td dir="ltr" class="text-end">{{ $foreignCompany->representative->phone ?? '-' }}</td>
                            <th class="bg-light">الجنسية</th>
                            <td>{{ $foreignCompany->representative->nationality ?? '-' }}</td>
                        </tr>
                    <tr>
                        <th class="bg-light">تاريخ التسجيل</th>
                        <td>{{ $foreignCompany->representative->created_at->format('Y-m-d h:i A') }}</td>
                        <th class="bg-light">حالة الحساب</th>
                        <td>
                            @if($foreignCompany->representative->email_verified_at)
                                <span class="badge bg-success">مفعل</span>
                            @else
                                <span class="badge bg-warning">غير مفعل</span>
                            @endif
                        </td>
                    </tr>
                </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-user-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">لا يوجد ممثل للشركة</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-documents">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-folder me-1"></i>
                        المستندات والملفات
                        @if($foreignCompany->hasAllRequiredDocuments())
                            <span class="badge bg-success ms-2"><i class="ti ti-check me-1"></i>مكتمل</span>
                        @else
                            <span class="badge bg-warning ms-2"><i class="ti ti-alert-triangle me-1"></i>غير مكتمل</span>
                        @endif
                    </h6>
                </div>

                @if($foreignCompany->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">نوع المستند</th>
                                <th width="15%">اسم الملف</th>
                                <th width="10%">الحجم</th>
                                <th width="20%">تاريخ الرفع</th>
                                <th width="20%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($foreignCompany->documents as $index => $document)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $document->document_type_name }}</strong>
                                        @if($document->notes)
                                            <br><small class="text-muted">{{ Str::limit($document->notes, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $document->document_name }}">
                                        {{ Str::limit($document->document_name, 20) }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $document->file_size_formatted }}</small>
                                </td>
                                <td>
                                    <small>{{ $document->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.foreign-companies.documents.download', [$foreignCompany, $document]) }}" class="btn btn-outline-primary" title="تحميل">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-folder-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">لا توجد مستندات مرفوعة</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoices">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-file-invoice me-1"></i>
                        الفواتير
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> إضافة فاتورة
                    </button>
                </div>

                @if($foreignCompany->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th width="12%">رقم الفاتورة</th>
                                <th width="20%">الوصف</th>
                                <th width="10%">المبلغ</th>
                                <th width="12%">الحالة</th>
                                <th width="13%">التاريخ</th>
                                <th width="20%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($foreignCompany->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>
                                    {{ $invoice->description }}
                                    @if($invoice->notes)
                                        <br><small class="text-muted">{{ Str::limit($invoice->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> د.ل</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">
                                        {{ $invoice->status == 'paid' ? 'مدفوعة' : 'قيد الدفع' }}
                                    </span>
                                    @if($invoice->paid_at)
                                        <br><small class="text-muted">{{ $invoice->paid_at->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $invoice->created_at->format('Y-m-d') }}</small>
                                    @if($invoice->due_date)
                                        <br><small class="text-muted">استحقاق: {{ $invoice->due_date->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($invoice->receipt_path && $invoice->receipt_status == 'pending')
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-company-id="{{ $foreignCompany->id }}">
                                                <i class="ti ti-check me-1"></i>موافقة على الإيصال
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-x me-1"></i>رفض الإيصال
                                            </button>
                                        @endif
                                        @if($invoice->receipt_path)
                                            <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$foreignCompany, $invoice]) }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-download me-1"></i>تحميل الإيصال
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-invoice" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                            <i class="ti ti-trash me-1"></i>حذف
                                        </button>
                                    </div>
                                    <form id="delete-invoice-form-{{ $invoice->id }}" action="{{ route('admin.foreign-companies.invoices.destroy', [$foreignCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-file-invoice fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">لا توجد فواتير</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> إضافة الفاتورة الأولى
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Company Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.reject', $foreignCompany) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">رفض الشركة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Company Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.approve', $foreignCompany) }}" method="POST" class="approve-form">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">قبول الشركة الأجنبية</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من قبول هذه الشركة الأجنبية؟ سيتم تغيير حالتها إلى "مقبولة" وإضافة فاتورة التسجيل.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تأكيد القبول</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.foreign-companies.invoices.store', $foreignCompany) }}" method="POST">
                @csrf
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-plus me-2"></i>إضافة فاتورة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الوصف <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="invoice_description" class="form-control" placeholder="وصف الفاتورة" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="invoice_amount" class="form-control" step="0.01" min="0" required>
                            <span class="input-group-text">د.ل</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية (اختياري)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i>إضافة الفاتورة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Receipt Modal -->
<div class="modal fade" id="rejectReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectReceiptForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ti ti-x me-2"></i>رفض إيصال الدفع</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>تنبيه:</strong> عند رفض الإيصال، سيتم تغيير حالة الفاتورة إلى "قيد الدفع" وإرسال إشعار بالبريد الإلكتروني إلى ممثل الشركة.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب رفض الإيصال <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="receipt_rejection_reason" class="form-control" rows="4" required placeholder="اكتب سبب رفض الإيصال بالتفصيل..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i>تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Save and restore active tab
const tabKey = 'foreignCompanyTab_{{ $foreignCompany->id }}';
const savedTab = sessionStorage.getItem(tabKey);
if (savedTab) {
    const tabButton = document.querySelector('[data-bs-target="' + savedTab + '"]');
    if (tabButton) {
        const tab = new bootstrap.Tab(tabButton);
        tab.show();
    }
}

document.querySelectorAll('#companyTabs button[data-bs-toggle="tab"]').forEach(function(tabButton) {
    tabButton.addEventListener('shown.bs.tab', function(e) {
        sessionStorage.setItem(tabKey, e.target.getAttribute('data-bs-target'));
    });
});

// Restore form confirm
document.querySelector('.restore-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'إعادة للمراجعة',
        text: 'هل أنت متأكد من إعادة هذه الشركة للمراجعة؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
});

// Approve receipt and activate company
document.querySelectorAll('.btn-approve-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const companyId = this.getAttribute('data-company-id');
        Swal.fire({
            title: 'الموافقة على الإيصال',
            text: 'هل أنت متأكد من الموافقة على الإيصال؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، موافق',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("admin/foreign-companies") }}/' + companyId + '/invoices/' + invoiceId + '/approve-receipt';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// Reject receipt
document.querySelectorAll('.btn-reject-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/foreign-companies/" . $foreignCompany->id . "/invoices") }}/' + invoiceId + '/reject-receipt';
        document.getElementById('receipt_rejection_reason').value = '';

        new bootstrap.Modal(document.getElementById('rejectReceiptModal')).show();
    });
});

// Delete invoice
document.querySelectorAll('.btn-delete-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const invoiceNumber = this.getAttribute('data-number');
        Swal.fire({
            title: 'تأكيد الحذف',
            text: 'هل أنت متأكد من حذف الفاتورة رقم: ' + invoiceNumber + '؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-invoice-form-' + invoiceId).submit();
            }
        });
    });
});
</script>
@endpush
