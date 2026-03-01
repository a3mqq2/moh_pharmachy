@extends('layouts.app')

@section('title', 'فواتير الشركات الأجنبية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">فواتير الشركات الأجنبية</li>
@endsection

@section('content')


<div class="card mt-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="ti ti-filter me-1"></i> الفلاتر
                </button>
                <span class="badge bg-secondary align-self-center">{{ $invoices->total() }} فاتورة</span>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'receipt_status', 'sort_by', 'sort_order']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث برقم الفاتورة</label>
                        <input type="text" name="search" class="form-control" placeholder="رقم الفاتورة..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة الفاتورة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة الإيصال</label>
                        <select name="receipt_status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('receipt_status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="approved" {{ request('receipt_status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                            <option value="rejected" {{ request('receipt_status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ترتيب حسب</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>تاريخ الإنشاء</option>
                            <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>المبلغ</option>
                            <option value="invoice_number" {{ request('sort_by') == 'invoice_number' ? 'selected' : '' }}>رقم الفاتورة</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">الاتجاه</label>
                        <select name="sort_order" class="form-select">
                            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> تطبيق
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>الشركة</th>
                        <th>المبلغ</th>
                        <th>حالة الفاتورة</th>
                        <th>حالة الإيصال</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $invoice->foreignCompany->localCompany->name_ar }}</strong>
                                @if($invoice->foreignCompany->localCompany->name_en)
                                <br><small class="text-muted">{{ $invoice->foreignCompany->localCompany->name_en }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong>
                        </td>
                        <td>
                            @if($invoice->status === 'pending')
                                <span class="badge bg-warning">قيد الانتظار</span>
                            @elseif($invoice->status === 'paid')
                                <span class="badge bg-success">مدفوعة</span>
                            @elseif($invoice->status === 'cancelled')
                                <span class="badge bg-danger">ملغاة</span>
                            @endif
                        </td>
                        <td>
                            @if($invoice->receipt_path)
                                @if($invoice->receipt_status === 'pending')
                                    <span class="badge bg-info">
                                        <i class="ti ti-clock"></i> قيد المراجعة
                                    </span>
                                @elseif($invoice->receipt_status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="ti ti-check"></i> موافق عليه
                                    </span>
                                @elseif($invoice->receipt_status === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="ti ti-x"></i> مرفوض
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="ti ti-file-off"></i> لا يوجد إيصال
                                </span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $invoice->created_at->format('Y-m-d') }}</div>
                            <small class="text-muted">{{ $invoice->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.foreign-company-invoices.show', $invoice->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض التفاصيل">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if($invoice->status === 'pending' && !$invoice->receipt_path)
                                <a href="{{ route('admin.foreign-company-invoices.edit', $invoice->id) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="تعديل">
                                    <i class="ti ti-edit"></i>
                                </a>
                                @endif
                                @if($invoice->receipt_path)
                                <a href="{{ route('admin.foreign-companies.invoices.download-receipt', [$invoice->foreign_company_id, $invoice->id]) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="تحميل الإيصال"
                                   target="_blank">
                                    <i class="ti ti-download"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="ti ti-file-invoice" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">لا توجد فواتير</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="mt-4">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
        @endif
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
