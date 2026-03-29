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
                ->with('error', __('invoices.msg_cannot_issue_current_status'));
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
                'description' => $validated['description'] ?? __('invoices.foreign_reg_fees'),
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
                ->with('error', __('invoices.msg_pending_invoice_exists'));
        }

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
            ->with('success', __('invoices.msg_invoice_issued_success'));
    }

    public function approveReceipt($companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $invoice = $company->invoices()
            ->with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status == 'cancelled') {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_handle_cancelled'));
        }

        if ($invoice->receipt_status == 'approved') {
            return redirect()->back()
                ->with('info', __('invoices.msg_already_approved'));
        }

        if (!$invoice->hasReceipt()) {
            return redirect()->back()
                ->with('error', __('invoices.msg_no_receipt_uploaded'));
        }

        if ($invoice->receipt_status != 'pending') {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_approve_current_status'));
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
                        throw new \Exception(__('invoices.msg_reg_number_in_use', ['number' => $company->pre_registration_number]));
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

        $message = __('invoices.msg_receipt_approved_activated');
        $message .= $emailFailed ? __('companies.msg_email_failed_notice') : '';

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
                ->with('error', __('invoices.msg_cannot_handle_cancelled'));
        }

        if ($invoice->receipt_status == 'rejected') {
            return redirect()->back()
                ->with('info', __('invoices.msg_already_rejected'));
        }

        if (!$invoice->hasReceipt()) {
            return redirect()->back()
                ->with('error', __('invoices.msg_no_receipt_uploaded'));
        }

        if ($invoice->receipt_status != 'pending') {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_reject_current_status'));
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
            ->with('success', __('invoices.msg_receipt_rejected_success'));
    }

    public function downloadReceipt($companyId, $invoiceId)
    {
        $company = ForeignCompany::findOrFail($companyId);
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
                ->with('info', __('invoices.msg_already_cancelled'));
        }

        if ($invoice->status == 'paid') {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_cancel_paid'));
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'status' => 'cancelled',
            'description' => $invoice->description . ' (' . __('invoices.msg_cancelled_label', ['reason' => $validated['cancellation_reason'] ?? __('invoices.msg_no_reason')]) . ')',
        ]);

        $company = $invoice->foreignCompany;

        return redirect()->route('admin.foreign-company-invoices.show', $invoice->id)
            ->with('success', __('invoices.msg_invoice_cancelled'));
    }

    public function edit($invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status != 'pending' || $invoice->receipt_path) {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_edit_invoice'));
        }

        return view('admin.foreign-companies.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, $invoiceId)
    {
        $invoice = ForeignCompanyInvoice::with('foreignCompany')
            ->findOrFail($invoiceId);

        if ($invoice->status != 'pending' || $invoice->receipt_path) {
            return redirect()->back()
                ->with('error', __('invoices.msg_cannot_edit_invoice'));
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
            ->with('success', __('invoices.msg_invoice_updated'));
    }
}
