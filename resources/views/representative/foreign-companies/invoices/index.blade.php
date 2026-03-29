@extends('layouts.auth')

@section('title', __('invoices.company_invoices'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.foreign-companies.show', $company) }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ __('invoices.company_invoices') }}</h1>
                <p>{{ $company->company_name }}</p>
            </div>
        </div>
    </div>

    @if($invoices->isEmpty())
        <div class="empty-state">
            <i class="ti ti-file-invoice"></i>
            <h3>{{ __('invoices.no_invoices_yet') }}</h3>
            <p>{{ __('invoices.no_invoices_for_company') }}</p>
        </div>
    @else
        <div class="invoices-list">
            @foreach($invoices as $invoice)
            <div class="invoice-card">
                <div class="invoice-header">
                    <div class="invoice-number">
                        <i class="ti ti-file-invoice"></i>
                        <span>{{ $invoice->invoice_number }}</span>
                    </div>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ $invoice->status_name }}
                    </span>
                </div>
                <div class="invoice-body">
                    <div class="invoice-info">
                        <div class="info-item">
                            <span class="label">{{ __('general.amount') }}</span>
                            <span class="value">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">{{ __('general.issue_date') }}</span>
                            <span class="value">{{ $invoice->created_at->format('Y-m-d') }}</span>
                        </div>
                        @if($invoice->description)
                        <div class="info-item">
                            <span class="label">{{ __('general.description') }}</span>
                            <span class="value">{{ $invoice->description }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="invoice-footer">
                    <a href="{{ route('representative.foreign-companies.invoices.show', [$company, $invoice]) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-eye"></i> {{ __('general.details') }}
                    </a>
                    <a href="{{ route('representative.foreign-companies.invoices.download', [$company, $invoice]) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-download"></i> {{ __('general.download') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    .page-header {
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

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #d1d5db;
    }

    .empty-state h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
        color: #374151;
    }

    .invoices-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .invoice-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoice-number {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #1f2937;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-paid { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .invoice-body {
        padding: 15px 20px;
    }

    .invoice-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }

    .info-item .label {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .info-item .value {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .invoice-footer {
        display: flex;
        gap: 10px;
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary { background: #1a5f4a; color: #fff; }
    .btn-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .btn-sm { padding: 5px 12px; font-size: 0.75rem; }

    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
</style>
@endpush
