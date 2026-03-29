@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.auth')

@section('title', __('invoices.invoice_details'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1>{{ __('invoices.invoice_details') }}</h1>
            <p>{{ $invoice->invoice_number }}</p>
        </div>
        <a href="{{ route('representative.invoices.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-right"></i>
            {{ __('invoices.back_to_invoices') }}
        </a>
    </div>

    <div class="status-badge {{ $invoice->status }}">
        @if($invoice->status == 'unpaid')
            <i class="ti ti-clock"></i>
            <span>{{ __('invoices.status_unpaid') }}</span>
        @elseif($invoice->status == 'pending_review')
            <i class="ti ti-hourglass"></i>
            <span>{{ __('invoices.status_review') }}</span>
        @elseif($invoice->status == 'paid')
            <i class="ti ti-check"></i>
            <span>{{ __('invoices.status_paid') }}</span>
        @elseif($invoice->status == 'rejected')
            <i class="ti ti-x"></i>
            <span>{{ __('invoices.status_rejected') }}</span>
        @endif
    </div>

    <div class="receipt-section">
        <div class="receipt-header">
            <h3><i class="ti ti-receipt"></i> {{ __('invoices.payment_receipt_label') }}</h3>
            @if($invoice->canUploadReceipt() && !$invoice->hasReceipt())
                <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'">
                    <i class="ti ti-upload"></i>
                    {{ __('invoices.upload_payment_receipt') }}
                </button>
            @endif
        </div>

        @if($invoice->hasReceipt())
            <div class="receipt-card">
                <div class="receipt-preview">
                    @php
                        $extension = pathinfo($invoice->receipt_path, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp

                    @if($isImage)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($invoice->receipt_path) }}" alt="{{ __('invoices.payment_receipt_label') }}">
                    @else
                        <div class="receipt-icon">
                            <i class="ti ti-file-text"></i>
                            <p>{{ strtoupper($extension) }}</p>
                        </div>
                    @endif
                </div>
                <div class="receipt-info">
                    <p><strong>{{ __('invoices.receipt_uploaded_label') }}</strong></p>
                    @if($invoice->isPaid())
                        <p class="success-text"><i class="ti ti-check"></i> {{ __('invoices.payment_confirmed') }}</p>
                    @elseif($invoice->status == 'rejected')
                        <p class="danger-text"><i class="ti ti-x"></i> {{ __('invoices.receipt_rejected_by_admin') }}</p>
                        @if($invoice->receipt_rejection_reason)
                            <p class="text-muted">{{ __('general.reason') }} {{ $invoice->receipt_rejection_reason }}</p>
                        @endif
                    @else
                        <p class="warning-text"><i class="ti ti-clock"></i> {{ __('invoices.awaiting_admin_review') }}</p>
                    @endif
                </div>
                <div class="receipt-actions">
                    <button type="button" class="btn-icon btn-doc-preview" data-file-url="{{ Storage::url($invoice->receipt_path) }}" data-file-name="{{ __('invoices.payment_receipt_label') }}_{{ $invoice->invoice_number }}" data-download-url="{{ route('representative.invoices.download-receipt', $invoice) }}" title="{{ __('general.view') }}">
                        <i class="ti ti-eye"></i>
                    </button>
                    <a href="{{ route('representative.invoices.download-receipt', $invoice) }}" class="btn-icon" title="{{ __('general.download') }}">
                        <i class="ti ti-download"></i>
                    </a>
                    @if($invoice->canDeleteReceipt())
                    <button type="button" class="btn-icon btn-danger" onclick="deleteReceipt()" title="{{ __('general.delete') }}">
                        <i class="ti ti-trash"></i>
                    </button>
                    <form id="delete-receipt-form" action="{{ route('representative.invoices.delete-receipt', $invoice) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                </div>
            </div>
        @else
            <div class="empty-receipt">
                <i class="ti ti-receipt-off"></i>
                <p>{{ __('invoices.no_receipt_uploaded') }}</p>
                @if($invoice->canUploadReceipt())
                <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'">
                    <i class="ti ti-upload"></i>
                    {{ __('invoices.upload_payment_receipt') }}
                </button>
                @endif
            </div>
        @endif
    </div>

    <div class="invoice-details-card">
        <div class="card-section">
            <h3><i class="ti ti-file-invoice"></i> {{ __('invoices.invoice_info') }}</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ __('invoices.invoice_number') }}</span>
                    <span class="info-value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('general.type') }}</span>
                    <span class="info-value">{{ $invoice->type_name }}</span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">{{ __('general.description') }}</span>
                    <span class="info-value">{{ $invoice->description }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('general.amount') }}</span>
                    <span class="info-value amount">{{ number_format($invoice->amount, 2) }} {{ __('general.currency') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('invoices.due_date') }}</span>
                    <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('general.issue_date') }}</span>
                    <span class="info-value">{{ $invoice->created_at->format('Y-m-d') }}</span>
                </div>
                @if($invoice->paid_at)
                <div class="info-item">
                    <span class="info-label">{{ __('invoices.payment_date') }}</span>
                    <span class="info-value">{{ $invoice->paid_at->format('Y-m-d') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card-section">
            <h3><i class="ti ti-building"></i> {{ __('products.company_info') }}</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ __('invoices.company_name_label') }}</span>
                    <span class="info-value">{{ $invoice->localCompany->company_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('invoices.company_type_label') }}</span>
                    <span class="info-value">{{ $invoice->localCompany->company_type_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('general.city') }}</span>
                    <span class="info-value">{{ $invoice->localCompany->city }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('general.email') }}</span>
                    <span class="info-value">{{ $invoice->localCompany->email }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="card-section">
            <h3><i class="ti ti-note"></i> {{ __('general.notes') }}</h3>
            <p class="notes-text">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>

    @if(!$invoice->isPaid())
    <div id="uploadModal" class="upload-modal">
        <div class="upload-modal-content">
            <div class="upload-modal-header">
                <h3><i class="ti ti-upload"></i> {{ __('invoices.upload_payment_receipt') }}</h3>
                <button type="button" class="close-modal" onclick="document.getElementById('uploadModal').style.display='none'">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form action="{{ route('representative.invoices.upload-receipt', $invoice) }}" method="POST" enctype="multipart/form-data" id="uploadReceiptForm">
                @csrf
                <div class="upload-modal-body">
                    <div class="form-group">
                        <label>{{ __('invoices.payment_receipt_label') }} <span class="required">*</span></label>
                        <input type="file" name="receipt" id="receiptFile" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                        <small>{{ __('invoices.receipt_max_size') }}</small>
                        <div id="filePreview" style="margin-top: 10px; display: none;">
                            <div style="padding: 10px; background: #f3f4f6; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                                <i class="ti ti-file-check" style="color: #10b981; font-size: 1.5rem;"></i>
                                <div style="flex: 1;">
                                    <p id="fileName" style="margin: 0; font-weight: 600; font-size: 0.875rem;"></p>
                                    <p id="fileSize" style="margin: 0; color: #6b7280; font-size: 0.75rem;"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('general.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('invoices.notes_placeholder') }}">{{ $invoice->notes }}</textarea>
                    </div>

                    <div class="alert-info">
                        <i class="ti ti-info-circle"></i>
                        <p>{{ __('invoices.review_notice') }}</p>
                    </div>
                </div>
                <div class="upload-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('uploadModal').style.display='none'">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn"><i class="ti ti-upload"></i> {{ __('invoices.upload_receipt_btn') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1000px;
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

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: white;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .status-badge i {
        font-size: 1.125rem;
    }

    .status-badge.unpaid {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.unpaid i {
        color: #dc2626;
    }

    .status-badge.pending_review {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.pending_review i {
        color: #f59e0b;
    }

    .status-badge.paid {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.paid i {
        color: #10b981;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.rejected i {
        color: #dc2626;
    }

    .invoice-details-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 25px;
    }

    .card-section {
        padding: 25px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-section:last-child {
        border-bottom: none;
    }

    .card-section h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-section h3 i {
        color: #1a5f4a;
        font-size: 1.125rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 15px;
        border-right: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-item:nth-child(2n) {
        border-left: 1px solid #f3f4f6;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 600;
    }

    .info-value.amount {
        font-size: 1.25rem;
        color: #1a5f4a;
    }

    .notes-text {
        font-size: 0.95rem;
        color: #374151;
        line-height: 1.6;
        margin: 0;
        padding: 15px;
        background: #f9fafb;
        border-radius: 6px;
    }

    .receipt-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .receipt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .receipt-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .receipt-header i {
        color: #1a5f4a;
        font-size: 1.125rem;
    }

    .receipt-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
    }

    .receipt-preview {
        width: 150px;
        height: 150px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .receipt-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .receipt-icon {
        text-align: center;
    }

    .receipt-icon i {
        font-size: 3rem;
        color: #6b7280;
        display: block;
        margin-bottom: 10px;
    }

    .receipt-icon p {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        margin: 0;
    }

    .receipt-info {
        flex: 1;
    }

    .receipt-info p {
        margin: 0 0 10px 0;
        font-size: 0.95rem;
        color: #374151;
    }

    .receipt-info p:last-child {
        margin-bottom: 0;
    }

    .success-text {
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .success-text i {
        color: #10b981;
    }

    .warning-text {
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .warning-text i {
        color: #f59e0b;
    }

    .danger-text {
        color: #991b1b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .danger-text i {
        color: #dc2626;
    }

    .text-muted {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .receipt-actions {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: #f3f4f6;
        border-color: #1a5f4a;
        color: #1a5f4a;
    }

    .btn-icon.btn-danger {
        color: #dc2626;
    }

    .btn-icon.btn-danger:hover {
        background: #fef2f2;
        border-color: #dc2626;
    }

    .empty-receipt {
        text-align: center;
        padding: 60px 20px;
        background: #f9fafb;
    }

    .empty-receipt i {
        font-size: 3rem;
        color: #9ca3af;
        display: block;
        margin-bottom: 15px;
    }

    .empty-receipt p {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0 0 20px 0;
    }

    .upload-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .upload-modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .upload-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .upload-modal-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .close-modal {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .close-modal:hover {
        background: #f3f4f6;
    }

    .upload-modal-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .form-group small {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 5px;
    }

    .alert-info {
        display: flex;
        gap: 10px;
        padding: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        margin-top: 15px;
    }

    .alert-info i {
        font-size: 1.25rem;
        color: #3b82f6;
        flex-shrink: 0;
    }

    .alert-info p {
        font-size: 0.875rem;
        color: #1e40af;
        margin: 0;
    }

    .upload-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .dashboard-container {
            padding: 20px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .receipt-card {
            flex-direction: column;
            text-align: center;
        }

        .receipt-preview {
            width: 100%;
            height: 200px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteReceipt() {
        Swal.fire({
            title: '{{ __("general.are_you_sure") }}',
            text: '{{ __("invoices.delete_receipt_confirm") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __("general.yes_delete") }}',
            cancelButtonText: '{{ __("general.cancel") }}',
            iconColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-receipt-form').submit();
            }
        });
    }
    window.deleteReceipt = deleteReceipt;

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

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: '{{ __("general.data_error") }}',
            html: '<ul style="text-align: right; list-style: none; padding: 0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif


    window.addEventListener('click', function(event) {
        const modal = document.getElementById('uploadModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    const receiptFile = document.getElementById('receiptFile');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    if (receiptFile) {
        receiptFile.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("invoices.file_too_large") }}',
                        text: '{{ __("invoices.file_too_large_msg") }}',
                        confirmButtonText: '{{ __("general.ok") }}',
                        confirmButtonColor: '#1a5f4a',
                        iconColor: '#ef4444'
                    });
                    receiptFile.value = '';
                    filePreview.style.display = 'none';
                    return;
                }

                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("invoices.file_type_unsupported") }}',
                        text: '{{ __("invoices.file_type_unsupported_msg") }}',
                        confirmButtonText: '{{ __("general.ok") }}',
                        confirmButtonColor: '#1a5f4a',
                        iconColor: '#ef4444'
                    });
                    receiptFile.value = '';
                    filePreview.style.display = 'none';
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' {{ __("invoices.mb_unit") }}';
                filePreview.style.display = 'block';
            } else {
                filePreview.style.display = 'none';
            }
        });
    }

    const uploadForm = document.getElementById('uploadReceiptForm');
    const uploadBtn = document.getElementById('uploadBtn');

    if (uploadForm && uploadBtn) {
        uploadForm.addEventListener('submit', function(e) {
            const file = receiptFile.files[0];
            if (!file) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("invoices.no_file_selected") }}',
                    text: '{{ __("invoices.select_file_first") }}',
                    confirmButtonText: '{{ __("general.ok") }}',
                    confirmButtonColor: '#1a5f4a',
                    iconColor: '#ef4444'
                });
                return false;
            }

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="ti ti-loader" style="animation: spin 1s linear infinite;"></i> {{ __("invoices.uploading") }}';
        });
    }
</script>
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
