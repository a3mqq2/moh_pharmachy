@extends('layouts.app')

@section('title', 'طلبات تعديل المستندات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">طلبات تعديل المستندات</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="ti ti-replace me-2"></i>طلبات تعديل المستندات
                @if($pendingCount > 0)
                    <span class="badge bg-warning rounded-pill ms-2">{{ $pendingCount }}</span>
                @endif
            </h5>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <i class="fas fa-filter me-1"></i>الفلاتر
            </button>
        </div>
    </div>
    <div class="collapse {{ (request('search') || request('doc_type') != 'all' || request('status')) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3 pb-3">
            <form method="GET" action="{{ route('admin.document-center.update-requests') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>المعلقة</option>
                            <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>الموافق عليها</option>
                            <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>المرفوضة</option>
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>الكل</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="doc_type" class="form-select">
                            <option value="all" {{ $docType == 'all' ? 'selected' : '' }}>جميع الأنواع</option>
                            <option value="local" {{ $docType == 'local' ? 'selected' : '' }}>شركات محلية</option>
                            <option value="foreign" {{ $docType == 'foreign' ? 'selected' : '' }}>شركات أجنبية</option>
                            <option value="product" {{ $docType == 'product' ? 'selected' : '' }}>أصناف دوائية</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الملف، المندوب..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>بحث
                        </button>
                        @if(request('search') || request('doc_type') || request('status'))
                        <a href="{{ route('admin.document-center.update-requests') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>مسح
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>المستند</th>
                        <th>المصدر</th>
                        <th>المندوب</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>الملفات</th>
                        <th style="width: 150px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    @php
                        $doc = $req->documentable;
                        $docTypeName = '-';
                        $parentName = '-';
                        $parentRoute = '#';
                        $oldFileUrl = '';
                        $sourceLabel = '';
                        $sourceBadge = '';
                        $oldFileName = '';

                        if ($doc instanceof \App\Models\LocalCompanyDocument) {
                            $docTypeName = $doc->document_type_name;
                            $parentName = $doc->localCompany->company_name ?? '-';
                            $parentRoute = route('admin.local-companies.show', $doc->local_company_id);
                            $oldFileUrl = Storage::url($doc->file_path);
                            $oldFileName = $doc->original_name ?? basename($doc->file_path);
                            $sourceLabel = 'شركة محلية';
                            $sourceBadge = 'bg-info';
                        } elseif ($doc instanceof \App\Models\ForeignCompanyDocument) {
                            $docTypeName = $doc->document_type_name;
                            $parentName = $doc->foreignCompany->company_name ?? '-';
                            $parentRoute = route('admin.foreign-companies.show', $doc->foreign_company_id);
                            $oldFileUrl = Storage::url($doc->file_path);
                            $oldFileName = $doc->document_name ?? basename($doc->file_path);
                            $sourceLabel = 'شركة أجنبية';
                            $sourceBadge = 'bg-primary';
                        } elseif ($doc instanceof \App\Models\PharmaceuticalProductDocument) {
                            $docTypeName = $doc->document_type_name;
                            $parentName = $doc->pharmaceuticalProduct->trade_name ?? '-';
                            $parentRoute = route('admin.pharmaceutical-products.show', $doc->pharmaceutical_product_id);
                            $oldFileUrl = Storage::url($doc->file_path);
                            $oldFileName = $doc->original_name ?? basename($doc->file_path);
                            $sourceLabel = 'صنف دوائي';
                            $sourceBadge = 'bg-success';
                        }

                        $newFileUrl = Storage::url($req->new_file_path);
                    @endphp
                    <tr class="{{ $req->status == 'pending' ? 'table-warning-light' : '' }}">
                        <td class="fw-bold text-muted">{{ $req->id }}</td>
                        <td>
                            <div class="fw-medium">{{ $docTypeName }}</div>
                            <small class="text-muted">{{ Str::limit($req->original_name, 30) }}</small>
                        </td>
                        <td>
                            <a href="{{ $parentRoute }}" class="text-decoration-none d-block">{{ Str::limit($parentName, 25) }}</a>
                            <span class="badge {{ $sourceBadge }}" style="font-size: 0.65rem;">{{ $sourceLabel }}</span>
                        </td>
                        <td>
                            <small>{{ $req->representative->name ?? '-' }}</small>
                            @if($req->reason)
                                <i class="ti ti-message-dots text-muted ms-1" data-bs-toggle="tooltip" title="{{ $req->reason }}"></i>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $req->created_at->format('Y-m-d') }}<br>{{ $req->created_at->format('H:i') }}</small></td>
                        <td>
                            @if($req->status == 'pending')
                                <span class="badge bg-warning">معلق</span>
                            @elseif($req->status == 'approved')
                                <span class="badge bg-success">موافق</span>
                                <br><small class="text-muted">{{ $req->reviewer->name ?? '' }}</small>
                            @else
                                <span class="badge bg-danger">مرفوض</span>
                                <br><small class="text-muted">{{ $req->reviewer->name ?? '' }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-doc-preview"
                                    data-file-url="{{ $oldFileUrl }}"
                                    data-file-name="{{ $oldFileName }}"
                                    data-download-url="{{ $oldFileUrl }}">
                                    <i class="ti ti-file me-1"></i>الحالي
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-doc-preview"
                                    data-file-url="{{ $newFileUrl }}"
                                    data-file-name="{{ $req->original_name }}"
                                    data-download-url="{{ $newFileUrl }}">
                                    <i class="ti ti-file-plus me-1"></i>الجديد
                                </button>
                            </div>
                        </td>
                        <td>
                            @if($req->status == 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.document-center.update-requests.approve', $req) }}" method="POST" class="approve-form">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="موافقة">
                                        <i class="ti ti-check me-1"></i>قبول
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger btn-sm" title="رفض" onclick="openRejectModal({{ $req->id }})">
                                    <i class="ti ti-x me-1"></i>رفض
                                </button>
                            </div>
                            @elseif($req->status == 'rejected' && $req->rejection_reason)
                                <small class="text-danger" data-bs-toggle="tooltip" title="{{ $req->rejection_reason }}">
                                    <i class="ti ti-info-circle me-1"></i>سبب الرفض
                                </small>
                            @else
                                <small class="text-muted">{{ $req->reviewed_at?->format('Y-m-d') }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="text-center text-muted py-5">
                                <i class="ti ti-checklist fs-1 d-block mb-2"></i>
                                <h6>لا توجد طلبات</h6>
                                <small>لا توجد طلبات تعديل مستندات {{ $status == 'pending' ? 'معلقة' : '' }}</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="d-flex justify-content-center py-3">
            {{ $requests->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-x me-2 text-danger"></i>رفض طلب التعديل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">سبب الرفض</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="اذكر سبب رفض الطلب..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i>رفض الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.table-warning-light { background-color: #fffbeb !important; }
</style>
@endpush

@push('scripts')
<script>
function openRejectModal(requestId) {
    document.getElementById('rejectForm').action = '/admin/document-center/update-requests/' + requestId + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

document.querySelectorAll('.approve-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'تأكيد الموافقة',
            text: 'سيتم استبدال المستند القديم بالملف الجديد',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، موافق',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

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
