@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'تفاصيل الشركة: ' . $localCompany->company_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.local-companies.index') }}">الشركات المحلية</a></li>
    <li class="breadcrumb-item active">{{ $localCompany->company_name }}</li>
@endsection

@section('content')
<div class="card mt-3 mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">{{ $localCompany->company_name }}</h4>
            <span class="badge bg-{{ $localCompany->company_type == 'distributor' ? 'info' : 'primary' }} me-1">{{ $localCompany->company_type_name }}</span>
            <span class="badge bg-{{ $localCompany->status_color }} me-1">{{ $localCompany->status_name }}</span>
            @if($localCompany->registration_number)
                <span class="badge bg-dark">رقم القيد: {{ $localCompany->registration_number }}</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($localCompany->status == 'pending')
                @php
                    $missingDocs = $localCompany->getMissingDocuments();
                    $hasUnpaidInvoices = $localCompany->hasUnpaidInvoices();
                    $canApprove = count($missingDocs) == 0 && !$hasUnpaidInvoices;
                @endphp
                @if($canApprove)
                    <form action="{{ route('admin.local-companies.approve', $localCompany) }}" method="POST" class="d-inline approve-form">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i>قبول</button>
                    </form>
                @else
                    @php
                        $disabledReason = count($missingDocs) > 0 ? 'يجب رفع جميع المستندات المطلوبة' : 'يجب دفع جميع الفواتير المستحقة';
                    @endphp
                    <button type="button" class="btn btn-success" disabled title="{{ $disabledReason }}">
                        <i class="ti ti-check me-1"></i>قبول
                    </button>
                @endif
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>رفض
                </button>
            @elseif($localCompany->status == 'rejected')
                <form action="{{ route('admin.local-companies.restore-pending', $localCompany) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>إعادة للمراجعة</button>
                </form>
            @elseif(in_array($localCompany->status, ['approved', 'active']))
                <a href="{{ route('admin.local-companies.certificate', $localCompany) }}" class="btn btn-info" target="_blank">
                    <i class="ti ti-certificate me-1"></i>طباعة شهادة التسجيل
                </a>
            @endif
            <a href="{{ route('admin.local-companies.edit', $localCompany) }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>تعديل</a>
            <a href="{{ route('admin.local-companies.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-right me-1"></i>رجوع</a>
        </div>
    </div>
</div>



