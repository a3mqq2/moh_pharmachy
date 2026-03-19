<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LocalCompanyInvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_invoice', only: ['store']),
            new Middleware('permission:edit_invoice', only: ['update']),
            new Middleware('permission:delete_invoice', only: ['destroy']),
            new Middleware('permission:approve_payment_receipt', only: ['approveReceipt', 'markAsPaid']),
            new Middleware('permission:reject_payment_receipt', only: ['rejectReceipt', 'markAsUnpaid']),
        ];
    }

    public function store(Request $request, LocalCompany $localCompany)
    {
        $existingUnpaid = $localCompany->invoices()
            ->whereIn('status', ['unpaid', 'pending_review'])
            ->where('type', $request->type)
            ->first();

        if ($existingUnpaid) {
            return redirect()->back()
                ->with('error', 'يوجد فاتورة غير مدفوعة من نفس النوع بالفعل رقم: ' . $existingUnpaid->invoice_number);
        }

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
            'status' => 'unpaid',
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
        if (in_array($invoice->status, ['paid', 'pending_review'])) {
            return redirect()->back()->with('error', 'لا يمكن تعديل فاتورة مدفوعة أو قيد المراجعة');
        }

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
        if ($invoice->isPaid()) {
            return redirect()->back()->with('error', 'لا يمكن حذف فاتورة مدفوعة');
        }

        if ($invoice->receipt_status === 'pending') {
            return redirect()->back()->with('error', 'لا يمكن حذف فاتورة قيد مراجعة الإيصال');
        }

        if ($invoice->receipt_path) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        $localCompany->logActivity('invoice_deleted', 'تم حذف الفاتورة رقم: ' . $invoiceNumber);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    public function approveReceipt(LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        if ($invoice->receipt_status === 'approved') {
            return redirect()->back()->with('info', 'تم قبول هذا الإيصال مسبقاً');
        }

        if (!$invoice->hasReceipt()) {
            return redirect()->back()->with('error', 'لا يوجد إيصال لهذه الفاتورة');
        }

        if ($invoice->receipt_status !== 'pending') {
            return redirect()->back()->with('error', 'لا يمكن قبول هذا الإيصال في حالته الحالية');
        }

        DB::transaction(function () use ($localCompany, $invoice) {
            $invoice->approveReceipt(auth()->id());

            if (!$localCompany->registration_number) {
                if ($localCompany->is_pre_registered && $localCompany->pre_registration_number) {
                    $existingCompany = LocalCompany::whereNotNull('registration_number')
                        ->where('registration_number', $localCompany->pre_registration_number)
                        ->where('id', '!=', $localCompany->id)
                        ->first();

                    if ($existingCompany) {
                        throw new \Exception('رقم القيد ' . $localCompany->pre_registration_number . ' مستخدم بالفعل');
                    }

                    $registrationNumber = $localCompany->pre_registration_number;
                    $registrationDate = $localCompany->pre_registration_year ?
                        \Carbon\Carbon::createFromDate($localCompany->pre_registration_year, 1, 1) :
                        now();
                } else {
                    $registrationNumber = LocalCompany::generateRegistrationNumber();
                    $registrationDate = now();
                }

                $localCompany->update([
                    'registration_number' => $registrationNumber,
                    'registration_date' => $registrationDate,
                ]);

                $localCompany->logActivity('registration_number_assigned', 'تم إصدار رقم القيد: ' . $registrationNumber);
            }

            $validityYears = $localCompany->company_type === 'distributor' ? 5 : 1;

            if (in_array($localCompany->status, ['payment_review', 'approved'])) {
                $localCompany->update([
                    'status' => 'active',
                    'activated_at' => $localCompany->activated_at ?? now(),
                    'expires_at' => now()->addYears($validityYears),
                ]);
            } elseif ($localCompany->status == 'expired') {
                $baseDate = $localCompany->expires_at ?? now();
                $localCompany->update([
                    'status' => 'active',
                    'expires_at' => \Carbon\Carbon::parse($baseDate)->addYears($validityYears),
                ]);
            }
        });

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم قبول إيصال الدفع وتفعيل الشركة بنجاح');
    }

    public function markAsPaid(Request $request, LocalCompany $localCompany, LocalCompanyInvoice $invoice)
    {
        if ($invoice->isPaid()) {
            return redirect()->back()->with('info', 'تم دفع هذه الفاتورة مسبقاً');
        }

        $request->validate([
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'receipt.mimes' => 'الإيصال يجب أن يكون PDF أو صورة',
            'receipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميجابايت',
        ]);

        $receiptPath = $invoice->receipt_path;

        if ($request->hasFile('receipt')) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
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
            Storage::disk('public')->delete($invoice->receipt_path);
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
        if ($invoice->receipt_status !== 'pending') {
            return redirect()->back()->with('error', 'لا يمكن رفض هذا الإيصال في حالته الحالية');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'يرجى إدخال سبب رفض الإيصال',
        ]);

        DB::transaction(function () use ($localCompany, $invoice, $request) {
            $invoice->rejectReceipt($request->rejection_reason, auth()->id());

            if ($localCompany->status == 'payment_review') {
                $localCompany->update([
                    'status' => 'approved',
                ]);
            }
        });

        if ($localCompany->representative && $localCompany->representative->email) {
            try {
                \Mail::to($localCompany->representative->email)->send(new \App\Mail\ReceiptRejectedMail($localCompany, $invoice, $request->rejection_reason));
                $localCompany->logActivity('email_sent', 'تم إرسال إيميل رفض الإيصال إلى: ' . $localCompany->representative->email);
            } catch (\Exception $e) {
                \Log::error('Failed to send receipt rejection email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم رفض الإيصال وإشعار الممثل عبر البريد الإلكتروني');
    }
}
