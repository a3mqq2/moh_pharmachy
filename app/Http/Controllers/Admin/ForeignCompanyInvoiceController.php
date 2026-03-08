<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForeignCompanyActivated;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = ForeignCompanyInvoice::with([
            'foreignCompany.representative',
            'foreignCompany.localCompany',
            'issuedBy',
            'approvedBy'
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by receipt status
        if ($request->filled('receipt_status')) {
            $query->where('receipt_status', $request->receipt_status);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => ForeignCompanyInvoice::count(),
            'pending' => ForeignCompanyInvoice::where('status', 'pending')->count(),
            'paid' => ForeignCompanyInvoice::where('status', 'paid')->count(),
            'cancelled' => ForeignCompanyInvoice::where('status', 'cancelled')->count(),
            'awaiting_receipt' => ForeignCompanyInvoice::where('status', 'pending')
                ->whereNull('receipt_path')
                ->count(),
            'receipt_pending_review' => ForeignCompanyInvoice::where('receipt_status', 'pending')->count(),
        ];

        return view('admin.foreign-companies.invoices.index', compact('invoices', 'stats'));
    }

    public function show($invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with([
            'foreignCompany.representative',
            'foreignCompany.localCompany',
            'issuedBy',
            'approvedBy',
            'receiptReviewedBy'
        ])->findOrFail($invoiceId);

        return view('admin.foreign-companies.invoices.show', compact('invoice'));
    }

    public function store(Request $request, $companyId)
    {
        $company = ForeignCompany::findOrFail($companyId);

        if ($company->status != 'approved') {
            return redirect()->back()
                ->with('error', 'لا يمكن إصدار فاتورة إلا للشركات المقبولة');
        }

        // Check if there's already a pending invoice
        $existingInvoice = $company->invoices()
            ->where('status', 'pending')
            ->first();

        if ($existingInvoice) {
            return redirect()->back()
                ->with('error', 'يوجد فاتورة قائمة بالفعل لهذه الشركة');
        }

        // Get default amount from settings
        $defaultAmount = Setting::get('foreign_company_initial_fee', 5000);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $invoice = $company->invoices()->create([
            'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
            'amount' => $validated['amount'] ?? $defaultAmount,
            'description' => $validated['description'] ?? 'رسوم تسجيل شركة أجنبية',
            'status' => 'pending',
            'issued_by' => auth()->id(),
        ]);

        // Update company status
        $company->markAsPendingPayment();


        return redirect()->route('admin.foreign-companies.invoices.show', $invoice->id)
            ->with('success', 'تم إصدار الفاتورة بنجاح');
    }

    public function approveReceipt($companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $invoice = $company->invoices()
            ->with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->receipt_status == 'approved') {
            return redirect()->back()
                ->with('info', 'تمت الموافقة على هذا الإيصال مسبقاً');
        }

        if (!$invoice->hasReceipt()) {
            return redirect()->back()
                ->with('error', 'لم يتم رفع إيصال الدفع بعد');
        }

        if ($invoice->receipt_status != 'pending') {
            return redirect()->back()
                ->with('error', 'لا يمكن الموافقة على هذا الإيصال في حالته الحالية');
        }

        $invoice->approveReceipt();

        if ($company->status == 'approved' || $company->status == 'pending_payment') {
            $company->markAsActive();

            if ($company->representative && $company->representative->email) {
                try {
                    Mail::to($company->representative->email)->send(new ForeignCompanyActivated($company));
                } catch (\Exception $e) {
                    Log::error('Failed to send foreign company activated email: ' . $e->getMessage());
                }
            }
        } elseif ($company->status == 'expired') {
            $company->renewCompany();
        }

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تمت الموافقة على إيصال الدفع وتم تفعيل الشركة بنجاح');
    }

    public function rejectReceipt(Request $request, $companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $invoice = $company->invoices()
            ->with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->receipt_status == 'rejected') {
            return redirect()->back()
                ->with('info', 'تم رفض هذا الإيصال مسبقاً');
        }

        if (!$invoice->hasReceipt()) {
            return redirect()->back()
                ->with('error', 'لم يتم رفع إيصال الدفع بعد');
        }

        if ($invoice->receipt_status != 'pending') {
            return redirect()->back()
                ->with('error', 'لا يمكن رفض هذا الإيصال في حالته الحالية');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        // Reject the receipt
        $invoice->rejectReceipt($validated['rejection_reason']);

        $company = $invoice->foreignCompany;

        return redirect()->route('admin.foreign-companies.invoices.show', $invoice->id)
            ->with('success', 'تم رفض إيصال الدفع بنجاح');
    }

    public function downloadReceipt($companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
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

    public function downloadInvoice($invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with([
            'foreignCompany.representative',
            'foreignCompany.localCompany',
            'issuedBy'
        ])->findOrFail($invoiceId);

        // Generate PDF (you can use a library like DomPDF or similar)
        // For now, returning a view that can be printed
        return view('admin.foreign-companies.invoices.pdf', compact('invoice'));
    }

    public function cancel(Request $request, $invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status == 'cancelled') {
            return redirect()->back()
                ->with('info', 'هذه الفاتورة ملغاة بالفعل');
        }

        if ($invoice->status == 'paid') {
            return redirect()->back()
                ->with('error', 'لا يمكن إلغاء فاتورة تم دفعها');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Cancel the invoice
        $invoice->update([
            'status' => 'cancelled',
            'description' => $invoice->description . ' (ملغاة: ' . ($validated['cancellation_reason'] ?? 'بدون سبب محدد') . ')',
        ]);

        $company = $invoice->foreignCompany;

        return redirect()->route('admin.foreign-companies.invoices.show', $invoice->id)
            ->with('success', 'تم إلغاء الفاتورة بنجاح');
    }

    public function edit($invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status != 'pending' || $invoice->receipt_path) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعديل هذه الفاتورة');
        }

        return view('admin.foreign-companies.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, $invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status != 'pending' || $invoice->receipt_path) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعديل هذه الفاتورة');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? $invoice->description,
        ]);

        $company = $invoice->foreignCompany;

        return redirect()->route('admin.foreign-companies.invoices.show', $invoice->id)
            ->with('success', 'تم تحديث الفاتورة بنجاح');
    }
}
