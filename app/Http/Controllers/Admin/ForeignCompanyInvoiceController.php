<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForeignCompanyActivated;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyInvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_invoices', only: ['index', 'show']),
            new Middleware('permission:create_invoice', only: ['store']),
            new Middleware('permission:edit_invoice', only: ['edit', 'update']),
            new Middleware('permission:delete_invoice', only: ['destroy']),
            new Middleware('permission:cancel_invoice', only: ['cancel']),
            new Middleware('permission:approve_payment_receipt', only: ['approveReceipt']),
            new Middleware('permission:reject_payment_receipt', only: ['rejectReceipt']),
        ];
    }

    public function index(Request $request)
    {
        $query = ForeignCompanyInvoice::with([
            'foreignCompany.representative',
            'foreignCompany.localCompany',
            'issuedBy',
            'approvedBy'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('receipt_status')) {
            $query->where('receipt_status', $request->receipt_status);
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate(15);

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

        if (!in_array($company->status, ['approved', 'active', 'expired'])) {
            return redirect()->back()
                ->with('error', 'لا يمكن إصدار فاتورة للشركة في حالتها الحالية');
        }

        $defaultAmount = Setting::get('foreign_company_initial_fee', 5000);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $invoice = DB::transaction(function () use ($company, $validated, $defaultAmount) {
            $lockedCompany = ForeignCompany::lockForUpdate()->find($company->id);

            $existingInvoice = $lockedCompany->invoices()
                ->where('status', 'pending')
                ->first();

            if ($existingInvoice) {
                return null;
            }

            $invoice = $lockedCompany->invoices()->create([
                'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                'amount' => $validated['amount'] ?? $defaultAmount,
                'description' => $validated['description'] ?? 'رسوم تسجيل شركة أجنبية',
                'status' => 'pending',
                'issued_by' => auth()->id(),
            ]);

            if ($lockedCompany->status === 'approved') {
                $lockedCompany->markAsPendingPayment();
            }

            return $invoice;
        });

        if (!$invoice) {
            return redirect()->back()
                ->with('error', 'يوجد فاتورة قائمة بالفعل لهذه الشركة');
        }

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
            ->with('success', 'تم إصدار الفاتورة بنجاح');
    }

    public function approveReceipt($companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $invoice = $company->invoices()
            ->with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status == 'cancelled') {
            return redirect()->back()
                ->with('error', 'لا يمكن التعامل مع فاتورة ملغاة');
        }

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

        DB::transaction(function () use ($company, $invoice) {
            $invoice->approveReceipt();

            if (!$company->registration_number) {
                if ($company->is_pre_registered && $company->pre_registration_number) {
                    $existingCompany = ForeignCompany::whereNotNull('registration_number')
                        ->where('registration_number', $company->pre_registration_number)
                        ->where('id', '!=', $company->id)
                        ->first();

                    if ($existingCompany) {
                        throw new \Exception('رقم القيد ' . $company->pre_registration_number . ' مستخدم بالفعل');
                    }

                    $company->update([
                        'registration_number' => $company->pre_registration_number,
                    ]);
                } else {
                    $company->update([
                        'registration_number' => ForeignCompany::generateRegistrationNumber(),
                    ]);
                }
            }

            if (in_array($company->status, ['approved', 'pending_payment'])) {
                $company->markAsActive();
            } elseif (in_array($company->status, ['payment_review', 'expired'])) {
                $company->renewCompany();
            }
        });

        $emailFailed = false;
        if (in_array($company->status, ['active']) && $company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyActivated($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company activated email: ' . $e->getMessage());
                $emailFailed = true;
            }
        }

        $message = 'تمت الموافقة على إيصال الدفع وتم تفعيل الشركة بنجاح';
        $message .= $emailFailed ? ' (تنبيه: فشل إرسال البريد الإلكتروني)' : '';

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', $message);
    }

    public function rejectReceipt(Request $request, $companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $invoice = $company->invoices()
            ->with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status == 'cancelled') {
            return redirect()->back()
                ->with('error', 'لا يمكن التعامل مع فاتورة ملغاة');
        }

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

        DB::transaction(function () use ($invoice, $validated, $company) {
            $invoice->rejectReceipt($validated['rejection_reason']);

            if ($company->status === 'payment_review') {
                $hasPendingReceipts = $company->invoices()
                    ->where('id', '!=', $invoice->id)
                    ->where('receipt_status', 'pending')
                    ->exists();

                if (!$hasPendingReceipts) {
                    $previousStatus = $company->expires_at && $company->expires_at->isPast() ? 'expired' : 'pending_payment';
                    $company->update(['status' => $previousStatus]);
                }
            }
        });

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
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

        $invoice->update([
            'status' => 'cancelled',
            'description' => $invoice->description . ' (ملغاة: ' . ($validated['cancellation_reason'] ?? 'بدون سبب محدد') . ')',
        ]);

        $company = $invoice->foreignCompany;

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
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

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
            ->with('success', 'تم تحديث الفاتورة بنجاح');
    }
}
