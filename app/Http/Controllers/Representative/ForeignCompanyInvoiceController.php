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
                ->with('error', 'لا يمكن رفع إيصال الدفع في الحالة الحالية');
        }

        $validated = $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Delete old receipt if exists
        if ($invoice->receipt_path && Storage::disk('public')->exists($invoice->receipt_path)) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        // Store new receipt
        $file = $request->file('receipt');
        $fileName = 'receipt_' . time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs(
            'foreign_companies/' . $company->id . '/receipts',
            $fileName,
            'public'
        );

        // Update invoice
        $invoice->update([
            'receipt_path' => $filePath,
            'receipt_uploaded_at' => now(),
            'receipt_status' => 'pending',
            'receipt_rejection_reason' => null,
        ]);

        // Company status remains as 'pending_payment' to indicate payment receipt is under review
        // It will only change to 'active' after admin approves the receipt

        // Send notification to admins
        $representative = auth('representative')->user();
        NotificationHelper::notifyAdmins(
            'receipt_uploaded',
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name,
            [
                'رقم الفاتورة' => $invoice->invoice_number,
                'المبلغ' => number_format($invoice->amount, 2) . ' د.ل',
            ]
        );

        return redirect()->route('representative.foreign-companies.invoices.show', [$company->id, $invoice->id])
            ->with('success', 'تم رفع إيصال الدفع بنجاح. سيتم مراجعته من قبل الإدارة');
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
                ->with('error', 'إيصال الدفع غير موجود');
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
                ->with('error', 'لا يمكن حذف إيصال الدفع في الحالة الحالية');
        }

        // Delete receipt file
        if ($invoice->receipt_path && Storage::disk('public')->exists($invoice->receipt_path)) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        // Update invoice
        $invoice->update([
            'receipt_path' => null,
            'receipt_uploaded_at' => null,
            'receipt_status' => null,
            'receipt_rejection_reason' => null,
        ]);

        // Send notification to admins
        NotificationHelper::notifyAdmins(
            'receipt_deleted',
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name,
            ['invoice_number' => $invoice->invoice_number]
        );

        return redirect()->route('representative.foreign-companies.invoices.show', [$company->id, $invoice->id])
            ->with('success', 'تم حذف إيصال الدفع بنجاح');
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

        // Generate PDF (you can use a library like DomPDF or similar)
        // For now, returning a view that can be printed
        return view('representative.foreign-companies.invoices.pdf', compact('company', 'invoice'));
    }
}