@if($localCompany->status == 'rejected' && $localCompany->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>سبب الرفض:</strong> {{ $localCompany->rejection_reason }}
</div>
@endif

@if($localCompany->status == 'pending' && count($localCompany->getMissingDocuments()) > 0)
<div class="alert alert-warning">
    <strong><i class="ti ti-alert-triangle me-1"></i>المستندات الناقصة:</strong>
    <ul class="mb-0 mt-2">
        @foreach($localCompany->getMissingDocuments() as $type => $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($localCompany->status == 'pending' && $localCompany->hasUnpaidInvoices())
<div class="alert alert-danger">
    <strong><i class="ti ti-cash me-1"></i>فواتير غير مدفوعة:</strong>
    يوجد {{ $localCompany->invoices()->where('status', 'unpaid')->count() }} فاتورة غير مدفوعة بإجمالي {{ number_format($localCompany->getUnpaidInvoicesTotal(), 2) }} د.ل
    <a href="#tab-invoices" class="alert-link" onclick="document.querySelector('[data-bs-target=\'#tab-invoices\']').click()">عرض الفواتير</a>
</div>
@endif

<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="companyTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-company">بيانات الشركة</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-license">بيانات الترخيص</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-manager">المدير المسؤول</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-representative">ممثل الشركة</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>الملفات
                    <span class="badge {{ $localCompany->hasAllRequiredDocuments() ? 'bg-success' : 'bg-warning' }} rounded-pill ms-1">
                        {{ $localCompany->documents->count() }}/{{ count(\App\Models\LocalCompanyDocument::requiredDocumentTypes()) }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                    <i class="ti ti-file-invoice me-1"></i>الفواتير
                    @if($localCompany->hasUnpaidInvoices())
                        <span class="badge bg-danger rounded-pill ms-1">{{ $localCompany->invoices()->where('status', 'unpaid')->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activities">
                    <i class="ti ti-history me-1"></i>السجل
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-company">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr><th class="bg-light" width="40%">اسم الشركة</th><td>{{ $localCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">البريد الإلكتروني</th><td>{{ $localCompany->email }}</td></tr>
                            <tr><th class="bg-light">الهاتف</th><td dir="ltr" class="text-end">{{ $localCompany->phone }}</td></tr>
                            <tr><th class="bg-light">هاتف محمول</th><td dir="ltr" class="text-end">{{ $localCompany->mobile ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr><th class="bg-light" width="40%">العنوان</th><td>{{ $localCompany->company_address ?? '-' }}</td></tr>
                            <tr><th class="bg-light">الشارع</th><td>{{ $localCompany->street ?? '-' }}</td></tr>
                            <tr><th class="bg-light">المدينة</th><td>{{ $localCompany->city }}</td></tr>
                        </table>
                    </div>
                </div>

                @if($localCompany->is_pre_registered)
                <div class="alert alert-info mt-3">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>شركة مسجلة مسبقاً:</strong>
                    <br>
                    <small>رقم القيد السابق: <strong>{{ $localCompany->pre_registration_number }}</strong></small>
                    <br>
                    <small>سنة التسجيل: <strong>{{ $localCompany->pre_registration_year }}</strong></small>
                    <br>
                    <small class="text-warning">سيتم استخدام رقم القيد السابق عند الموافقة على الشركة</small>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-license">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">بيانات التسجيل</h6>
                        <table class="table table-striped">
                            <tr>
                                <th class="bg-light" width="40%">رقم القيد</th>
                                <td>
                                    @if($localCompany->registration_number)
                                        <strong class="text-primary">{{ $localCompany->registration_number }}</strong>
                                    @else
                                        <span class="text-muted">سيُولد عند القبول</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th class="bg-light">تاريخ التسجيل</th><td>{{ $localCompany->registration_date?->format('Y-m-d') ?? '-' }}</td></tr>
                            @if($localCompany->last_renewal_date)
                            <tr>
                                <th class="bg-light">آخر تاريخ تجديد</th>
                                <td>
                                    <span class="badge bg-success">{{ $localCompany->last_renewal_date->format('Y-m-d') }}</span>
                                </td>
                            </tr>
                            @endif
                            @if($localCompany->expires_at)
                            <tr>
                                <th class="bg-light">تاريخ انتهاء الصلاحية</th>
                                <td>
                                    <span class="badge bg-{{ $localCompany->isExpired() ? 'danger' : 'info' }}">
                                        {{ $localCompany->expires_at->format('Y-m-d') }}
                                    </span>
                                    @if($localCompany->isExpired())
                                        <small class="text-danger d-block mt-1">منتهية الصلاحية</small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr><th class="bg-light">نوع الترخيص</th><td>{{ $localCompany->license_type_name }}</td></tr>
                            <tr><th class="bg-light">تخصص الترخيص</th><td>{{ $localCompany->license_specialty_name }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">التراخيص الرسمية</h6>
                        <table class="table table-striped">
                            <tr><th class="bg-light" width="40%">رقم الترخيص</th><td>{{ $localCompany->license_number ?? '-' }}</td></tr>
                            <tr><th class="bg-light">جهة الإصدار</th><td>{{ $localCompany->license_issuer ?? '-' }}</td></tr>
                            <tr><th class="bg-light">رقم الرقابة على الأدوية</th><td>{{ $localCompany->food_drug_registration_number ?? '-' }}</td></tr>
                            <tr><th class="bg-light">رقم الغرفة التجارية</th><td>{{ $localCompany->chamber_of_commerce_number ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-manager">
                <table class="table table-striped">
                    <tr>
                        <th class="bg-light" width="15%">الاسم</th>
                        <td width="35%">{{ $localCompany->manager_name }}</td>
                        <th class="bg-light" width="15%">الصفة</th>
                        <td width="35%">{{ $localCompany->manager_position ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">رقم الهاتف</th>
                        <td dir="ltr" class="text-end">{{ $localCompany->manager_phone }}</td>
                        <th class="bg-light">البريد الإلكتروني</th>
                        <td>{{ $localCompany->manager_email ?? '-' }}</td>
                    </tr>
                    @if($localCompany->user_id)
                    <tr>
                        <th class="bg-light">حالة الحساب</th>
                        <td colspan="3"><span class="badge bg-success">تم إنشاء حساب للمدير</span></td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="tab-pane fade" id="tab-representative">
                @if($localCompany->representative)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="bg-light" width="15%">الاسم</th>
                            <td width="35%">{{ $localCompany->representative->name }}</td>
                            <th class="bg-light" width="15%">البريد الإلكتروني</th>
                            <td width="35%">{{ $localCompany->representative->email }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">رقم الهاتف</th>
                            <td dir="ltr" class="text-end">{{ $localCompany->representative->phone ?? '-' }}</td>
                            <th class="bg-light">الجنسية</th>
                            <td>{{ $localCompany->representative->nationality ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">تاريخ التسجيل</th>
                            <td>{{ $localCompany->representative->created_at->format('Y-m-d h:i A') }}</td>
                            <th class="bg-light">حالة الحساب</th>
                            <td>
                                @if($localCompany->representative->email_verified_at)
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
                        @if($localCompany->hasAllRequiredDocuments())
                            <span class="badge bg-success ms-2"><i class="ti ti-check me-1"></i>مكتمل</span>
                        @endif
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="ti ti-upload me-1"></i> رفع مستند جديد
                    </button>
                </div>

                @if($localCompany->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">معاينة</th>
                                <th width="30%">نوع المستند</th>
                                <th width="15%">اسم الملف</th>
                                <th width="10%">الحجم</th>
                                <th width="15%">تاريخ الرفع</th>
                                <th width="15%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->documents as $index => $document)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    @if($document->isImage())
                                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" data-bs-toggle="tooltip" title="انقر للتكبير">
                                            <img src="{{ Storage::url($document->file_path) }}" alt="{{ $document->display_name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        </a>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 5px;">
                                            <i class="ti {{ $document->file_icon }} fs-3"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $document->display_name }}</strong>
                                        @if($document->notes)
                                            <br><small class="text-muted">{{ Str::limit($document->notes, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $document->original_name }}">
                                        {{ Str::limit($document->original_name, 20) }}
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
                                        @if($document->isImage())
                                            <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn btn-outline-info" title="عرض">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.local-companies.documents.download', [$localCompany, $document]) }}" class="btn btn-outline-primary" title="تحميل">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-doc" data-id="{{ $document->id }}" data-name="{{ $document->display_name }}" title="حذف">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $document->id }}" action="{{ route('admin.local-companies.documents.destroy', [$localCompany, $document]) }}" method="POST" style="display: none;">
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
                    <i class="ti ti-folder-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">لا توجد مستندات مرفوعة</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="ti ti-upload me-1"></i> رفع المستند الأول
                    </button>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoices">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 text-muted">
                        <i class="ti ti-file-invoice me-1"></i>
                        الفواتير
                        @if($localCompany->hasUnpaidInvoices())
                            <span class="badge bg-danger ms-2">مستحق: {{ number_format($localCompany->getUnpaidInvoicesTotal(), 2) }} د.ل</span>
                        @endif
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                        <i class="ti ti-plus me-1"></i> إضافة فاتورة
                    </button>
                </div>

                @if($localCompany->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th width="12%">رقم الفاتورة</th>
                                <th width="15%">النوع</th>
                                <th width="20%">الوصف</th>
                                <th width="10%">المبلغ</th>
                                <th width="10%">الحالة</th>
                                <th width="13%">التاريخ</th>
                                <th width="20%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>{{ $invoice->type_name }}</td>
                                <td>
                                    {{ $invoice->description }}
                                    @if($invoice->notes)
                                        <br><small class="text-muted">{{ Str::limit($invoice->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> د.ل</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_name }}</span>
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
                                        @if($invoice->receipt_path && $invoice->status == 'pending_review')
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-company-id="{{ $localCompany->id }}">
                                                <i class="ti ti-check me-1"></i>موافقة على الإيصال
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                                <i class="ti ti-x me-1"></i>رفض الإيصال
                                            </button>
                                        @elseif($invoice->status == 'paid')
                                            <button type="button" class="btn btn-outline-warning btn-sm btn-mark-unpaid" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-x me-1"></i>إلغاء الدفع
                                            </button>
                                        @endif
                                        @if($invoice->receipt_path)
                                            <a href="{{ route('admin.local-companies.invoices.download-receipt', [$localCompany, $invoice]) }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-download me-1"></i>تحميل الإيصال
                                            </a>
                                        @elseif($invoice->status == 'unpaid')
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-upload-receipt" data-id="{{ $invoice->id }}">
                                                <i class="ti ti-upload me-1"></i>رفع إيصال
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit-invoice" data-id="{{ $invoice->id }}" data-type="{{ $invoice->type }}" data-description="{{ $invoice->description }}" data-amount="{{ $invoice->amount }}" data-due_date="{{ $invoice->due_date?->format('Y-m-d') }}" data-notes="{{ $invoice->notes }}">
                                            <i class="ti ti-edit me-1"></i>تعديل
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-invoice" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                            <i class="ti ti-trash me-1"></i>حذف
                                        </button>
                                    </div>

                                    <form id="mark-paid-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.mark-paid', [$localCompany, $invoice]) }}" method="POST" style="display: none;" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="receipt" id="receipt-input-{{ $invoice->id }}" class="d-none">
                                    </form>
                                    <form id="mark-unpaid-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.mark-unpaid', [$localCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    <form id="delete-invoice-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.destroy', [$localCompany, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <form id="upload-receipt-form-{{ $invoice->id }}" action="{{ route('admin.local-companies.invoices.upload-receipt', [$localCompany, $invoice]) }}" method="POST" style="display: none;" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="receipt" id="upload-receipt-input-{{ $invoice->id }}" class="d-none">
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

            <div class="tab-pane fade" id="tab-activities">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-history me-1"></i>
                    سجل الحركات
                </h6>

                @if($localCompany->activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th width="5%">#</th>
                                <th width="45%">الوصف</th>
                                <th width="25%">التاريخ</th>
                                <th width="25%">المستخدم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localCompany->activities as $index => $activity)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $activity->action_color }}">
                                        <i class="ti {{ $activity->action_icon }}"></i>
                                    </span>
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    <small>{{ $activity->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $activity->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($activity->user)
                                        <small>{{ $activity->user->name }}</small>
                                    @else
                                        <small class="text-muted">النظام</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-history-off fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">لا توجد حركات مسجلة</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.reject', $localCompany) }}" method="POST">
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

<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.documents.store', $localCompany) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-upload me-2"></i>رفع مستند جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نوع المستند <span class="text-danger">*</span></label>
                        <select name="document_type" id="document_type" class="form-select" required>
                            <option value="">اختر نوع المستند</option>
                            @php
                                $uploadedTypes = $localCompany->documents->pluck('document_type')->toArray();
                            @endphp
                            @foreach(\App\Models\LocalCompanyDocument::documentTypes() as $key => $value)
                                @php
                                    $isUploaded = in_array($key, $uploadedTypes) && $key != 'other';
                                @endphp
                                <option value="{{ $key }}" {{ $isUploaded ? 'disabled' : '' }}>
                                    {{ $value }}
                                    @if($isUploaded) (تم الرفع) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="custom_name_wrapper">
                        <label class="form-label">اسم المستند <span class="text-danger">*</span></label>
                        <input type="text" name="custom_name" id="custom_name" class="form-control" placeholder="أدخل اسم المستند">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الملف <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                        <small class="text-muted">الحد الأقصى: 10 ميجابايت | الأنواع المدعومة: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, ZIP, RAR</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية (اختياري)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i>رفع المستند</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-companies.invoices.store', $localCompany) }}" method="POST">
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

                    <input type="hidden" name="type" value="other">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i>إضافة الفاتورة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editInvoiceForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background-color: #f8f9fa;">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>تعديل الفاتورة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الوصف <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="edit_invoice_description" class="form-control" placeholder="وصف الفاتورة" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="edit_invoice_amount" class="form-control" step="0.01" min="0" required>
                            <span class="input-group-text">د.ل</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" id="edit_invoice_due_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" id="edit_invoice_notes" class="form-control" rows="2" placeholder="ملاحظات إضافية (اختياري)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <strong>تنبيه:</strong> عند رفض الإيصال، سيتم تغيير حالة الشركة إلى "مرفوضة" وسيتم إرسال إشعار بالبريد الإلكتروني إلى ممثل الشركة.
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
const tabKey = 'localCompanyTab_{{ $localCompany->id }}';
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

document.querySelector('.approve-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const isPreRegistered = {{ $localCompany->is_pre_registered ? 'true' : 'false' }};
    const preRegNumber = '{{ $localCompany->pre_registration_number }}';
    const preRegYear = '{{ $localCompany->pre_registration_year }}';

    if (isPreRegistered) {
        Swal.fire({
            title: 'شركة مسجلة مسبقاً',
            html: `
                <div style="text-align: right;">
                    <p><strong>رقم القيد المدخل:</strong> ${preRegNumber}</p>
                    <p><strong>سنة التسجيل:</strong> ${preRegYear}</p>
                    <hr>
                    <p class="text-warning"><i class="ti ti-alert-circle"></i> يرجى التحقق من أن رقم القيد المدخل صحيح</p>
                    <hr>
                    <p><strong>هل تود إنشاء فاتورة تجديد لهذه الشركة؟</strong></p>
                </div>
            `,
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonColor: '#198754',
            denyButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، إنشاء فاتورة تجديد',
            denyButtonText: 'لا، إدخال تاريخ يدوي',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'text-end'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'create_renewal_invoice';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'إدخال تاريخ آخر تجديد',
                    html: `
                        <div style="text-align: right;">
                            <label for="last_renewal_date" class="form-label">آخر تاريخ تجديد</label>
                            <input type="date" id="last_renewal_date" class="form-control" value="${new Date().getFullYear()}-01-01" required>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'موافقة',
                    cancelButtonText: 'إلغاء',
                    preConfirm: () => {
                        const date = document.getElementById('last_renewal_date').value;
                        if (!date) {
                            Swal.showValidationMessage('يرجى إدخال التاريخ');
                            return false;
                        }
                        return date;
                    }
                }).then((dateResult) => {
                    if (dateResult.isConfirmed) {
                        const dateInput = document.createElement('input');
                        dateInput.type = 'hidden';
                        dateInput.name = 'last_renewal_date';
                        dateInput.value = dateResult.value;
                        form.appendChild(dateInput);
                        form.submit();
                    }
                });
            }
        });
    } else {
        Swal.fire({
            title: 'تأكيد القبول',
            text: 'هل أنت متأكد من قبول هذه الشركة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }
});

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

document.getElementById('document_type')?.addEventListener('change', function() {
    const customNameWrapper = document.getElementById('custom_name_wrapper');
    const customNameInput = document.getElementById('custom_name');
    if (this.value == 'other') {
        customNameWrapper.classList.remove('d-none');
        customNameInput.setAttribute('required', 'required');
    } else {
        customNameWrapper.classList.add('d-none');
        customNameInput.removeAttribute('required');
    }
});

document.querySelectorAll('.btn-delete-doc').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const docId = this.getAttribute('data-id');
        const docName = this.getAttribute('data-name');
        Swal.fire({
            title: 'تأكيد الحذف',
            text: 'هل أنت متأكد من حذف المستند: ' + docName + '؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + docId).submit();
            }
        });
    });
});

// Mark as paid
document.querySelectorAll('.btn-mark-paid').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        Swal.fire({
            title: 'تأكيد الدفع',
            text: 'هل تريد رفع إيصال الدفع؟',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#198754',
            denyButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، رفع إيصال',
            denyButtonText: 'بدون إيصال',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('receipt-input-' + invoiceId).click();
            } else if (result.isDenied) {
                document.getElementById('mark-paid-form-' + invoiceId).submit();
            }
        });
    });
});

// Receipt file selected
document.querySelectorAll('[id^="receipt-input-"]').forEach(function(input) {
    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            this.closest('form').submit();
        }
    });
});

// Mark as unpaid
document.querySelectorAll('.btn-mark-unpaid').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        Swal.fire({
            title: 'إلغاء الدفع',
            text: 'هل أنت متأكد من إلغاء دفع هذه الفاتورة؟ سيتم حذف الإيصال إن وجد.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، إلغاء الدفع',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('mark-unpaid-form-' + invoiceId).submit();
            }
        });
    });
});

// Upload receipt
document.querySelectorAll('.btn-upload-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        document.getElementById('upload-receipt-input-' + invoiceId).click();
    });
});

document.querySelectorAll('[id^="upload-receipt-input-"]').forEach(function(input) {
    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            this.closest('form').submit();
        }
    });
});

// Edit invoice
document.querySelectorAll('.btn-edit-invoice').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const description = this.getAttribute('data-description');
        const amount = this.getAttribute('data-amount');
        const dueDate = this.getAttribute('data-due_date');
        const notes = this.getAttribute('data-notes');

        document.getElementById('editInvoiceForm').action = '{{ url("admin/local-companies/" . $localCompany->id . "/invoices") }}/' + invoiceId;
        document.getElementById('edit_invoice_description').value = description;
        document.getElementById('edit_invoice_amount').value = amount;
        document.getElementById('edit_invoice_due_date').value = dueDate || '';
        document.getElementById('edit_invoice_notes').value = notes || '';

        new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
    });
});

// Approve receipt and activate company
document.querySelectorAll('.btn-approve-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const companyId = this.getAttribute('data-company-id');
        Swal.fire({
            title: 'الموافقة على الإيصال وتفعيل الشركة',
            text: 'هل أنت متأكد من الموافقة على الإيصال؟ سيتم تفعيل الشركة تلقائياً وإرسال إشعار بالبريد الإلكتروني.',
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
                form.action = '{{ url("admin/local-companies") }}/' + companyId + '/invoices/' + invoiceId + '/approve-receipt';

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
        const invoiceNumber = this.getAttribute('data-number');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/local-companies/" . $localCompany->id . "/invoices") }}/' + invoiceId + '/reject-receipt';
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
