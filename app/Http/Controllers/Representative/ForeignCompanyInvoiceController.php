<?php

namespace App\Http\Controllers\Representative;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyInvoiceController extends Controller
{
    public function index($companyId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $invoices = $company->invoices()
            ->with(['issuedBy', 'approvedBy', 'receiptReviewedBy'])
            ->latest()
            ->paginate(10);

        return view('representative.foreign-companies.invoices.index', compact('company', 'invoices'));
    }

    public function show($companyId, $invoiceId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $invoice = $company->invoices()
            ->with(['issuedBy', 'approvedBy', 'receiptReviewedBy'])
            ->findOrFail($invoiceId);

        return view('representative.foreign-companies.invoices.show', compact('company', 'invoice'));
    }

    public function uploadReceipt(Request $request, $companyId, $invoiceId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $invoice = $company->invoices()->findOrFail($invoiceId);

        if (!$invoice->canUploadReceipt()) {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_upload_receipt'));
        }

        $validated = $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($invoice->receipt_path && Storage::disk('public')->exists($invoice->receipt_path)) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $file = $request->file('receipt');
        $fileName = \Illuminate\Support\Str::random(32) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs(
            'foreign_companies/' . $company->id . '/receipts',
            $fileName,
            'public'
        );

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $filePath, $company) {
            $invoice->update([
                'receipt_path' => $filePath,
                'receipt_uploaded_at' => now(),
                'receipt_status' => 'pending',
                'receipt_rejection_reason' => null,
            ]);

            if (in_array($company->status, ['pending_payment', 'active', 'expired'])) {
                $company->update(['status' => 'payment_review']);
            }
        });
        $representative = auth('representative')->user();
        NotificationHelper::notifyAdmins(
            'receipt_uploaded',
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name,
            [
                __('invoices.invoice_number') => $invoice->invoice_number,
                __('invoices.amount') => number_format($invoice->amount, 2) . ' ' . __('general.lyd'),
            ]
        );

        return redirect()->route('representative.foreign-companies.invoices.show', [$company->id, $invoice->id])
            ->with('success', __('invoices.msg_receipt_upload_success_review'));
    }

    public function downloadReceipt($companyId, $invoiceId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $invoice = $company->invoices()->findOrFail($invoiceId);

        if (!$invoice->hasReceipt()) {
            return redirect()->back()
                ->with('error', __('invoices.msg_receipt_not_found'));
        }

        return Storage::disk('public')->download(
            $invoice->receipt_path,
            'receipt_' . $invoice->invoice_number . '.pdf'
        );
    }

    public function deleteReceipt($companyId, $invoiceId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $invoice = $company->invoices()->findOrFail($invoiceId);

        if (!$invoice->canDeleteReceipt()) {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_delete_receipt'));
        }

        if ($invoice->receipt_path && Storage::disk('public')->exists($invoice->receipt_path)) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $invoice->update([
            'receipt_path' => null,
            'receipt_uploaded_at' => null,
            'receipt_status' => null,
            'receipt_rejection_reason' => null,
        ]);

        if ($company->status === 'payment_review') {
            $hasOtherPendingReceipts = $company->invoices()
                ->where('id', '!=', $invoice->id)
                ->where('receipt_status', 'pending')
                ->exists();

            if (!$hasOtherPendingReceipts) {
                $previousStatus = $company->expires_at && $company->expires_at->isPast() ? 'expired' : 'pending_payment';
                $company->update(['status' => $previousStatus]);
            }
        }


        NotificationHelper::notifyAdmins(
            'receipt_deleted',
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name,
            ['invoice_number' => $invoice->invoice_number]
        );

        return redirect()->route('representative.foreign-companies.invoices.show', [$company->id, $invoice->id])
            ->with('success', __('invoices.msg_receipt_deleted_success'));
    }

    public function downloadInvoice($companyId, $invoiceId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->with('localCompany')
            ->firstOrFail();

        $invoice = $company->invoices()
            ->with('issuedBy')
            ->findOrFail($invoiceId);

        return view('representative.foreign-companies.invoices.pdf', compact('company', 'invoice'));
    }
}
