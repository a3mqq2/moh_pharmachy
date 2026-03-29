<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompanyActivatedMail;
use App\Mail\CompanyApprovedMail;
use App\Mail\InvoiceCreatedMail;
use App\Mail\LocalCompanyRejected;
use App\Models\LocalCompany;
use App\Models\LocalCompanyDocument;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LocalCompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_local_companies', only: ['index', 'show']),
            new Middleware('permission:create_local_company', only: ['create', 'store']),
            new Middleware('permission:edit_local_company', only: ['edit', 'update']),
            new Middleware('permission:delete_local_company', only: ['destroy']),
            new Middleware('permission:approve_local_company', only: ['approve']),
            new Middleware('permission:reject_local_company', only: ['reject']),
            new Middleware('permission:activate_local_company', only: ['activate']),
            new Middleware('permission:print_local_company_certificate', only: ['certificate']),
            new Middleware('permission:export_local_companies', only: ['print']),
            new Middleware('permission:reject_local_company|approve_local_company', only: ['restorePending']),
        ];
    }

    public function index(Request $request)
    {
        $query = LocalCompany::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('manager_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_type')) {
            $query->where('company_type', $request->company_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('license_type')) {
            $query->where('license_type', $request->license_type);
        }

        if ($request->filled('license_specialty')) {
            $query->where('license_specialty', $request->license_specialty);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('missing_docs')) {
            $requiredCount = count(LocalCompanyDocument::requiredDocumentTypes());
            $query->whereHas('documents', function($q) {}, '<', $requiredCount)
                  ->orWhereDoesntHave('documents');
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.local-companies.index', compact('companies'));
    }

    public function print(Request $request)
    {
        $query = LocalCompany::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('manager_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_type')) {
            $query->where('company_type', $request->company_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('license_type')) {
            $query->where('license_type', $request->license_type);
        }

        if ($request->filled('license_specialty')) {
            $query->where('license_specialty', $request->license_specialty);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $companies = $query->orderBy('registration_number', 'asc')->get();

        return view('admin.local-companies.print', compact('companies'));
    }

    public function create()
    {
        $companyTypes = LocalCompany::companyTypes();
        $licenseTypes = LocalCompany::licenseTypes();
        $licenseSpecialties = LocalCompany::licenseSpecialties();

        return view('admin.local-companies.create', compact('companyTypes', 'licenseTypes', 'licenseSpecialties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:distributor,supplier',
            'company_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'required|email|unique:local_companies,email',
            'registration_date' => 'nullable|date',
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:100',
            'license_issuer' => 'nullable|string|max:255',
            'food_drug_registration_number' => 'required|string|max:100',
            'chamber_of_commerce_number' => 'nullable|string|max:100',
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'manager_password' => 'nullable|string|min:6',
        ], [
            'food_drug_registration_number.required' => __('companies.val_food_drug_reg_required'),
            'company_name.required' => __('companies.val_company_name_required'),
            'company_type.required' => __('companies.val_company_type_required'),
            'city.required' => __('companies.val_city_required'),
            'phone.required' => __('companies.val_phone_required'),
            'email.required' => __('companies.val_email_required'),
            'email.unique' => __('companies.val_email_unique'),
            'license_type.required' => __('companies.val_license_type_required'),
            'license_specialty.required' => __('companies.val_license_specialty_required'),
            'manager_name.required' => __('companies.val_manager_name_required'),
            'manager_phone.required' => __('companies.val_manager_phone_required'),
        ]);

        $company = LocalCompany::create($validated);

        $company->logActivity('created', __('companies.log_company_created'));

        $registrationFee = Setting::get('local_company_registration_fee', 0);
        if ($registrationFee > 0) {
            $invoice = $company->invoices()->create([
                'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                'type' => 'registration',
                'description' => __('companies.log_invoice_registration_desc'),
                'amount' => $registrationFee,
                'created_by' => auth()->id(),
            ]);

            $company->logActivity('invoice_created', __('companies.log_auto_registration_invoice', ['number' => $invoice->invoice_number]));
        }

        return redirect()->route('admin.local-companies.show', $company)
            ->with('success', __('companies.msg_company_added'));
    }

    public function show(LocalCompany $localCompany)
    {
        $localCompany->load(['documents', 'activities.user', 'invoices', 'representative']);
        return view('admin.local-companies.show', compact('localCompany'));
    }

    public function edit(LocalCompany $localCompany)
    {
        $companyTypes = LocalCompany::companyTypes();
        $licenseTypes = LocalCompany::licenseTypes();
        $licenseSpecialties = LocalCompany::licenseSpecialties();
        $statuses = LocalCompany::statuses();

        return view('admin.local-companies.edit', compact('localCompany', 'companyTypes', 'licenseTypes', 'licenseSpecialties', 'statuses'));
    }

    public function update(Request $request, LocalCompany $localCompany)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:distributor,supplier',
            'company_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'required|email|unique:local_companies,email,' . $localCompany->id,
            'registration_date' => 'nullable|date',
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:100',
            'license_issuer' => 'nullable|string|max:255',
            'food_drug_registration_number' => 'required|string|max:100',
            'chamber_of_commerce_number' => 'nullable|string|max:100',
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'status' => 'required|in:pending,approved,rejected,suspended',
            'rejection_reason' => 'nullable|string',
            'registration_number' => ['nullable', 'string', 'max:50', 'regex:/^\d{4}-\d+$/', 'unique:local_companies,registration_number,' . $localCompany->id],
        ]);

        $oldStatus = $localCompany->status;
        $localCompany->update($validated);

        if ($request->status == 'approved' && !$localCompany->registration_number) {
            $localCompany->update([
                'registration_number' => LocalCompany::generateRegistrationNumber(),
                'registration_date' => now(),
            ]);
        }

        $localCompany->logActivity('updated', __('companies.log_company_updated'));

        if ($oldStatus != $request->status) {
            $statusNames = LocalCompany::statuses();
            $localCompany->logActivity('status_changed', __('companies.log_status_changed', ['from' => $statusNames[$oldStatus] ?? $oldStatus, 'to' => $statusNames[$request->status] ?? $request->status]));
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_updated'));
    }

    public function destroy(LocalCompany $localCompany)
    {
        $localCompany->delete();

        return redirect()->route('admin.local-companies.index')
            ->with('success', __('companies.msg_company_deleted'));
    }

    public function approve(Request $request, LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['pending', 'uploading_documents'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_approve_local_status'));
        }

        $localCompany->load(['documents', 'invoices', 'representative']);

        if (!$localCompany->hasAllRequiredDocuments()) {
            $missingDocs = $localCompany->getMissingDocuments();
            $missingList = implode(__('general.list_separator'), array_values($missingDocs));
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_approve_missing_docs', ['list' => $missingList]));
        }

        DB::transaction(function () use ($localCompany, $request) {
            if ($request->has('is_pre_registered')) {
                $year = $request->input('pre_registration_year');
                $seq = $request->input('pre_registration_sequence');
                $localCompany->update([
                    'is_pre_registered' => true,
                    'pre_registration_number' => ($year && $seq) ? "{$year}-{$seq}" : null,
                    'pre_registration_year' => $year,
                ]);
                $localCompany->refresh();
            }

            $localCompany->update([
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            $localCompany->logActivity('approved', __('companies.log_company_approved'));

            if ($localCompany->is_pre_registered) {
                if ($request->has('create_renewal_invoice')) {
                    $renewalFee = Setting::get('renewal_fee', 300.00);

                    $invoice = LocalCompanyInvoice::create([
                        'local_company_id' => $localCompany->id,
                        'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                        'type' => 'renewal',
                        'description' => __('companies.log_renewal_fee_desc', ['name' => $localCompany->company_name]),
                        'amount' => $renewalFee,
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(30),
                        'created_by' => auth()->id(),
                    ]);

                    $localCompany->logActivity('invoice_created', __('companies.log_renewal_invoice_issued', ['number' => $invoice->invoice_number]));
                } else {
                    $lastRenewalDate = $request->input('last_renewal_date', now()->format('Y-m-d'));
                    $validityYears = $localCompany->company_type === 'distributor' ? 5 : 1;

                    $localCompany->update([
                        'status' => 'active',
                        'last_renewal_date' => $lastRenewalDate,
                        'activated_at' => $localCompany->activated_at ?? now(),
                        'registration_number' => $localCompany->pre_registration_number,
                        'registration_date' => $localCompany->pre_registration_year
                            ? \Carbon\Carbon::createFromDate($localCompany->pre_registration_year, 1, 1)
                            : now(),
                        'expires_at' => \Carbon\Carbon::parse($lastRenewalDate)->addYears($validityYears),
                    ]);

                    $localCompany->logActivity('activated', __('companies.log_pre_registered_activated', ['date' => $lastRenewalDate]));
                }
            } else {
                $registrationFee = Setting::get('local_company_annual_fee', 1000.00);

                $invoice = LocalCompanyInvoice::create([
                    'local_company_id' => $localCompany->id,
                    'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                    'type' => 'registration',
                    'description' => __('companies.log_registration_fee_desc', ['name' => $localCompany->company_name]),
                    'amount' => $registrationFee,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                    'created_by' => auth()->id(),
                ]);

                $localCompany->logActivity('invoice_created', __('companies.log_registration_invoice_issued', ['number' => $invoice->invoice_number]));
            }

        });

        $message = __('companies.msg_company_approved_success') . ' ';
        if ($localCompany->is_pre_registered) {
            if ($request->has('create_renewal_invoice')) {
                $message .= __('companies.msg_renewal_invoice_issued');
            } else {
                $message .= __('companies.msg_activated_directly');
            }
        } else {
            $message .= __('companies.msg_registration_invoice_issued');
        }

        $emailFailed = false;
        if ($localCompany->representative && $localCompany->representative->email) {
            try {
                Mail::to($localCompany->representative->email)->send(new CompanyApprovedMail($localCompany));
                $localCompany->logActivity('email_sent', __('companies.log_email_sent_to', ['email' => $localCompany->representative->email]));
            } catch (\Exception $e) {
                Log::error('Failed to send emails: ' . $e->getMessage());
                $emailFailed = true;
            }
        }

        $message .= $emailFailed ? __('companies.msg_email_failed_notice') : __('companies.msg_rep_notified_email');

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', $message);
    }

    public function activate(LocalCompany $localCompany)
    {
        $localCompany->load(['invoices', 'representative']);

        if (!in_array($localCompany->status, ['approved', 'payment_review'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_activate_current_status'));
        }

        $registrationInvoice = $localCompany->invoices()
            ->whereIn('type', ['registration', 'renewal'])
            ->where('status', 'pending_review')
            ->where('receipt_status', 'pending')
            ->first();

        if (!$registrationInvoice) {
            $paidInvoice = $localCompany->invoices()
                ->whereIn('type', ['registration', 'renewal'])
                ->where('status', 'paid')
                ->first();

            if ($paidInvoice) {
                return redirect()->route('admin.local-companies.show', $localCompany)
                    ->with('info', __('companies.msg_already_paid_activated'));
            }

            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_no_pending_receipt'));
        }

        if (!$registrationInvoice->receipt_path) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_no_receipt_uploaded'));
        }

        DB::transaction(function () use ($localCompany, $registrationInvoice) {
            $validityYears = $localCompany->company_type === 'distributor' ? 5 : 1;

            $localCompany->update([
                'status' => 'active',
                'activated_at' => now(),
                'last_renewal_date' => now(),
                'expires_at' => now()->addYears($validityYears),
            ]);

            $registrationInvoice->approveReceipt(auth()->id());

            $localCompany->logActivity('activated', __('companies.log_activated'));
        });

        if ($localCompany->representative && $localCompany->representative->email) {
            try {
                Mail::to($localCompany->representative->email)->send(new CompanyActivatedMail($localCompany));
                $localCompany->logActivity('email_sent', __('companies.log_activation_email_sent_to', ['email' => $localCompany->representative->email]));
            } catch (\Exception $e) {
                Log::error('Failed to send company activated email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_activated_success', ['number' => $localCompany->registration_number]));
    }

    public function reject(Request $request, LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['pending', 'approved', 'uploading_documents'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_reject_current_status'));
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => __('companies.msg_rejection_reason_required'),
        ]);

        $localCompany->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $localCompany->logActivity('rejected', __('companies.log_company_rejected', ['reason' => $request->rejection_reason]));

        $emailFailed = false;
        if ($localCompany->email) {
            try {
                Mail::to($localCompany->email)->send(new LocalCompanyRejected($localCompany));
                $localCompany->logActivity('email_sent', __('companies.log_rejection_email_sent_to', ['email' => $localCompany->email]));
            } catch (\Exception $e) {
                Log::error('Failed to send local company rejected email: ' . $e->getMessage());
                $emailFailed = true;
            }
        }

        $message = __('companies.msg_company_rejected');
        $message .= $emailFailed ? __('companies.msg_email_failed_notice') : '';

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', $message);
    }

    public function restorePending(LocalCompany $localCompany)
    {
        if ($localCompany->status !== 'rejected') {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_only_rejected_can_restore'));
        }

        $localCompany->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        $localCompany->logActivity('status_changed', __('companies.log_returned_to_review'));

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_returned_to_review'));
    }

    public function suspend(Request $request, LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['active', 'expired'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_suspend_current_status'));
        }

        $request->validate([
            'suspension_reason' => 'required|string|max:1000',
        ], [
            'suspension_reason.required' => __('companies.msg_suspension_reason_required'),
        ]);

        $localCompany->update([
            'status' => 'suspended',
            'suspension_reason' => $request->suspension_reason,
        ]);

        $localCompany->logActivity('suspended', __('companies.log_company_suspended', ['reason' => $request->suspension_reason]));

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_company_suspended'));
    }

    public function unsuspend(LocalCompany $localCompany)
    {
        if ($localCompany->status !== 'suspended') {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_company_not_suspended'));
        }

        $previousStatus = ($localCompany->expires_at && $localCompany->expires_at->isPast()) ? 'expired' : 'active';

        $localCompany->update([
            'status' => $previousStatus,
            'suspension_reason' => null,
        ]);

        $localCompany->logActivity('unsuspended', __('companies.log_unsuspended'));

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_unsuspended'));
    }

    public function requestRenewal(LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['active', 'expired'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_renew_current_status'));
        }

        $hasRecentRenewal = $localCompany->invoices()
            ->where('type', 'renewal')
            ->whereIn('status', ['unpaid', 'pending_review'])
            ->exists();

        if ($hasRecentRenewal) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_renewal_invoice_exists'));
        }

        $renewalFee = Setting::where('key', 'local_company_renewal_fee')->first()?->value ?? 500.00;

        DB::transaction(function () use ($localCompany, $renewalFee) {
            $invoice = $localCompany->invoices()->create([
                'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                'type' => 'renewal',
                'description' => __('companies.msg_renewal_fee_desc'),
                'amount' => $renewalFee,
                'status' => 'unpaid',
                'due_date' => now()->addDays(30),
                'created_by' => auth()->id(),
            ]);

            if ($localCompany->status === 'active' && $localCompany->isExpired()) {
                $localCompany->update(['status' => 'expired']);
                $localCompany->logActivity('expired', __('companies.log_status_expired'));
            }

            $localCompany->logActivity('renewal_requested', __('companies.log_renewal_invoice_created', ['number' => $invoice->invoice_number]));
        });

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', __('companies.msg_renewal_invoice_created'));
    }

    public function certificate(LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['approved', 'active'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', __('companies.msg_cannot_print_cert'));
        }

        return view('admin.local-companies.certificate', compact('localCompany'));
    }
}
