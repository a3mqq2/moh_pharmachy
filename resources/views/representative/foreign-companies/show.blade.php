@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.auth')

@section('title', 'تفاصيل الشركة الأجنبية')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.foreign-companies.index') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ $company->company_name }}</h1>
                <p>{{ $company->country }} - {{ $company->entity_type_name }}</p>
            </div>
        </div>
        <div class="header-actions">
            <span class="badge {{ $company->status_badge_class }}">
                {{ $company->status_name }}
            </span>
            @if(in_array($company->status, ['rejected', 'uploading_documents']))
                <a href="{{ route('representative.foreign-companies.edit', $company) }}" class="btn btn-secondary">
                    <i class="ti ti-edit"></i>
                    تعديل البيانات
                </a>
            @endif
        </div>
    </div>

    

    @if($company->status == 'rejected' && $company->rejection_reason)
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <h4><i class="ti ti-alert-circle"></i> تم رفض الطلب</h4>
            <p><strong>سبب الرفض:</strong> {{ $company->rejection_reason }}</p>
            <p>يمكنك تعديل البيانات والمستندات ثم إعادة التقديم للمراجعة.</p>
        </div>
    @endif

    @if(in_array($company->status, ['uploading_documents', 'rejected']) && $company->hasAllRequiredDocuments())
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <h4><i class="ti ti-check-circle"></i> {{ $company->status == 'rejected' ? 'جاهز لإعادة التقديم' : 'جميع المستندات الإلزامية متوفرة' }}</h4>
            <p>{{ $company->status == 'rejected' ? 'إن قمت بجميع التعديلات المطلوبة، يمكنك إعادة إرسال الطلب للمراجعة.' : 'تم رفع جميع المستندات المطلوبة. يمكنك الآن إرسال الطلب للمراجعة.' }}</p>
            <form action="{{ route('representative.foreign-companies.submit-for-review', $company) }}" method="POST" style="margin-top: 15px;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-send"></i>
                    {{ $company->status == 'rejected' ? 'إعادة التقديم للمراجعة' : 'إرسال للمراجعة' }}
                </button>
            </form>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="tabs-container">
        <ul class="nav nav-tabs" id="companyTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                    <i class="ti ti-info-circle"></i>
                    معلومات الشركة
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="ti ti-file-certificate"></i>
                    المستندات
                    @if($company->documents->count() > 0)
                        <span class="tab-badge">{{ $company->documents->count() }}</span>
                    @endif
                </button>
            </li>
            @if($company->invoices->count() > 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                    <i class="ti ti-file-invoice"></i>
                    الفواتير
                    <span class="tab-badge">{{ $company->invoices->count() }}</span>
                </button>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="companyTabsContent">
            <!-- Tab 1: Company Information -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="ti ti-info-circle"></i> معلومات الشركة الأساسية</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">اسم الشركة:</span>
                                <span class="value">{{ $company->company_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">الدولة:</span>
                                <span class="value">{{ $company->country }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">نوع الكيان:</span>
                                <span class="value">{{ $company->entity_type_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">نوع النشاط:</span>
                                <span class="value">{{ $company->activity_type_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">الشركة المحلية (الوكيل):</span>
                                <span class="value">{{ $company->localCompany->company_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">عدد المنتجات:</span>
                                <span class="value">{{ $company->products_count }}</span>
                            </div>
                            @if($company->email)
                            <div class="info-item">
                                <span class="label">البريد الإلكتروني:</span>
                                <span class="value">{{ $company->email }}</span>
                            </div>
                            @endif
                            <div class="info-item full-width">
                                <span class="label">العنوان:</span>
                                <span class="value">{{ $company->address }}</span>
                            </div>
                            @if($company->registered_countries && count($company->registered_countries) > 0)
                            <div class="info-item full-width">
                                <span class="label">الدول المسجلة:</span>
                                <span class="value">
                                    @foreach($company->registered_countries as $country)
                                        <span class="country-tag">{{ $country }}</span>
                                    @endforeach
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Documents -->
            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="info-card">
        <div class="card-header">
            <h3><i class="ti ti-file-certificate"></i> المستندات المطلوبة</h3>
            @if(in_array($company->status, ['uploading_documents', 'rejected']))
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                <i class="ti ti-upload"></i>
                رفع مستند
            </button>
            @endif
        </div>
        <div class="card-body">
            @if($company->documents->count() > 0)
                <h5 style="margin: 25px 0 15px 0; color: #1f2937; font-size: 1rem;">
                    <i class="ti ti-files"></i> المستندات المرفوعة ({{ $company->documents->count() }})
                </h5>
                <div class="documents-list">
                    @foreach($company->documents as $document)
                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="ti ti-file-text"></i>
                            </div>
                            <div class="document-details">
                                <h4>{{ $document->document_type_name }}</h4>
                                <p class="text-muted">
                                    رفع بتاريخ: {{ $document->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="document-actions">
                            <span class="badge {{ $document->status_badge_class }}">
                                {{ $document->status_name }}
                            </span>
                            <button type="button" class="btn btn-sm btn-info btn-doc-preview" data-file-url="{{ Storage::url($document->file_path) }}" data-file-name="{{ $document->document_name }}" data-download-url="{{ route('representative.foreign-companies.documents.download', [$company, $document]) }}">
                                <i class="ti ti-eye"></i>
                            </button>
                            <a href="{{ route('representative.foreign-companies.documents.download', [$company, $document]) }}" class="btn btn-sm btn-secondary">
                                <i class="ti ti-download"></i>
                            </a>
                            @if(in_array($company->status, ['uploading_documents', 'rejected']) && $document->status == 'rejected')
                            <button type="button" class="btn btn-sm btn-warning" onclick="openReplaceModal({{ $document->id }}, '{{ $document->document_type_name }}')" title="استبدال المستند">
                                <i class="ti ti-refresh"></i>
                            </button>
                            @endif
                            @if(in_array($company->status, ['uploading_documents', 'rejected']) && $document->status != 'approved')
                            <form action="{{ route('representative.foreign-companies.documents.destroy', [$company, $document]) }}" method="POST" style="display: inline;" id="delete-doc-{{ $document->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('delete-doc-{{ $document->id }}')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                            
                            @endif
                            @if(!in_array($company->status, ['uploading_documents', 'rejected']))
                                @if($document->pendingUpdateRequest)
                                    <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="ti ti-clock me-1"></i>طلب تعديل معلق</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-warning text-white" onclick="openUpdateRequestModal({{ $document->id }}, '{{ $document->document_type_name }}', 'foreign_company_document')" title="طلب تعديل">
                                        <i class="ti ti-replace"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if($document->status == 'rejected' && $document->rejection_reason)
                    <div class="rejection-reason">
                        <i class="ti ti-alert-circle"></i>
                        <strong>سبب الرفض:</strong> {{ $document->rejection_reason }}
                    </div>
                    @endif
                    @endforeach
                </div>
            @else
                <div class="empty-state-small">
                    <i class="ti ti-file-x"></i>
                    <p>لم يتم رفع أي مستندات بعد</p>
                </div>
            @endif
        </div>
                </div>
            </div>

            <!-- Tab 3: Invoices -->
            @if($company->invoices->count() > 0)
            <div class="tab-pane fade" id="invoices" role="tabpanel">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="ti ti-file-invoice"></i> الفواتير</h3>
                    </div>
                    <div class="card-body">
                        <div class="documents-list">
                            @foreach($company->invoices as $invoice)
                            <div class="document-item">
                                <div class="document-info">
                                    <div class="document-icon" style="background: #3b82f6;">
                                        <i class="ti ti-file-invoice"></i>
                                    </div>
                                    <div class="document-details">
                                        <h4>فاتورة رقم: {{ $invoice->invoice_number }}</h4>
                                        <p class="text-muted">
                                            المبلغ: {{ number_format($invoice->amount, 2) }} د.ل
                                            - تاريخ الإصدار: {{ $invoice->created_at->format('Y-m-d') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="document-actions">
                                    <span class="badge {{ $invoice->status == 'paid' ? 'badge-success' : ($invoice->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                        {{ $invoice->status == 'paid' ? 'مدفوعة' : ($invoice->status == 'pending' ? 'معلقة' : 'ملغاة') }}
                                    </span>
                                    <a href="{{ route('representative.foreign-companies.invoices.show', [$company, $invoice]) }}" class="btn btn-sm btn-secondary">
                                        <i class="ti ti-eye"></i>
                                        عرض
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($company->status == 'rejected' && $company->rejection_reason)
    <div class="alert alert-danger">
        <h4><i class="ti ti-x-circle"></i> تم رفض الطلب</h4>
        <p><strong>السبب:</strong> {{ $company->rejection_reason }}</p>
    </div>
    @endif
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">رفع مستند جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('representative.foreign-companies.documents.store', $company) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Documents Status Summary -->
                    @php
                        // Get unique document types that have been uploaded
                        $uploadedTypes = $company->documents->pluck('document_type')->unique()->toArray();

                        // Repeatable document types (can upload multiple)
                        $repeatableTypes = ['cpp_certificate', 'fsc_certificate', 'registration_certificates', 'other'];

                        // Get non-repeatable types that have been uploaded
                        $uploadedNonRepeatableTypes = array_diff($uploadedTypes, $repeatableTypes);

                        // Calculate remaining types (excluding repeatable ones)
                        $remainingTypes = array_diff(array_keys($availableDocumentTypes), $uploadedNonRepeatableTypes);
                        $remainingCount = count($remainingTypes);

                        // Count total files
                        $totalFiles = $company->documents->count();
                    @endphp
                    <div class="documents-status-summary">
                        <div class="status-item uploaded">
                            <i class="ti ti-check-circle"></i>
                            <span>أنواع مرفوعة: <strong>{{ count($uploadedTypes) }}</strong></span>
                        </div>
                        <div class="status-item pending">
                            <i class="ti ti-clock"></i>
                            <span>أنواع متبقية: <strong>{{ $remainingCount }}</strong></span>
                        </div>
                        <div class="status-item total">
                            <i class="ti ti-files"></i>
                            <span>إجمالي الملفات: <strong>{{ $totalFiles }}</strong></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="document_type" class="required">نوع المستند</label>
                        <select name="document_type" id="document_type" class="form-control" required>
                            <option value="">-- اختر نوع المستند --</option>
                            @foreach($availableDocumentTypes as $type => $name)
                                @php
                                    $isRepeatable = in_array($type, $repeatableTypes);
                                    $isUploaded = in_array($type, $uploadedTypes);
                                    $shouldDisable = $isUploaded && !$isRepeatable;
                                @endphp
                                @if($shouldDisable)
                                    <option value="{{ $type }}" disabled>✓ {{ $name }} (تم الرفع)</option>
                                @else
                                    <option value="{{ $type }}">{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="help-text">
                            <strong>إلزامي:</strong> 9 مستندات أساسية + FDA أو EMEA •
                            <strong>اختياري:</strong> CPP, FSC, شهادات تسجيل، مستندات أخرى (يمكن رفع أكثر من نسخة)
                        </small>
                    </div>
                    <input type="hidden" name="document_name" id="document_name_hidden">
                    <div class="form-group">
                        <label for="file" class="required">الملف</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="help-text">الصيغ المسموحة: PDF, JPG, PNG (الحد الأقصى: 10MB)</small>
                    </div>
                    <div class="form-group">
                        <label for="notes">ملاحظات (اختياري)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload"></i>
                        رفع المستند
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="replaceDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">استبدال المستند</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="replaceDocumentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i>
                        <span>سيتم استبدال المستند: <strong id="replaceDocumentName"></strong></span>
                    </div>

                    <div class="form-group">
                        <label for="replace_document_name">اسم المستند</label>
                        <input type="text" name="document_name" id="replace_document_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="replace_file" class="required">الملف الجديد</label>
                        <input type="file" name="file" id="replace_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">الحد الأقصى: 10 ميجابايت | الصيغ المقبولة: PDF, JPG, PNG</small>
                    </div>

                    <div class="form-group">
                        <label for="replace_notes">ملاحظات (اختياري)</label>
                        <textarea name="notes" id="replace_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-refresh"></i>
                        استبدال المستند
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="updateRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('representative.document-update-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentable_type" id="ur_documentable_type">
                <input type="hidden" name="documentable_id" id="ur_documentable_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-replace me-2"></i>طلب تعديل مستند</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">المستند: <strong id="ur_doc_name"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">الملف الجديد <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <small class="text-muted">الحد الأقصى: 10 ميجابايت</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب التعديل</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="اذكر سبب طلب التعديل..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>إرسال الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1200px;
        padding: 0 20px;
    }

    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-to-home {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 8px;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
    }

    .back-to-home:hover {
        background: #e5e7eb;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Tabs Styling */
    .tabs-container {
        margin-bottom: 30px;
    }

    .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        gap: 5px;
        margin-bottom: 0;
    }

    .nav-tabs .nav-item {
        margin-bottom: 0;
    }

    .nav-tabs .nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-bottom: 3px solid transparent;
        background: transparent;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Almarai', sans-serif;
    }

    .nav-tabs .nav-link:hover {
        color: #1a5f4a;
        background: #f9fafb;
    }

    .nav-tabs .nav-link.active {
        color: #1a5f4a;
        border-bottom-color: #1a5f4a;
        background: transparent;
    }

    .nav-tabs .nav-link i {
        font-size: 1.1rem;
    }

    .tab-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #1a5f4a;
        color: #ffffff;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-right: 5px;
    }

    .tab-content {
        padding-top: 25px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.show.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-item .label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-item .value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 600;
    }

    .country-tag {
        display: inline-block;
        padding: 4px 12px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin: 2px 5px 2px 0;
    }

    /* Documents Requirements Info */
    .documents-requirements-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .req-section {
        padding: 15px;
        background: #ffffff;
        border-radius: 6px;
        border-right: 3px solid #dc2626;
    }

    .req-section.optional {
        border-right-color: #3b82f6;
    }

    .req-section h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .req-section h5 i {
        font-size: 1.1rem;
        color: #dc2626;
    }

    .req-section.optional h5 i {
        color: #3b82f6;
    }

    .req-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .req-section ul li {
        padding: 6px 0;
        font-size: 0.85rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        position: relative;
        padding-right: 18px;
    }

    .req-section ul li:last-child {
        border-bottom: none;
    }

    .req-section ul li::before {
        content: '•';
        position: absolute;
        right: 0;
        color: #dc2626;
        font-weight: bold;
    }

    .req-section.optional ul li::before {
        color: #3b82f6;
    }

    @media (max-width: 768px) {
        .documents-requirements-info {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }

    .badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #4b5563;
    }

    .documents-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .document-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }

    .document-icon {
        width: 48px;
        height: 48px;
        background: #1a5f4a;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.5rem;
    }

    .document-details h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 5px 0;
    }

    .document-details p {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0;
    }

    .document-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rejection-reason {
        padding: 12px 15px;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        margin-top: 10px;
        color: #991b1b;
        font-size: 0.875rem;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .rejection-reason i {
        margin-top: 2px;
    }

    .empty-state-small {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .empty-state-small i {
        font-size: 3rem;
        margin-bottom: 10px;
    }

    .empty-state-small p {
        margin: 0;
        font-size: 0.95rem;
    }

    .alert {
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert h4 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert p {
        margin: 0;
        font-size: 0.875rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .btn-secondary {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
    }

    .btn-danger {
        background: #ef4444;
        color: #ffffff;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .modal-content {
        border-radius: 8px;
    }

    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 20px 25px;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 15px 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-group label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #ffffff;
        transition: all 0.2s;
        font-family: 'Almarai', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .help-text {
        display: block;
        color: #6b7280;
        font-size: 0.75rem;
        margin-top: 5px;
    }

    .text-muted {
        color: #6b7280;
    }

    /* Modal Fix */
    .modal {
        z-index: 9999 !important;
    }

    .modal-backdrop {
        z-index: 9998 !important;
    }

    .modal-dialog {
        z-index: 10000 !important;
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    /* Documents Status Summary */
    .documents-status-summary {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .documents-status-summary .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: #374151;
        flex: 1;
        min-width: 120px;
    }

    .documents-status-summary .status-item i {
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .documents-status-summary .status-item.uploaded i {
        color: #16a34a;
    }

    .documents-status-summary .status-item.pending i {
        color: #f59e0b;
    }

    .documents-status-summary .status-item.total i {
        color: #3b82f6;
    }

    .documents-status-summary .status-item strong {
        color: #1f2937;
        font-weight: 700;
    }

    /* Disabled select options styling */
    .form-control option:disabled {
        color: #9ca3af;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .documents-status-summary {
            flex-direction: column;
            gap: 10px;
        }

        .documents-status-summary .status-item {
            min-width: auto;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .header-actions {
            width: 100%;
            justify-content: space-between;
        }

        .nav-tabs {
            flex-direction: column;
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            border-bottom: 1px solid #e5e7eb;
            border-right: 3px solid transparent;
            justify-content: space-between;
        }

        .nav-tabs .nav-link.active {
            border-bottom-color: #e5e7eb;
            border-right-color: #1a5f4a;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .document-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .document-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function openUpdateRequestModal(docId, docName, docType) {
    var modal = document.getElementById('updateRequestModal');
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    document.getElementById('ur_documentable_id').value = docId;
    document.getElementById('ur_documentable_type').value = docType;
    document.getElementById('ur_doc_name').textContent = docName;
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    setTimeout(function() {
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.style.zIndex = '9998';
        modal.style.zIndex = '9999';
    }, 50);
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const companyId = '{{ $company->id }}';
        const sessionKey = `foreign_company_${companyId}_active_tab`;

        // Restore active tab from sessionStorage
        const savedTab = sessionStorage.getItem(sessionKey);
        if (savedTab) {
            // Remove active class from all tabs and panes
            document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Activate the saved tab
            const savedTabButton = document.querySelector(`button[data-bs-target="${savedTab}"]`);
            const savedTabPane = document.querySelector(savedTab);

            if (savedTabButton && savedTabPane) {
                savedTabButton.classList.add('active');
                savedTabPane.classList.add('show', 'active');
            }
        }

        // Save active tab when clicked
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function (e) {
                const targetTab = e.target.getAttribute('data-bs-target');
                sessionStorage.setItem(sessionKey, targetTab);
            });
        });

        // Auto-open documents tab if there's a success/error message related to documents
        @if(session('success') && (strpos(session('success'), 'مستند') != false || strpos(session('success'), 'المستند') != false))
            // Activate documents tab
            const documentsTab = document.querySelector('button[data-bs-target="#documents"]');
            if (documentsTab) {
                documentsTab.click();
            }
        @endif

        // Move modal to body to fix z-index and backdrop issues
        const uploadModal = document.getElementById('uploadDocumentModal');

        if (uploadModal) {
            // Move modal to body (prepend to ensure it's at the top)
            document.body.appendChild(uploadModal);

            uploadModal.addEventListener('show.bs.modal', function () {
                // Ensure modal and backdrop have correct z-index
                setTimeout(function() {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '9998';
                    }
                    uploadModal.style.zIndex = '9999';
                    const modalDialog = uploadModal.querySelector('.modal-dialog');
                    if (modalDialog) {
                        modalDialog.style.zIndex = '10000';
                    }
                }, 50);
            });

            uploadModal.addEventListener('hidden.bs.modal', function () {
                // Clean up any leftover backdrops
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
            });

            // Auto-fill document name from selected type
            const documentTypeSelect = document.getElementById('document_type');
            const documentNameHidden = document.getElementById('document_name_hidden');

            if (documentTypeSelect && documentNameHidden) {
                documentTypeSelect.addEventListener('change', function() {
                    // Get the selected option's text
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        // Remove the checkmark and "(تم الرفع)" if present
                        let documentName = selectedOption.text.replace(/^✓\s*/, '').replace(/\s*\(تم الرفع\)$/, '').trim();
                        documentNameHidden.value = documentName;
                    }
                });
            }

            // Form validation
            const uploadForm = uploadModal.querySelector('form');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    const documentType = document.getElementById('document_type');
                    const file = document.getElementById('file');

                    let hasError = false;
                    let errorMessage = '';

                    // Validate document type
                    if (!documentType || !documentType.value) {
                        hasError = true;
                        errorMessage = 'يجب اختيار نوع المستند';
                    }

                    // Validate file
                    if (!hasError && (!file || !file.files || file.files.length == 0)) {
                        hasError = true;
                        errorMessage = 'يجب اختيار ملف للرفع';
                    }

                    // Auto-set document name before submit
                    if (!hasError && documentType && documentType.value) {
                        const selectedOption = documentType.options[documentType.selectedIndex];
                        if (selectedOption) {
                            let documentName = selectedOption.text.replace(/^✓\s*/, '').replace(/\s*\(تم الرفع\)$/, '').trim();
                            documentNameHidden.value = documentName;
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        alert(errorMessage);
                        return false;
                    }
                });
            }
        }
    });

    function openReplaceModal(documentId, documentName) {
        const modal = new bootstrap.Modal(document.getElementById('replaceDocumentModal'));
        const form = document.getElementById('replaceDocumentForm');

        form.action = '{{ route('representative.foreign-companies.documents.replace', [$company, ':id']) }}'.replace(':id', documentId);

        document.getElementById('replaceDocumentName').textContent = documentName;
        document.getElementById('replace_document_name').value = documentName;

        modal.show();
    }
</script>
@endpush
