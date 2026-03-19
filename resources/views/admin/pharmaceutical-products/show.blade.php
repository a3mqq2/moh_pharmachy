@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'تفاصيل الصنف الدوائي: ' . $product->product_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pharmaceutical-products.index') }}">الأصناف الدوائية</a></li>
    <li class="breadcrumb-item active">{{ $product->product_name }}</li>
@endsection

@section('content')
<div class="show-header mt-3 mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-2"><i class="ti ti-pill me-2 text-primary"></i>{{ $product->product_name }}</h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span>
                <span class="badge bg-info">{{ $product->pharmaceutical_form }}</span>
                @if($product->concentration)
                    <span class="badge bg-dark">{{ $product->concentration }}</span>
                @endif
                @if($product->registration_number)
                    <span class="badge bg-dark">{{ $product->registration_number }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($product->status == 'active')
                <a href="{{ route('admin.pharmaceutical-products.certificate', $product) }}" target="_blank" class="btn btn-primary">
                    <i class="ti ti-certificate me-1"></i>طباعة الشهادة
                </a>
            @endif
            @if($product->status == 'pending_review')
                <form action="{{ route('admin.pharmaceutical-products.approve', $product) }}" method="POST" class="d-inline preliminary-approve-form">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i>موافقة مبدئية</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x me-1"></i>رفض
                </button>
            @elseif($product->status == 'pending_final_approval')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finalApproveModal">
                    <i class="ti ti-check-circle me-1"></i>موافقة نهائية
                </button>
            @elseif($product->status == 'rejected')
                <form action="{{ route('admin.pharmaceutical-products.approve', $product) }}" method="POST" class="d-inline restore-form">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>إعادة للمراجعة</button>
                </form>
            @endif
            <a href="{{ route('admin.pharmaceutical-products.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-right me-1"></i>رجوع</a>
        </div>
    </div>
</div>


@if($product->status == 'rejected' && $product->rejection_reason)
<div class="alert alert-danger">
    <strong><i class="ti ti-alert-circle me-1"></i>سبب الرفض:</strong> {{ $product->rejection_reason }}
</div>
@endif

<div class="card">
    <div class="card-header p-0 border-bottom">
        <ul class="nav nav-tabs" id="productTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basic"><i class="ti ti-info-circle me-1"></i>المعلومات الأساسية</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-detailed">
                    <i class="ti ti-list-details me-1"></i>البيانات التفصيلية
                    @if($product->hasCompleteDetailedInfo())
                        <span class="badge bg-success rounded-pill ms-1"><i class="ti ti-check"></i></span>
                    @else
                        <span class="badge bg-warning rounded-pill ms-1"><i class="ti ti-dots"></i></span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="ti ti-files me-1"></i>المستندات
                    <span class="badge bg-info rounded-pill ms-1">{{ $product->documents()->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoice">
                    <i class="ti ti-file-invoice me-1"></i>الفاتورة
                    @if($product->hasUnpaidInvoice())
                        <span class="badge bg-danger rounded-pill ms-1">1</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-companies"><i class="ti ti-building me-1"></i>معلومات الشركات</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-basic">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-pill me-2"></i>بيانات الصنف الدوائي</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">الاسم التجاري</th><td>{{ $product->product_name }}</td></tr>
                            <tr><th class="bg-light">الاسم العلمي</th><td>{{ $product->scientific_name }}</td></tr>
                            <tr><th class="bg-light">الشكل الصيدلاني</th><td>{{ $product->pharmaceutical_form }}</td></tr>
                            <tr><th class="bg-light">التركيز</th><td>{{ $product->concentration }}</td></tr>
                            <tr><th class="bg-light">طريقة الاستعمال</th><td>{{ $product->usage_methods_text }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clipboard me-2"></i>معلومات التسجيل</h6>
                        <table class="table table-striped info-table">
                            <tr>
                                <th class="bg-light" width="40%">رقم القيد</th>
                                <td>
                                    @if($product->registration_number)
                                        <span class="fw-bold text-primary fs-6">{{ $product->registration_number }}</span>
                                    @else
                                        <span class="text-muted">لم يُصدر بعد</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th class="bg-light" width="40%">تاريخ التقديم</th><td>{{ $product->created_at->format('Y-m-d') }}</td></tr>
                            <tr><th class="bg-light">الحالة</th><td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_name }}</span></td></tr>
                            @if($product->reviewed_by)
                            <tr><th class="bg-light">تمت المراجعة بواسطة</th><td>{{ $product->reviewedBy->name ?? 'غير محدد' }}</td></tr>
                            <tr><th class="bg-light">تاريخ المراجعة</th><td>{{ $product->reviewed_at ? $product->reviewed_at->format('Y-m-d h:i A') : 'غير محدد' }}</td></tr>
                            @endif
                            @if($product->preliminary_approved_by)
                            <tr><th class="bg-light">الموافقة المبدئية بواسطة</th><td>{{ $product->preliminaryApprovedBy->name ?? 'غير محدد' }}</td></tr>
                            <tr><th class="bg-light">تاريخ الموافقة المبدئية</th><td>{{ $product->preliminary_approved_at ? $product->preliminary_approved_at->format('Y-m-d h:i A') : 'غير محدد' }}</td></tr>
                            @endif
                            @if($product->final_approved_by)
                            <tr><th class="bg-light">الموافقة النهائية بواسطة</th><td>{{ $product->finalApprovedBy->name ?? 'غير محدد' }}</td></tr>
                            <tr><th class="bg-light">تاريخ الموافقة النهائية</th><td>{{ $product->final_approved_at ? $product->final_approved_at->format('Y-m-d h:i A') : 'غير محدد' }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-detailed">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-list-details me-1"></i>
                    البيانات التفصيلية للصنف الدوائي
                </h6>

                @if($product->hasCompleteDetailedInfo())
                <div class="alert alert-success mb-4">
                    <i class="ti ti-circle-check me-2"></i>
                    تم استكمال جميع البيانات التفصيلية المطلوبة
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-info-circle me-2"></i>المعلومات الأساسية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">الاسم التجاري</th><td>{{ $product->trade_name }}</td></tr>
                            <tr><th class="bg-light">البلد المنشأ</th><td>{{ $product->origin }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-package me-2"></i>معلومات التعبئة والتغليف</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">الوحدة</th><td>{{ $product->unit }}</td></tr>
                            <tr><th class="bg-light">نوع التعبئة</th><td>{{ $product->packaging }}</td></tr>
                            <tr><th class="bg-light">كمية العبوة</th><td>{{ $product->quantity }}</td></tr>
                            @if($product->unit_price)
                            <tr><th class="bg-light">سعر الوحدة</th><td>{{ number_format($product->unit_price, 2) }} د.ل</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clock me-2"></i>الصلاحية والتخزين</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">مدة الصلاحية</th><td>{{ $product->shelf_life_months }} شهر</td></tr>
                            <tr><th class="bg-light">ظروف التخزين</th><td>{{ $product->storage_conditions }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-dots me-2"></i>معلومات إضافية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">نوع البيع</th><td>{{ $product->free_sale }}</td></tr>
                            <tr><th class="bg-light">العينات</th><td>{{ $product->samples }}</td></tr>
                            <tr><th class="bg-light">المرجع الدستوري</th><td>{{ $product->pharmacopeal_ref }}</td></tr>
                            <tr><th class="bg-light">تصنيف الصنف</th><td>{{ $product->item_classification }}</td></tr>
                        </table>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    لم يتم استكمال البيانات التفصيلية بعد. في انتظار قيام الممثل باستكمال البيانات.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-info-circle me-2"></i>المعلومات الأساسية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">الاسم التجاري</th><td>{{ $product->trade_name ?: '-' }}</td></tr>
                            <tr><th class="bg-light">البلد المنشأ</th><td>{{ $product->origin ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-package me-2"></i>معلومات التعبئة والتغليف</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">الوحدة</th><td>{{ $product->unit ?: '-' }}</td></tr>
                            <tr><th class="bg-light">نوع التعبئة</th><td>{{ $product->packaging ?: '-' }}</td></tr>
                            <tr><th class="bg-light">كمية العبوة</th><td>{{ $product->quantity ?: '-' }}</td></tr>
                            <tr><th class="bg-light">سعر الوحدة</th><td>{{ $product->unit_price ? number_format($product->unit_price, 2) . ' د.ل' : '-' }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-clock me-2"></i>الصلاحية والتخزين</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">مدة الصلاحية</th><td>{{ $product->shelf_life_months ? $product->shelf_life_months . ' شهر' : '-' }}</td></tr>
                            <tr><th class="bg-light">ظروف التخزين</th><td>{{ $product->storage_conditions ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-dots me-2"></i>معلومات إضافية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">نوع البيع</th><td>{{ $product->free_sale ?: '-' }}</td></tr>
                            <tr><th class="bg-light">العينات</th><td>{{ $product->samples ?: '-' }}</td></tr>
                            <tr><th class="bg-light">المرجع الدستوري</th><td>{{ $product->pharmacopeal_ref ?: '-' }}</td></tr>
                            <tr><th class="bg-light">تصنيف الصنف</th><td>{{ $product->item_classification ?: '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-documents">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-folder me-1"></i>
                    المستندات والملفات
                </h6>

                @if($product->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">نوع المستند</th>
                                <th width="20%">اسم الملف</th>
                                <th width="10%">الحجم</th>
                                <th width="15%">تاريخ الرفع</th>
                                <th width="20%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->documents as $index => $document)
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
                                        <button type="button" class="btn btn-outline-info btn-doc-preview" title="عرض"
                                            data-file-url="{{ $document->file_url }}"
                                            data-file-name="{{ $document->original_name ?? $document->document_type_name }}"
                                            data-download-url="{{ $document->file_url }}">
                                            <i class="ti ti-eye"></i>
                                        </button>
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
                    <p class="text-muted">لا توجد مستندات مرفوعة</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-invoice">
                <h6 class="mb-4 text-muted">
                    <i class="ti ti-file-invoice me-1"></i>
                    الفاتورة
                    @if($product->hasUnpaidInvoice())
                        <span class="badge bg-danger ms-2">مستحق: {{ number_format($product->getUnpaidInvoice()->amount, 2) }} د.ل</span>
                    @endif
                </h6>

                @if($product->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="15%">رقم الفاتورة</th>
                                <th width="15%">المبلغ</th>
                                <th width="15%">الحالة</th>
                                <th width="15%">التاريخ</th>
                                <th width="20%" class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->invoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td><strong>{{ number_format($invoice->amount, 2) }}</strong> د.ل</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_name }}</span>
                                    @if($invoice->paid_at)
                                        <br><small class="text-muted">{{ $invoice->paid_at->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $invoice->created_at->format('Y-m-d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $invoice->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($invoice->receipt_path && $invoice->status == 'pending_review')
                                            <button type="button" class="btn btn-outline-info btn-sm btn-doc-preview"
                                                data-file-url="{{ $invoice->receipt_url }}"
                                                data-file-name="إيصال - {{ $invoice->invoice_number }}"
                                                data-download-url="{{ $invoice->receipt_url }}">
                                                <i class="ti ti-eye me-1"></i>عرض الإيصال
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm btn-approve-receipt" data-id="{{ $invoice->id }}" data-product-id="{{ $product->id }}">
                                                <i class="ti ti-check me-1"></i>موافقة
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-reject-receipt" data-id="{{ $invoice->id }}" data-number="{{ $invoice->invoice_number }}">
                                                <i class="ti ti-x me-1"></i>رفض
                                            </button>
                                        @elseif($invoice->receipt_path && $invoice->status == 'paid')
                                            <button type="button" class="btn btn-outline-info btn-sm btn-doc-preview"
                                                data-file-url="{{ $invoice->receipt_url }}"
                                                data-file-name="إيصال - {{ $invoice->invoice_number }}"
                                                data-download-url="{{ $invoice->receipt_url }}">
                                                <i class="ti ti-eye me-1"></i>عرض الإيصال
                                            </button>
                                        @elseif($invoice->status == 'unpaid')
                                            <span class="text-muted">في انتظار رفع الإيصال</span>
                                        @endif
                                    </div>

                                    <form id="approve-receipt-form-{{ $invoice->id }}" action="{{ route('admin.pharmaceutical-products.invoices.approve-receipt', [$product, $invoice]) }}" method="POST" style="display: none;">
                                        @csrf
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
                    <p class="text-muted">لا توجد فواتير</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-companies">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-world me-2"></i>الشركة الأجنبية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">اسم الشركة</th><td>{{ $product->foreignCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">الدولة</th><td>{{ $product->foreignCompany->country }}</td></tr>
                            <tr><th class="bg-light">البريد الإلكتروني</th><td>{{ $product->foreignCompany->email }}</td></tr>
                            @if($product->foreignCompany->phone)
                            <tr><th class="bg-light">الهاتف</th><td dir="ltr" class="text-end">{{ $product->foreignCompany->phone }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="ti ti-building-skyscraper me-2"></i>الشركة المحلية</h6>
                        <table class="table table-striped info-table">
                            <tr><th class="bg-light" width="40%">اسم الشركة</th><td>{{ $product->foreignCompany->localCompany->company_name }}</td></tr>
                            <tr><th class="bg-light">رقم السجل التجاري</th><td>{{ $product->foreignCompany->localCompany->commercial_registration_number }}</td></tr>
                            <tr><th class="bg-light">البريد الإلكتروني</th><td>{{ $product->foreignCompany->localCompany->email }}</td></tr>
                            @if($product->foreignCompany->localCompany->phone)
                            <tr><th class="bg-light">الهاتف</th><td dir="ltr" class="text-end">{{ $product->foreignCompany->localCompany->phone }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="section-title"><i class="ti ti-user me-2"></i>ممثل الشركة</h6>
                    <table class="table table-striped info-table">
                        <tr>
                            <th class="bg-light" width="15%">الاسم</th>
                            <td width="35%">{{ $product->representative->name }}</td>
                            <th class="bg-light" width="15%">البريد الإلكتروني</th>
                            <td width="35%">{{ $product->representative->email }}</td>
                        </tr>
                        @if($product->representative->phone)
                        <tr>
                            <th class="bg-light">رقم الهاتف</th>
                            <td dir="ltr" class="text-end">{{ $product->representative->phone }}</td>
                            <th class="bg-light">الجنسية</th>
                            <td>{{ $product->representative->nationality ?? '-' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="bg-light">تاريخ التسجيل</th>
                            <td>{{ $product->representative->created_at->format('Y-m-d h:i A') }}</td>
                            <th class="bg-light">حالة الحساب</th>
                            <td>
                                @if($product->representative->email_verified_at)
                                    <span class="badge bg-success">مفعل</span>
                                @else
                                    <span class="badge bg-warning">غير مفعل</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($product->status == 'pending_review')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pharmaceutical-products.reject', $product) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">رفض الصنف الدوائي</h5>
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
@endif

@if($product->status == 'pending_final_approval')
<div class="modal fade" id="finalApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pharmaceutical-products.final-approve', $product) }}" method="POST" class="final-approve-form">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">الموافقة النهائية على الصنف الدوائي</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>سيتم إنشاء فاتورة وإرسال إشعار للممثل.</p>
                    <hr>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_pre_registered" value="1" id="productPreRegistered">
                            <label class="form-check-label" for="productPreRegistered">صنف مسجل مسبقاً (قبل النظام)</label>
                        </div>
                    </div>
                    <div id="productPreRegFields" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">سنة التسجيل <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_year" class="form-control" min="1990" max="{{ date('Y') }}" placeholder="مثال: 2020">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الرقم التسلسلي <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_sequence" class="form-control" min="1" placeholder="مثال: 15">
                            </div>
                        </div>
                        <div class="alert alert-light py-2">
                            <small>رقم القيد: <strong id="pharmaPreRegPreview">-</strong></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تأكيد الموافقة النهائية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="rejectReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectReceiptForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">رفض إيصال الدفع</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <strong>تنبيه:</strong> عند رفض الإيصال، سيتم تغيير حالة الصنف إلى "مرفوض" وسيتم إرسال إشعار للممثل.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب رفض الإيصال <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="اكتب سبب رفض الإيصال بالتفصيل..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tabKey = 'pharmaceuticalProductTab_{{ $product->id }}';
const savedTab = sessionStorage.getItem(tabKey);
if (savedTab) {
    const tabButton = document.querySelector('[data-bs-target="' + savedTab + '"]');
    if (tabButton) {
        const tab = new bootstrap.Tab(tabButton);
        tab.show();
    }
}

document.querySelectorAll('#productTabs button[data-bs-toggle="tab"]').forEach(function(tabButton) {
    tabButton.addEventListener('shown.bs.tab', function(e) {
        sessionStorage.setItem(tabKey, e.target.getAttribute('data-bs-target'));
    });
});

document.querySelector('.preliminary-approve-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'تأكيد الموافقة المبدئية',
        text: 'سيتم إرسال إشعار للممثل لاستكمال البيانات التفصيلية. هل تريد المتابعة؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، موافقة مبدئية',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
});

document.getElementById('productPreRegistered')?.addEventListener('change', function() {
    document.getElementById('productPreRegFields').style.display = this.checked ? '' : 'none';
});

function updatePharmaPreRegPreview() {
    const year = document.querySelector('#finalApproveModal input[name="pre_registration_year"]')?.value;
    const seq = document.querySelector('#finalApproveModal input[name="pre_registration_sequence"]')?.value;
    const preview = document.getElementById('pharmaPreRegPreview');
    if (preview) {
        preview.textContent = (year && seq) ? year + '-' + seq : '-';
    }
}
document.querySelector('#finalApproveModal input[name="pre_registration_year"]')?.addEventListener('input', updatePharmaPreRegPreview);
document.querySelector('#finalApproveModal input[name="pre_registration_sequence"]')?.addEventListener('input', updatePharmaPreRegPreview);

document.querySelector('.restore-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'إعادة للمراجعة',
        text: 'هل أنت متأكد من إعادة هذا الصنف الدوائي للمراجعة؟',
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

document.querySelectorAll('.btn-approve-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const productId = this.getAttribute('data-product-id');
        Swal.fire({
            title: 'الموافقة على الإيصال وتفعيل الصنف',
            text: 'هل أنت متأكد من الموافقة على الإيصال؟ سيتم تفعيل الصنف الدوائي تلقائياً.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، موافق',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('approve-receipt-form-' + invoiceId).submit();
            }
        });
    });
});

document.querySelectorAll('.btn-reject-receipt').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const invoiceId = this.getAttribute('data-id');
        const invoiceNumber = this.getAttribute('data-number');

        document.getElementById('rejectReceiptForm').action = '{{ url("admin/pharmaceutical-products/" . $product->id . "/invoices") }}/' + invoiceId + '/reject-receipt';

        new bootstrap.Modal(document.getElementById('rejectReceiptModal')).show();
    });
});
</script>
@endpush
