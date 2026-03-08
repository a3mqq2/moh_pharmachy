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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LocalCompanyController extends Controller
{
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
            'food_drug_registration_number' => 'nullable|string|max:100',
            'chamber_of_commerce_number' => 'nullable|string|max:100',
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'manager_password' => 'nullable|string|min:6',
        ], [
            'company_name.required' => 'اسم الشركة مطلوب',
            'company_type.required' => 'نوع الشركة مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'phone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'license_type.required' => 'نوع الترخيص مطلوب',
            'license_specialty.required' => 'تخصص الترخيص مطلوب',
            'manager_name.required' => 'اسم المدير المسؤول مطلوب',
            'manager_phone.required' => 'رقم هاتف المدير مطلوب',
        ]);

        $company = LocalCompany::create($validated);

        $company->logActivity('created', 'تم إنشاء ملف الشركة');

        $registrationFee = Setting::get('local_company_registration_fee', 0);
        if ($registrationFee > 0) {
            $invoice = $company->invoices()->create([
                'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                'type' => 'registration',
                'description' => 'رسوم تسجيل شركة محلية',
                'amount' => $registrationFee,
                'created_by' => auth()->id(),
            ]);

            $company->logActivity('invoice_created', 'تم إنشاء فاتورة تسجيل تلقائية رقم: ' . $invoice->invoice_number);
        }

        return redirect()->route('admin.local-companies.show', $company)
            ->with('success', 'تم إضافة الشركة بنجاح');
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
            'food_drug_registration_number' => 'nullable|string|max:100',
            'chamber_of_commerce_number' => 'nullable|string|max:100',
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'status' => 'required|in:pending,approved,rejected,suspended',
            'rejection_reason' => 'nullable|string',
            'registration_number' => 'nullable|string|max:50|unique:local_companies,registration_number,' . $localCompany->id,
        ]);

        $oldStatus = $localCompany->status;
        $localCompany->update($validated);

        if ($request->status == 'approved' && !$localCompany->registration_number) {
            $localCompany->update([
                'registration_number' => LocalCompany::generateRegistrationNumber(),
                'registration_date' => now(),
            ]);
        }

        $localCompany->logActivity('updated', 'تم تحديث بيانات الشركة');

        if ($oldStatus != $request->status) {
            $statusNames = LocalCompany::statuses();
            $localCompany->logActivity('status_changed', 'تم تغيير الحالة من "' . ($statusNames[$oldStatus] ?? $oldStatus) . '" إلى "' . ($statusNames[$request->status] ?? $request->status) . '"');
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }

    public function destroy(LocalCompany $localCompany)
    {
        $localCompany->delete();

        return redirect()->route('admin.local-companies.index')
            ->with('success', 'تم حذف الشركة بنجاح');
    }

    public function approve(Request $request, LocalCompany $localCompany)
    {
        $localCompany->load(['documents', 'invoices', 'representative']);

        if (!$localCompany->hasAllRequiredDocuments()) {
            $missingDocs = $localCompany->getMissingDocuments();
            $missingList = implode('، ', array_values($missingDocs));
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', 'لا يمكن قبول الشركة. المستندات الناقصة: ' . $missingList);
        }

        DB::transaction(function () use ($localCompany, $request) {
            if ($localCompany->is_pre_registered && $localCompany->pre_registration_number) {
                $existingCompany = LocalCompany::whereNotNull('registration_number')
                    ->where('registration_number', $localCompany->pre_registration_number)
                    ->first();

                if ($existingCompany) {
                    throw new \Exception('رقم القيد ' . $localCompany->pre_registration_number . ' مستخدم بالفعل من قبل الشركة: ' . $existingCompany->company_name);
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
                'status' => 'approved',
                'rejection_reason' => null,
                'registration_number' => $registrationNumber,
                'registration_date' => $registrationDate,
            ]);

            $localCompany->logActivity('approved', 'تم قبول الشركة - رقم القيد: ' . $registrationNumber);

            if ($localCompany->is_pre_registered) {
                if ($request->has('create_renewal_invoice')) {
                    $renewalFee = Setting::get('renewal_fee', 300.00);

                    $invoice = LocalCompanyInvoice::create([
                        'local_company_id' => $localCompany->id,
                        'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                        'type' => 'renewal',
                        'description' => 'رسوم تجديد شركة - ' . $localCompany->company_name,
                        'amount' => $renewalFee,
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(30),
                        'created_by' => auth()->id(),
                    ]);

                    $localCompany->logActivity('invoice_created', 'تم إصدار فاتورة التجديد رقم: ' . $invoice->invoice_number);
                } elseif ($request->has('last_renewal_date')) {
                    $localCompany->update([
                        'last_renewal_date' => $request->last_renewal_date,
                    ]);

                    $localCompany->logActivity('renewal_date_set', 'تم تحديد تاريخ آخر تجديد: ' . $request->last_renewal_date);
                }
            } else {
                $registrationFee = Setting::get('local_company_annual_fee', 1000.00);

                $invoice = LocalCompanyInvoice::create([
                    'local_company_id' => $localCompany->id,
                    'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                    'type' => 'registration',
                    'description' => 'رسوم تسجيل شركة - ' . $localCompany->company_name,
                    'amount' => $registrationFee,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                    'created_by' => auth()->id(),
                ]);

                $localCompany->logActivity('invoice_created', 'تم إصدار فاتورة التسجيل رقم: ' . $invoice->invoice_number);
            }

            // Send emails to representative
            try {
                Mail::to($localCompany->representative->email)->send(new CompanyApprovedMail($localCompany));
                Mail::to($localCompany->representative->email)->send(new InvoiceCreatedMail($localCompany, $invoice));
                $localCompany->logActivity('email_sent', 'تم إرسال إيميلات الإشعار إلى: ' . $localCompany->representative->email);
            } catch (\Exception $e) {
                Log::error('Failed to send emails: ' . $e->getMessage());
            }
        });

        $message = 'تم قبول الشركة بنجاح. ';
        if ($localCompany->is_pre_registered) {
            if ($request->has('create_renewal_invoice')) {
                $message .= 'تم إصدار فاتورة تجديد.';
            } else {
                $message .= 'تم تحديد تاريخ آخر تجديد.';
            }
        } else {
            $message .= 'تم إصدار فاتورة التسجيل.';
        }
        $message .= ' سيتم إشعار الممثل عبر البريد الإلكتروني.';

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', $message);
    }

    public function activate(LocalCompany $localCompany)
    {
        $localCompany->load(['invoices', 'representative']);

        $invoice = $localCompany->invoices()
            ->whereIn('type', ['registration', 'renewal'])
            ->where('status', '!=', 'paid')
            ->first();

        if (!$invoice) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', 'لا توجد فاتورة للشركة أو تم دفعها بالفعل');
        }

        $registrationInvoice = $invoice;

        if (!$registrationInvoice->receipt_path) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', 'لم يتم رفع إيصال الدفع بعد');
        }

        DB::transaction(function () use ($localCompany, $registrationInvoice) {
            $validityYears = (int) (Setting::where('key', 'local_company_validity_years')->first()?->value ?? 1);

            $localCompany->update([
                'status' => 'active',
                'activated_at' => now(),
                'last_renewal_date' => now(),
                'expires_at' => now()->addYears($validityYears),
            ]);

            $registrationInvoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
            ]);

            $localCompany->logActivity('activated', 'تم تفعيل الشركة');
        });

        // Send email notification to representative
        try {
            Mail::to($localCompany->representative->email)->send(new CompanyActivatedMail($localCompany));
            $localCompany->logActivity('email_sent', 'تم إرسال إيميل إشعار التفعيل إلى: ' . $localCompany->representative->email);
        } catch (\Exception $e) {
            Log::error('Failed to send company activated email: ' . $e->getMessage());
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم تفعيل الشركة بنجاح. رقم القيد: ' . $localCompany->registration_number);
    }

    public function reject(Request $request, LocalCompany $localCompany)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'يرجى إدخال سبب الرفض',
        ]);

        $localCompany->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $localCompany->logActivity('rejected', 'تم رفض الشركة. السبب: ' . $request->rejection_reason);

        if ($localCompany->email) {
            Mail::to($localCompany->email)->send(new LocalCompanyRejected($localCompany));
            $localCompany->logActivity('email_sent', 'تم إرسال إيميل إشعار الرفض إلى: ' . $localCompany->email);
        }

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم رفض الشركة');
    }

    public function restorePending(LocalCompany $localCompany)
    {
        $localCompany->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        $localCompany->logActivity('status_changed', 'تم إعادة الشركة للمراجعة');

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم إعادة الشركة للمراجعة');
    }

    public function certificate(LocalCompany $localCompany)
    {
        if (!in_array($localCompany->status, ['approved', 'active'])) {
            return redirect()->route('admin.local-companies.show', $localCompany)
                ->with('error', 'لا يمكن طباعة شهادة لشركة غير مقبولة أو غير مفعلة');
        }

        return view('admin.local-companies.certificate', compact('localCompany'));
    }
}
