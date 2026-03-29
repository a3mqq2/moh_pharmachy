<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForeignCompanyActivated;
use App\Mail\ForeignCompanyApproved;
use App\Mail\ForeignCompanyRejected;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompany;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_foreign_companies', only: ['index', 'show']),
            new Middleware('permission:create_foreign_company', only: ['create', 'store']),
            new Middleware('permission:approve_foreign_company', only: ['approve']),
            new Middleware('permission:reject_foreign_company', only: ['reject']),
            new Middleware('permission:activate_foreign_company', only: ['activate']),
            new Middleware('permission:suspend_foreign_company', only: ['suspend', 'unsuspend']),
            new Middleware('permission:manage_cgmp_certificate', only: ['uploadCgmp', 'downloadCgmp', 'deleteCgmp']),
            new Middleware('permission:print_foreign_company_certificate', only: ['certificate']),
            new Middleware('permission:export_foreign_companies', only: ['print']),
            new Middleware('permission:reject_foreign_company|approve_foreign_company', only: ['restorePending']),
        ];
    }

    public function index(Request $request)
    {
        $query = ForeignCompany::with([
            'representative',
            'localCompany',
            'documents',
            'invoices'
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by activity type
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Search by company name
        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $companies = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => ForeignCompany::count(),
            'uploading_documents' => ForeignCompany::uploadingDocuments()->count(),
            'pending' => ForeignCompany::pending()->count(),
            'pending_payment' => ForeignCompany::pendingPayment()->count(),
            'approved' => ForeignCompany::approved()->count(),
            'active' => ForeignCompany::active()->count(),
            'rejected' => ForeignCompany::where('status', 'rejected')->count(),
        ];

        return view('admin.foreign-companies.index', compact('companies', 'stats'));
    }

    public function print(Request $request)
    {
        $query = ForeignCompany::with([
            'representative',
            'localCompany',
            'documents',
        ]);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $companies = $query->orderBy('created_at', 'desc')->get();

        return view('admin.foreign-companies.print', compact('companies'));
    }

    public function create()
    {
        $localCompanies = LocalCompany::where('company_type', 'supplier')
            ->where('status', 'active')
            ->get();

        $countries = $this->getCountriesList();

        return view('admin.foreign-companies.create', compact('localCompanies', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'local_company_id' => 'required|exists:local_companies,id',
            'company_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'entity_type' => 'required|in:company,factory',
            'address' => 'required|string',
            'email' => 'required|email|max:255',
            'activity_type' => 'required|in:medicines,medical_supplies,both',
            'products_count' => 'required|integer|min:1',
            'registered_countries' => 'nullable|array',
            'registered_countries.*' => 'string|max:100',
        ], [
            'local_company_id.required' => __('companies.val_local_company_required'),
            'company_name.required' => __('companies.val_company_name_required'),
            'country.required' => __('companies.val_country_required'),
            'entity_type.required' => __('companies.val_entity_type_required'),
            'address.required' => __('companies.val_address_required'),
            'email.required' => __('companies.val_email_required'),
            'activity_type.required' => __('companies.val_activity_type_required'),
            'products_count.required' => __('companies.val_products_count_required'),
        ]);

        $localCompany = LocalCompany::findOrFail($validated['local_company_id']);
        $validated['representative_id'] = $localCompany->representative_id;
        $validated['status'] = 'uploading_documents';

        $company = ForeignCompany::create($validated);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_foreign_created'));
    }

    public function show($id)
    {
        $foreignCompany = ForeignCompany::with([
            'representative',
            'localCompany',
            'documents',
            'invoices',
            'approvedBy'
        ])->findOrFail($id);

        return view('admin.foreign-companies.show', compact('foreignCompany'));
    }

    public function certificate($id)
    {
        $foreignCompany = ForeignCompany::with([
            'representative',
            'localCompany'
        ])->findOrFail($id);

        if (!in_array($foreignCompany->status, ['approved', 'active'])) {
            return redirect()->back()
                ->with('error', __('companies.msg_cannot_print_cert'));
        }

        return view('admin.foreign-companies.certificate', compact('foreignCompany'));
    }

    public function approve(Request $request, $id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if ($company->status != 'pending') {
            return redirect()->back()
                ->with('error', __('companies.msg_cannot_approve_current_status'));
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->back()
                ->with('error', __('companies.msg_missing_required_docs'));
        }

        $rules = [];
        if ($request->has('is_pre_registered')) {
            $rules['pre_registration_year'] = 'required|integer|min:1990|max:' . date('Y');
            $rules['pre_registration_sequence'] = 'required|integer|min:1';
        }
        $request->validate($rules);

        $meetingNumber = $request->input('meeting_number');
        $meetingDate = $request->input('meeting_date');

        if ($request->has('is_pre_registered')) {
            $year = $request->input('pre_registration_year');
            $seq = (int) $request->input('pre_registration_sequence');
            $preRegNumber = "{$year}-{$seq}";

            $exists = ForeignCompany::where('pre_registration_number', $preRegNumber)
                ->where('id', '!=', $company->id)
                ->exists();

            $regExists = ForeignCompany::where('registration_number', $preRegNumber)
                ->where('id', '!=', $company->id)
                ->exists();

            if ($exists || $regExists) {
                return redirect()->route('admin.foreign-companies.show', $company->id)
                    ->with('error', __('companies.msg_reg_number_exists', ['number' => $preRegNumber]));
            }

            $company->update([
                'is_pre_registered' => true,
                'pre_registration_number' => $preRegNumber,
                'pre_registration_year' => $year,
            ]);
            $company->refresh();
        }

        DB::transaction(function () use ($company, $meetingNumber, $meetingDate) {
            $company->markAsApproved(auth()->id(), $meetingNumber, $meetingDate);

            // Generate invoice
            $registrationFee = $this->getRegistrationFee();

            $company->invoices()->create([
                'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                'amount' => $registrationFee,
                'description' => __('companies.invoice_desc_foreign_registration'),
                'status' => 'pending',
                'due_date' => now()->addDays(30),
            ]);

            // Update company status to pending_payment
            $company->markAsPendingPayment();
        });

        $emailFailed = false;
        if ($company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyApproved($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company approved email: ' . $e->getMessage());
                $emailFailed = true;
            }
        }

        $message = __('companies.msg_approved_invoice_created');
        $message .= $emailFailed ? __('companies.msg_email_failed_notice') : '';

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', $message);
    }


    public function reject(Request $request, $id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if (!in_array($company->status, ['pending', 'pending_payment'])) {
            return redirect()->back()
                ->with('error', __('companies.msg_cannot_reject_current_status'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        DB::transaction(function () use ($company, $validated) {
            $company->invoices()
                ->where('status', 'pending')
                ->each(function ($invoice) {
                    $invoice->update([
                        'status' => 'cancelled',
                        'description' => $invoice->description . ' (' . __('companies.invoice_cancelled_company_rejected') . ')',
                    ]);
                });

            $company->markAsRejected($validated['rejection_reason']);
        });

            $emailFailed = false;
            if ($company->representative && $company->representative->email) {
                try {
                    Mail::to($company->representative->email)->send(new ForeignCompanyRejected($company));
                } catch (\Exception $e) {
                    Log::error('Failed to send foreign company rejected email: ' . $e->getMessage());
                    $emailFailed = true;
                }
            }

            $message = __('companies.msg_company_rejected');
            $message .= $emailFailed ? __('companies.msg_email_failed_notice') : '';

            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('success', $message);
    }

    public function restorePending($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->status != 'rejected') {
            return redirect()->back()
                ->with('error', __('companies.msg_only_rejected_can_restore'));
        }

        $company->markAsPending();

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_restore_pending_success'));
    }

    public function activate($id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if ($company->status != 'approved') {
            return redirect()->back()
                ->with('error', __('companies.msg_cannot_activate_current_status'));
        }

        $paidInvoice = $company->invoices()
            ->where('status', 'paid')
            ->where('receipt_status', 'approved')
            ->first();

        if (!$paidInvoice) {
            return redirect()->back()
                ->with('error', __('companies.msg_activate_no_paid_invoice'));
        }

        $company->markAsActive();

        $emailFailed = false;
        if ($company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyActivated($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company activated email: ' . $e->getMessage());
                $emailFailed = true;
            }
        }

        $message = __('companies.msg_activated');
        $message .= $emailFailed ? __('companies.msg_email_failed_notice') : '';

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', $message);
    }

    public function suspend(Request $request, $id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!in_array($company->status, ['active', 'expired'])) {
            return redirect()->back()
                ->with('error', __('companies.msg_cannot_suspend_current_status'));
        }

        $validated = $request->validate([
            'suspension_reason' => 'required|string|min:10',
        ]);

        $company->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['suspension_reason'],
        ]);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_company_suspended'));
    }

    public function unsuspend($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->status != 'suspended') {
            return redirect()->back()
                ->with('error', __('companies.msg_company_not_suspended'));
        }

        $previousStatus = ($company->expires_at && $company->expires_at->isPast()) ? 'expired' : 'active';

        $company->update([
            'status' => $previousStatus,
            'suspension_reason' => null,
        ]);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_unsuspended'));
    }

    public function requestRenewal($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!in_array($company->status, ['active', 'expired'])) {
            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('error', __('companies.msg_cannot_renew_current_status'));
        }

        $hasRecentRenewal = $company->invoices()
            ->where('description', 'like', '%' . __('companies.invoice_desc_foreign_renewal') . '%')
            ->whereIn('status', ['pending', 'paid'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->exists();

        if ($hasRecentRenewal) {
            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('error', __('companies.msg_renewal_invoice_exists'));
        }

        $renewalFee = Setting::where('key', 'foreign_company_renewal_fee')->first()?->value ?? 1000.00;

        DB::transaction(function () use ($company, $renewalFee) {
            $invoice = $company->invoices()->create([
                'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                'amount' => $renewalFee,
                'description' => __('companies.invoice_desc_foreign_renewal'),
                'status' => 'pending',
                'issued_by' => auth()->id(),
            ]);

            if ($company->status === 'active' && $company->isExpired()) {
                $company->update(['status' => 'expired']);
            }
        });

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_renewal_invoice_created'));
    }

    public function uploadCgmp(Request $request, $id)
    {
        $company = ForeignCompany::findOrFail($id);

        $request->validate([
            'cgmp_certificate' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'cgmp_certificate.required' => __('companies.val_cgmp_required'),
            'cgmp_certificate.max' => __('companies.val_cgmp_max_size'),
            'cgmp_certificate.mimes' => __('companies.val_cgmp_mimes'),
        ]);

        if ($company->cgmp_certificate_path) {
            Storage::disk('public')->delete($company->cgmp_certificate_path);
        }

        $file = $request->file('cgmp_certificate');
        $path = $file->store('foreign-companies/' . $company->id . '/cgmp', 'public');

        $company->update([
            'cgmp_certificate_path' => $path,
            'cgmp_certificate_name' => $file->getClientOriginalName(),
            'cgmp_uploaded_at' => now(),
        ]);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_cgmp_uploaded'));
    }

    public function downloadCgmp($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!$company->cgmp_certificate_path) {
            return redirect()->back()->with('error', __('companies.msg_no_cgmp'));
        }

        return Storage::disk('public')->download($company->cgmp_certificate_path, $company->cgmp_certificate_name);
    }

    public function deleteCgmp($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->cgmp_certificate_path) {
            Storage::disk('public')->delete($company->cgmp_certificate_path);
            $company->update([
                'cgmp_certificate_path' => null,
                'cgmp_certificate_name' => null,
                'cgmp_uploaded_at' => null,
            ]);
        }

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_cgmp_deleted'));
    }

    private function getRegistrationFee(): float
    {
        $setting = Setting::where('key', 'foreign_company_initial_fee')->first();
        return $setting ? floatval($setting->value) : 1000.00;
    }

    private function getCountriesList(): array
    {
        return __('companies.countries_list');
    }
}
