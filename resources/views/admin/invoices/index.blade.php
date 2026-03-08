@extends('layouts.app')

@section('title', 'الفواتير')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الفواتير</li>
@endsection

@section('content')

<div class="card mb-3 mt-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="ti ti-filter me-1"></i> الفلاتر
                </button>
                <span class="badge bg-secondary align-self-center">{{ $invoices->total() }} فاتورة</span>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-light text-dark">
                    <i class="ti ti-file-invoice me-1"></i>
                    الإجمالي: {{ $stats['total'] }}
                </div>
                <div class="badge bg-info">
                    <i class="ti ti-building me-1"></i>
                    محلية: {{ $stats['local_total'] }}
                </div>
                <div class="badge bg-success">
                    <i class="ti ti-world me-1"></i>
                    أجنبية: {{ $stats['foreign_total'] }}
                </div>
                <div class="badge bg-purple" style="background-color: #6f42c1;">
                    <i class="ti ti-pill me-1"></i>
                    دوائية: {{ $stats['pharmaceutical_total'] }}
                </div>
                <div class="badge bg-warning">
                    <i class="ti ti-clock me-1"></i>
                    قيد الانتظار: {{ $stats['pending'] }}
                </div>
                <div class="badge bg-primary">
                    <i class="ti ti-check me-1"></i>
                    مدفوعة: {{ $stats['paid'] }}
                </div>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'status', 'type', 'sort_by', 'sort_order']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="رقم الفاتورة، اسم الشركة..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع الفاتورة</label>
                        <select name="type" class="form-select">
                            <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                            <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>شركات محلية</option>
                            <option value="foreign" {{ request('type') == 'foreign' ? 'selected' : '' }}>شركات أجنبية</option>
                            <option value="pharmaceutical" {{ request('type') == 'pharmaceutical' ? 'selected' : '' }}>أصناف دوائية</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>قيد المراجعة</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ترتيب حسب</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>التاريخ</option>
                            <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>المبلغ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> بحث
                            </button>
                            @if(request()->hasAny(['search', 'status', 'type', 'sort_by', 'sort_order']))
                                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i> مسح الفلاتر
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="text-white" style="background-color: #000000;">
                    <tr>
                        <th class="text-white">رقم الفاتورة</th>
                        <th class="text-white">نوع الشركة</th>
                        <th class="text-white">اسم الشركة</th>
                        <th class="text-white">الوصف</th>
                        <th class="text-white">المبلغ</th>
                        <th class="text-white">الحالة</th>
                        <th class="text-white">تاريخ الإنشاء</th>
                        <th class="text-white">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <span class="badge bg-dark">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                <span class="badge bg-info">
                                    <i class="ti ti-building me-1"></i>محلية
                                </span>
                            @elseif($invoice->company_type == 'foreign')
                                <span class="badge bg-success">
                                    <i class="ti ti-world me-1"></i>أجنبية
                                </span>
                            @else
                                <span class="badge" style="background-color: #6f42c1;">
                                    <i class="ti ti-pill me-1"></i>دوائية
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $invoice->company?->company_name ?? 'غير متوفر' }}</strong>
                        </td>
                        <td>
                            <small>{{ $invoice->description ?? '-' }}</small>
                        </td>
                        <td>
                            <strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">مدفوعة</span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge bg-warning">غير مدفوعة</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @elseif($invoice->company_type == 'foreign')
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">مدفوعة</span>
                                @elseif($invoice->status == 'pending')
                                    <span class="badge bg-warning">قيد الانتظار</span>
                                @elseif($invoice->status == 'cancelled')
                                    <span class="badge bg-danger">ملغاة</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @else
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">مدفوعة</span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge bg-warning">غير مدفوعة</span>
                                @elseif($invoice->status == 'pending_review')
                                    <span class="badge bg-info">قيد المراجعة</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <small>{{ $invoice->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $invoice->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($invoice->company_type == 'local')
                                <a href="{{ route('admin.local-companies.show', $invoice->company) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض التفاصيل">
                                    <i class="ti ti-eye"></i>
                                </a>
                            @elseif($invoice->company_type == 'foreign')
                                <a href="{{ route('admin.foreign-company-invoices.show', $invoice) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض التفاصيل">
                                    <i class="ti ti-eye"></i>
                                </a>
                            @else
                                <a href="{{ route('admin.pharmaceutical-products.show', $invoice->pharmaceuticalProduct) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض التفاصيل">
                                    <i class="ti ti-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-file-invoice fs-1 d-block mb-2"></i>
                                لا توجد فواتير مسجلة
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
