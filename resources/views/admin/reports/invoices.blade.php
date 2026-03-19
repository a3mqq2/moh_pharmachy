@extends('layouts.app')

@section('title', 'تقرير الفواتير')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">الفواتير</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>تقرير الفواتير</h5>
                @if($filtered)
                <span class="badge bg-secondary">{{ $stats['total_invoices'] }} فاتورة</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($filtered)
                <button type="button" class="btn btn-outline-success btn-sm" onclick="window.open('{{ route('admin.reports.invoices', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <a href="{{ route('admin.reports.invoices', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> تصدير Excel
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body border-top bg-light pt-3">
        <form method="GET" action="{{ route('admin.reports.invoices') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">نوع الفاتورة</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>شركات محلية</option>
                        <option value="pharmaceutical" {{ request('type') == 'pharmaceutical' ? 'selected' : '' }}>أصناف دوائية</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        @if($filtered)
                            <a href="{{ route('admin.reports.invoices') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> مسح
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
    @if($filtered)
    <div class="card-body p-0">
        @if($type == 'all' || $type == 'local')
        <div class="border-bottom">
            <div class="px-3 py-2 bg-light border-bottom">
                <strong><i class="fas fa-building me-1"></i> فواتير الشركات المحلية</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الفاتورة</th>
                            <th>اسم الشركة</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>تاريخ الإصدار</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localInvoices as $invoice)
                        <tr>
                            <td><span class="badge bg-dark">{{ method_exists($localInvoices, 'currentPage') ? ($localInvoices->currentPage() - 1) * $localInvoices->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->localCompany->company_name }}</td>
                            <td><strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">لا توجد فواتير</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">إجمالي:</th>
                            <th>{{ $stats['local_total'] }}</th>
                            <th>مدفوعة: {{ $stats['local_paid'] }}</th>
                            <th>{{ number_format($stats['local_revenue'], 2) }} د.ل</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if(method_exists($localInvoices, 'hasPages') && $localInvoices->hasPages())
            <div class="d-flex justify-content-center py-2">
                {{ $localInvoices->links() }}
            </div>
            @endif
        </div>
        @endif

        @if($type == 'all' || $type == 'pharmaceutical')
        <div>
            <div class="px-3 py-2 bg-light border-bottom">
                <strong><i class="fas fa-capsules me-1"></i> فواتير الأصناف الدوائية</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الفاتورة</th>
                            <th>الصنف الدوائي</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>تاريخ الإصدار</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pharmaInvoices as $invoice)
                        <tr>
                            <td><span class="badge bg-dark">{{ method_exists($pharmaInvoices, 'currentPage') ? ($pharmaInvoices->currentPage() - 1) * $pharmaInvoices->perPage() + $loop->iteration : $loop->iteration }}</span></td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->pharmaceuticalProduct->product_name }}</td>
                            <td><strong class="text-success">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td><small>{{ $invoice->created_at->format('Y-m-d') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">لا توجد فواتير</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">إجمالي:</th>
                            <th>{{ $stats['pharma_total'] }}</th>
                            <th>مدفوعة: {{ $stats['pharma_paid'] }}</th>
                            <th>{{ number_format($stats['pharma_revenue'], 2) }} د.ل</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if(method_exists($pharmaInvoices, 'hasPages') && $pharmaInvoices->hasPages())
            <div class="d-flex justify-content-center py-2">
                {{ $pharmaInvoices->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
    @if($type == 'all')
    <div class="card-footer">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                    <span><strong>إجمالي الفواتير:</strong></span>
                    <span class="badge bg-info fs-6">{{ $stats['total_invoices'] }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                    <span><strong>إجمالي الإيرادات:</strong></span>
                    <span class="badge bg-success fs-6">{{ number_format($stats['total_revenue'], 2) }} د.ل</span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="card-body text-center py-5">
        <div class="text-muted">
            <i class="fas fa-filter fs-1 d-block mb-3"></i>
            <h5>استخدم الفلاتر أعلاه لعرض النتائج</h5>
            <p>اختر نوع الفاتورة أو الحالة أو التاريخ ثم اضغط بحث</p>
        </div>
    </div>
    @endif
</div>

@endsection
