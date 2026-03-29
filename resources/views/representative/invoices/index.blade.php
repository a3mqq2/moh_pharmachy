@extends('layouts.auth')

@section('title', __('invoices.invoices_payments'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.dashboard') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ __('invoices.invoices_payments') }}</h1>
                <p>{{ __('invoices.invoices_payments_desc') }}</p>
            </div>
        </div>
    </div>

    <div class="filters-card">
        <form method="GET" action="{{ route('representative.invoices.index') }}" id="filterForm">
            <div class="filters-grid">
                <div class="filter-item">
                    <label><i class="ti ti-filter"></i> {{ __('invoices.invoice_status_label') }}</label>
                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">{{ __('invoices.all_statuses') }}</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ __('invoices.status_unpaid') }}</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>{{ __('invoices.status_review') }}</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('invoices.status_paid') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('invoices.status_rejected') }}</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label><i class="ti ti-building"></i> {{ __('invoices.company_label') }}</label>
                    <select name="company_id" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">{{ __('invoices.all_companies') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item">
                    <label><i class="ti ti-file-invoice"></i> {{ __('invoices.invoice_type') }}</label>
                    <select name="type" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">{{ __('invoices.all_types') }}</option>
                        @foreach(\App\Models\LocalCompanyInvoice::invoiceTypes() as $key => $value)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item">
                    @if(request()->hasAny(['status', 'company_id', 'type']))
                        <label>&nbsp;</label>
                        <a href="{{ route('representative.invoices.index') }}" class="btn-clear-filters">
                            <i class="ti ti-x"></i>
                            {{ __('general.clear_filters') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>


    @if($invoices->count() > 0)
        <div class="invoices-table desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('invoices.invoice_number') }}</th>
                        <th>{{ __('invoices.company_label') }}</th>
                        <th>{{ __('general.type') }}</th>
                        <th>{{ __('general.description') }}</th>
                        <th>{{ __('general.amount') }}</th>
                        <th>{{ __('invoices.due_date') }}</th>
                        <th>{{ __('general.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="clickable-row" onclick="window.location='{{ route('representative.invoices.show', $invoice) }}'">
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                        <td>{{ $invoice->localCompany->company_name }}</td>
                        <td>{{ $invoice->type_name }}</td>
                        <td>{{ $invoice->description }}</td>
                        <td><strong>{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong></td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</td>
                        <td>
                            @if($invoice->status == 'unpaid')
                                <span class="badge badge-danger">{{ __('invoices.status_unpaid') }}</span>
                            @elseif($invoice->status == 'pending_review')
                                <span class="badge badge-warning">{{ __('invoices.status_review') }}</span>
                            @elseif($invoice->status == 'paid')
                                <span class="badge badge-success">{{ __('invoices.status_paid') }}</span>
                            @elseif($invoice->status == 'rejected')
                                <span class="badge badge-rejected">{{ __('invoices.status_rejected') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoices-cards mobile-only">
            @foreach($invoices as $invoice)
            <div class="invoice-card clickable-card" onclick="window.location='{{ route('representative.invoices.show', $invoice) }}'">
                <div class="card-header">
                    <div class="invoice-info">
                        <i class="ti ti-file-invoice"></i>
                        <div>
                            <h3>{{ $invoice->invoice_number }}</h3>
                            <p>{{ $invoice->localCompany->company_name }}</p>
                        </div>
                    </div>
                    @if($invoice->status == 'unpaid')
                        <span class="badge badge-danger">{{ __('invoices.status_unpaid') }}</span>
                    @elseif($invoice->status == 'pending_review')
                        <span class="badge badge-warning">{{ __('invoices.status_review') }}</span>
                    @elseif($invoice->status == 'paid')
                        <span class="badge badge-success">{{ __('invoices.status_paid') }}</span>
                    @elseif($invoice->status == 'rejected')
                        <span class="badge badge-rejected">{{ __('invoices.status_rejected') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="card-info-row">
                        <span class="info-label">{{ __('general.type') }}:</span>
                        <span class="info-value">{{ $invoice->type_name }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="info-label">{{ __('general.description') }}:</span>
                        <span class="info-value">{{ $invoice->description }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="info-label">{{ __('general.amount') }}:</span>
                        <span class="info-value"><strong>{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</strong></span>
                    </div>
                    <div class="card-info-row">
                        <span class="info-label">{{ __('invoices.due_date') }}:</span>
                        <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($invoices->hasPages())
        <div class="pagination-wrapper">
            {{ $invoices->links() }}
        </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ti ti-file-invoice"></i>
            </div>
            <h2>{{ __('invoices.no_invoices_yet') }}</h2>
            <p>{{ __('invoices.no_invoices_yet_msg') }}</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '{{ __("general.success") }}',
            text: '{{ session('success') }}',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#10b981',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '{{ __("general.error") }}',
            text: '{{ session('error') }}',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif
</script>
@endpush

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1400px;
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
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #1a5f4a;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 1.2rem;
    }

    .back-to-home:hover {
        background: #1a5f4a;
        color: white;
        border-color: #1a5f4a;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    .filters-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .filter-item label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .filter-item label i {
        margin-left: 5px;
        color: #1a5f4a;
    }

    .filter-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s ease;
        background: white;
    }

    .filter-select:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .btn-clear-filters {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 10px 16px;
        background: #ef4444;
        color: white;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-clear-filters:hover {
        background: #dc2626;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid;
    }

    .summary-card.unpaid {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .summary-card.pending {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .summary-card.paid {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .summary-card.total {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .summary-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.5rem;
    }

    .summary-card.unpaid .summary-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .summary-card.pending .summary-icon {
        background: #fef3c7;
        color: #f59e0b;
    }

    .summary-card.paid .summary-icon {
        background: #d1fae5;
        color: #059669;
    }

    .summary-card.total .summary-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .summary-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 5px 0;
    }

    .summary-content p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .invoices-table {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .invoices-table table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .invoices-table thead {
        background: #f9fafb;
    }

    .invoices-table th {
        padding: 12px 16px;
        text-align: right;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoices-table td {
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoices-table tbody tr:last-child td {
        border-bottom: none;
    }

    .invoices-table tbody tr:hover {
        background: #f9fafb;
    }

    .clickable-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-row:hover {
        background: #f0fdf4 !important;
        transform: translateX(-2px);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .table-action {
        color: #1a5f4a;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .table-action:hover {
        text-decoration: underline;
    }

    .invoices-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .invoice-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .invoice-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: #1a5f4a;
    }

    .clickable-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .clickable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
        border-color: #1a5f4a;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoice-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .invoice-info i {
        font-size: 1.5rem;
        color: #1a5f4a;
    }

    .invoice-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px 0;
    }

    .invoice-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .card-body {
        padding: 16px;
    }

    .card-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .card-info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 600;
    }

    .card-footer {
        padding: 16px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .card-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px;
        background: #1a5f4a;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .card-action-btn:hover {
        background: #164538;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        margin: 30px 0;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .empty-icon i {
        font-size: 2.5rem;
        color: #6b7280;
    }

    .empty-state h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .empty-state p {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
    }

    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }

    .desktop-only {
        display: block;
    }

    .mobile-only {
        display: none;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .desktop-only {
            display: none;
        }

        .mobile-only {
            display: block;
        }

        .dashboard-container {
            padding: 20px;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .summary-cards {
            grid-template-columns: 1fr;
        }

        .invoices-cards {
            gap: 15px;
        }
    }
</style>
@endpush
