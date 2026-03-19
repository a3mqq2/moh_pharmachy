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
                ->with('error', 'لا يمكن رفع إيصال لهذه الفاتورة في حالتها الحالية');
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:1000',
        ], [
            'receipt.required' => 'يرجى اختيار ملف الإيصال',
            'receipt.file' => 'الملف غير صالح',
            'receipt.mimes' => 'يجب أن يكون الإيصال بصيغة PDF أو صورة',
            'receipt.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
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
            'تم رفع إيصال الدفع للفاتورة رقم: ' . $invoice->invoice_number
        );

        // Send notification to admins
        NotificationHelper::notifyAdmins(
            'receipt_uploaded',
            'local',
            $invoice->localCompany->company_name,
            $invoice->localCompany->id,
            $representative->name,
            [
                'رقم الفاتورة' => $invoice->invoice_number,
                'المبلغ' => number_format($invoice->amount, 2) . ' د.ل',
            ]
        );

        return redirect()->route('representative.invoices.show', $invoice)
            ->with('success', 'تم رفع إيصال الدفع بنجاح. سيتم مراجعته من قبل الإدارة.');
    }

    public function downloadReceipt(LocalCompanyInvoice $invoice)
    {
        $representative = Auth::guard('representative')->user();

        // Check authorization
        if ($invoice->localCompany->representative_id != $representative->id) {
            abort(403);
        }

        if (!$invoice->hasReceipt()) {
            abort(404, 'الإيصال غير موجود');
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
                ->with('error', 'لا يمكن حذف الإيصال في حالته الحالية');
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
            'تم حذف إيصال الدفع للفاتورة رقم: ' . $invoice->invoice_number
        );

        return redirect()->route('representative.invoices.show', $invoice)
            ->with('success', 'تم حذف إيصال الدفع بنجاح');
    }
}
