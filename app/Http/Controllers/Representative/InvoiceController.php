<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $representative = Auth::guard('representative')->user();

        // Get all companies for this representative
        $companyIds = LocalCompany::where('representative_id', $representative->id)->pluck('id');

        // Start query
        $query = LocalCompanyInvoice::whereIn('local_company_id', $companyIds)
            ->with('localCompany');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by company if provided
        if ($request->filled('company_id')) {
            $query->where('local_company_id', $request->company_id);
        }

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Order by created date and paginate
        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get all invoices for summary (without pagination)
        $allInvoices = LocalCompanyInvoice::whereIn('local_company_id', $companyIds)->get();

        // Get companies for filter dropdown
        $companies = LocalCompany::where('representative_id', $representative->id)->get();

        return view('representative.invoices.index', compact('invoices', 'companies', 'allInvoices'));
    }

    public function show(LocalCompanyInvoice $invoice)
    {
        $representative = Auth::guard('representative')->user();

        // Check authorization
        if ($invoice->localCompany->representative_id != $representative->id) {
            abort(403);
        }

        return view('representative.invoices.show', compact('invoice'));
    }

    public function uploadReceipt(Request $request, LocalCompanyInvoice $invoice)
    {
        $representative = Auth::guard('representative')->user();

        if ($invoice->localCompany->representative_id != $representative->id) {
            abort(403);
        }

        if (!$invoice->canUploadReceipt()) {
            return redirect()->route('representative.invoices.show', $invoice)
                ->with('error', __('invoices.msg_cannot_upload_receipt'));
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:1000',
        ], [
            'receipt.required' => __('invoices.msg_receipt_required_upload'),
            'receipt.file' => __('invoices.msg_receipt_file_invalid'),
            'receipt.mimes' => __('invoices.msg_receipt_mimes'),
            'receipt.max' => __('invoices.msg_receipt_max_10mb'),
        ]);

        if ($invoice->receipt_path) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $file = $request->file('receipt');
        $path = $file->store('local-companies/' . $invoice->local_company_id . '/receipts', 'public');

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $path, $request) {
            $invoice->update([
                'receipt_path' => $path,
                'receipt_uploaded_at' => now(),
                'receipt_status' => 'pending',
                'receipt_rejection_reason' => null,
                'status' => 'pending_review',
                'notes' => $request->notes,
            ]);

            if (in_array($invoice->localCompany->status, ['approved', 'active', 'expired'])) {
                $invoice->localCompany->update(['status' => 'payment_review']);
            }
        });

        $invoice->localCompany->logActivity(
            'invoice_receipt_uploaded',
            __('invoices.msg_receipt_uploaded_log', ['number' => $invoice->invoice_number])
        );

        NotificationHelper::notifyAdmins(
            'receipt_uploaded',
            'local',
            $invoice->localCompany->company_name,
            $invoice->localCompany->id,
            $representative->name,
            [
                __('invoices.invoice_number') => $invoice->invoice_number,
                __('invoices.amount') => number_format($invoice->amount, 2) . ' ' . __('general.lyd'),
            ]
        );

        return redirect()->route('representative.invoices.show', $invoice)
            ->with('success', __('invoices.msg_receipt_upload_success_review'));
    }

    public function downloadReceipt(LocalCompanyInvoice $invoice)
    {
        $representative = Auth::guard('representative')->user();

        // Check authorization
        if ($invoice->localCompany->representative_id != $representative->id) {
            abort(403);
        }

        if (!$invoice->hasReceipt()) {
            abort(404, __('invoices.msg_receipt_not_found_error'));
        }

        return Storage::disk('public')->download($invoice->receipt_path);
    }

    public function deleteReceipt(LocalCompanyInvoice $invoice)
    {
        $representative = Auth::guard('representative')->user();

        if ($invoice->localCompany->representative_id != $representative->id) {
            abort(403);
        }

        if (!$invoice->canDeleteReceipt()) {
            return redirect()->route('representative.invoices.show', $invoice)
                ->with('error', __('invoices.msg_cannot_delete_receipt'));
        }

        $previousCompanyStatus = null;
        if ($invoice->localCompany->status === 'payment_review') {
            $previousCompanyStatus = 'approved';
        }

        if ($invoice->receipt_path) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $invoice->update([
            'receipt_path' => null,
            'receipt_uploaded_at' => null,
            'receipt_status' => null,
            'receipt_rejection_reason' => null,
            'status' => 'unpaid',
        ]);

        if ($previousCompanyStatus) {
            $hasOtherPendingReceipts = $invoice->localCompany->invoices()
                ->where('id', '!=', $invoice->id)
                ->where('receipt_status', 'pending')
                ->exists();

            if (!$hasOtherPendingReceipts) {
                $invoice->localCompany->update(['status' => $previousCompanyStatus]);
            }
        }

        $invoice->localCompany->logActivity(
            'invoice_receipt_deleted',
            __('invoices.msg_receipt_deleted_log', ['number' => $invoice->invoice_number])
        );

        return redirect()->route('representative.invoices.show', $invoice)
            ->with('success', __('invoices.msg_receipt_deleted_success'));
    }
}
