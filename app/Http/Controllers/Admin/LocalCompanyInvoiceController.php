<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocalCompanyInvoiceController extends Controller
{
    public function store(Request $request, LocalCompany $localCompany)
    {
        $validated = $request->validate([
            'type' => 'required|in:registration,renewal,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'نوع الفاتورة مطلوب',
            'description.required' => 'وصف الفاتورة مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون قيمة موجبة',
        ]);

        $invoice = $localCompany->invoices()->create([
            'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $localCompany->logActivity('invoice_created', 'تم إنشاء فاتورة جديدة رقم: ' . $invoice->invoice_number);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    public function update(Request $request, LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ], [
            'description.required' => 'وصف الفاتورة مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون قيمة موجبة',
        ]);

        $invoice->update([
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $localCompany->logActivity('invoice_updated', 'تم تعديل الفاتورة رقم: ' . $invoice->invoice_number);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم تعديل الفاتورة بنجاح');
    }

    public function destroy(LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        if ($invoice->receipt_path) {
            Storage::delete($invoice->receipt_path);
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        $localCompany->logActivity('invoice_deleted', 'تم حذف الفاتورة رقم: ' . $invoiceNumber);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    public function approveReceipt(LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        if (!$invoice->hasReceipt()) {
            return redirect()->back()->with('error', 'لا يوجد إيصال لهذه الفاتورة');
        }

        $invoice->approveReceipt(auth()->id());

        if ($localCompany->status === 'payment_review') {
            $validityYears = (int) (Setting::where('key', 'local_company_validity_years')->first()?->value ?? 1);
            $localCompany->update([
                'status' => 'active',
                'expires_at' => now()->addYears($validityYears),
            ]);
        } elseif ($localCompany->status === 'expired') {
            $localCompany->renewCompany();
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم قبول إيصال الدفع وتفعيل الشركة بنجاح');
    }

    public function markAsPaid(Request $request, LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        $request->validate([
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'receipt.mimes' => 'الإيصال يجب أن يكون PDF أو صورة',
            'receipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميجابايت',
        ]);

        $receiptPath = $invoice->receipt_path;

        if ($request->hasFile('receipt')) {
            if ($receiptPath) {
                Storage::delete($receiptPath);
            }
            $receiptPath = $request->file('receipt')->store('local-companies/' . $localCompany->id . '/receipts', 'public');
        }

        $invoice->markAsPaid($receiptPath);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم تأكيد دفع الفاتورة');
    }

    public function markAsUnpaid(LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        $invoice->markAsUnpaid();

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم إلغاء دفع الفاتورة');
    }

    public function uploadReceipt(Request $request, LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'receipt.required' => 'الإيصال مطلوب',
            'receipt.mimes' => 'الإيصال يجب أن يكون PDF أو صورة',
            'receipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميجابايت',
        ]);

        if ($invoice->receipt_path) {
            Storage::delete($invoice->receipt_path);
        }

        $path = $request->file('receipt')->store('local-companies/' . $localCompany->id . '/receipts', 'public');

        $invoice->update(['receipt_path' => $path]);

        $localCompany->logActivity('receipt_uploaded', 'تم رفع إيصال للفاتورة رقم: ' . $invoice->invoice_number);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم رفع الإيصال بنجاح');
    }

    public function downloadReceipt(LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        if (!$invoice->receipt_path || !Storage::disk('public')->exists($invoice->receipt_path)) {
            return redirect()->back()->with('error', 'الإيصال غير موجود');
        }

        return Storage::disk('public')->download($invoice->receipt_path, 'receipt-' . $invoice->invoice_number . '.' . pathinfo($invoice->receipt_path, PATHINFO_EXTENSION));
    }

    public function rejectReceipt(Request $request, LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'يرجى إدخال سبب رفض الإيصال',
        ]);

        $invoice->rejectReceipt($request->rejection_reason, auth()->id());

        if ($localCompany->status === 'payment_review') {
            $localCompany->update([
                'status' => 'approved',
            ]);
        }

        try {
            \Mail::to($localCompany->representative->email)->send(new \App\Mail\ReceiptRejectedMail($localCompany, $invoice, $request->rejection_reason));
            $localCompany->logActivity('email_sent', 'تم إرسال إيميل رفض الإيصال إلى: ' . $localCompany->representative->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send receipt rejection email: ' . $e->getMessage());
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم رفض الإيصال وإشعار الممثل عبر البريد الإلكتروني');
    }
}
