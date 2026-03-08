@extends('layouts.app')
@section('title', 'الفواتير')
@section('content')
<div class="container-fluid mt-3">

    <div class="card mb-4">
           <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="ti ti-arrow-right me-1"></i>
                        العودة للتقارير
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="window.open('{{ route('admin.reports.invoices', array_merge(request()->all(), ['print' => 1])) }}', '_blank')">
                        <i class="ti ti-printer me-1"></i>
                        طباعة
                    </button>
                    <a href="{{ route('admin.reports.invoices', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-success">
                        <i class="ti ti-file-spreadsheet me-1"></i>
                        تصدير Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <h5 class="mb-3">
                <i class="ti ti-filter me-2"></i>
                تصفية النتائج
            </h5>
            <form method="GET" action="{{ route('admin.reports.invoices') }}">
                <div class="row g-3">
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
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('type', 'all') == 'all' || request('type') == 'local')
    <div class="card mb-4">
        <div class="card-header bg-primary bg-opacity-10">
            <h5 class="mb-0 text-white">
                <i class="ti ti-building me-2"></i>
                فواتير الشركات المحلية
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
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
                            <td>{{ method_exists($localInvoices, 'currentPage') ? ($localInvoices->currentPage() - 1) * $localInvoices->perPage() + $loop->iteration : $loop->iteration }}</td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->localCompany->company_name }}</td>
                            <td><strong class="text-primary">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">لا توجد فواتير</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">إجمالي الفواتير:</th>
                            <th colspan="3">{{ $stats['local_total'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">مدفوعة:</th>
                            <th colspan="3" class="text-success">{{ $stats['local_paid'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">غير مدفوعة:</th>
                            <th colspan="3" class="text-warning">{{ $stats['local_unpaid'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">إجمالي الإيرادات:</th>
                            <th colspan="3" class="text-success">{{ number_format($stats['local_revenue'], 2) }} د.ل</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if(method_exists($localInvoices, 'hasPages') && $localInvoices->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $localInvoices->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if(request('type', 'all') == 'all' || request('type') == 'pharmaceutical')
    <div class="card mb-4">
        <div class="card-header bg-success bg-opacity-10">
            <h5 class="mb-0">
                <i class="ti ti-pill me-2"></i>
                فواتير الأصناف الدوائية
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
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
                            <td>{{ method_exists($pharmaInvoices, 'currentPage') ? ($pharmaInvoices->currentPage() - 1) * $pharmaInvoices->perPage() + $loop->iteration : $loop->iteration }}</td>
                            <td><span class="badge bg-dark">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->pharmaceuticalProduct->product_name }}</td>
                            <td><strong class="text-success">{{ number_format($invoice->amount, 2) }} د.ل</strong></td>
                            <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">{{ $invoice->status_name }}</span></td>
                            <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">لا توجد فواتير</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">إجمالي الفواتير:</th>
                            <th colspan="3">{{ $stats['pharma_total'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">مدفوعة:</th>
                            <th colspan="3" class="text-success">{{ $stats['pharma_paid'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">غير مدفوعة:</th>
                            <th colspan="3" class="text-warning">{{ $stats['pharma_unpaid'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">إجمالي الإيرادات:</th>
                            <th colspan="3" class="text-success">{{ number_format($stats['pharma_revenue'], 2) }} د.ل</th>
                        </tr>
                    </tfoot>
                </table>

            @if(method_exists($pharmaInvoices, 'hasPages') && $pharmaInvoices->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $pharmaInvoices->links() }}
                </div>
            </div>
            @endif
            </div>
        </div>
    </div>
    @endif

    @if(request('type', 'all') == 'all')
    <div class="card">
        <div class="card-header bg-info bg-opacity-10">
            <h5 class="mb-0">
                <i class="ti ti-calculator me-2"></i>
                الإجماليات الكلية
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <span><strong>إجمالي الفواتير:</strong></span>
                        <span class="badge bg-info fs-5">{{ $stats['total_invoices'] }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <span><strong>إجمالي الإيرادات:</strong></span>
                        <span class="badge bg-success fs-5">{{ number_format($stats['total_revenue'], 2) }} د.ل</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
