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
            'local_company_id.required' => 'الشركة المحلية مطلوبة',
            'company_name.required' => 'اسم الشركة مطلوب',
            'country.required' => 'الدولة مطلوبة',
            'entity_type.required' => 'نوع الكيان مطلوب',
            'address.required' => 'العنوان مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'activity_type.required' => 'نوع النشاط مطلوب',
            'products_count.required' => 'عدد المنتجات مطلوب',
        ]);

        $localCompany = LocalCompany::findOrFail($validated['local_company_id']);
        $validated['representative_id'] = $localCompany->representative_id;
        $validated['status'] = 'uploading_documents';

        $company = ForeignCompany::create($validated);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم إنشاء الشركة الأجنبية بنجاح');
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
                ->with('error', 'لا يمكن طباعة الشهادة للشركات غير الموافق عليها');
        }

        return view('admin.foreign-companies.certificate', compact('foreignCompany'));
    }

    public function approve(Request $request, $id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if ($company->status != 'pending') {
            return redirect()->back()
                ->with('error', 'لا يمكن الموافقة على الشركة في الحالة الحالية');
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->back()
                ->with('error', 'الشركة لم ترفع جميع المستندات المطلوبة');
        }

        $meetingNumber = $request->input('meeting_number');
        $meetingDate = $request->input('meeting_date');

        if ($request->has('is_pre_registered')) {
            $year = $request->input('pre_registration_year');
            $seq = $request->input('pre_registration_sequence');
            $company->update([
                'is_pre_registered' => true,
                'pre_registration_number' => ($year && $seq) ? "{$year}-{$seq}" : null,
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
                'description' => 'رسوم تسجيل شركة أجنبية',
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

        $message = 'تمت الموافقة على الشركة وتم إصدار الفاتورة بنجاح';
        $message .= $emailFailed ? ' (تنبيه: فشل إرسال البريد الإلكتروني)' : '';

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', $message);
    }


    public function reject(Request $request, $id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if (!in_array($company->status, ['pending', 'pending_payment'])) {
            return redirect()->back()
                ->with('error', 'لا يمكن رفض الشركة في الحالة الحالية');
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
                        'description' => $invoice->description . ' (ملغاة بسبب رفض الشركة)',
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

            $message = 'تم رفض الشركة بنجاح';
            $message .= $emailFailed ? ' (تنبيه: فشل إرسال البريد الإلكتروني)' : '';

            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('success', $message);
    }

    public function restorePending($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->status != 'rejected') {
            return redirect()->back()
                ->with('error', 'يمكن فقط إعادة الشركات المرفوضة للمراجعة');
        }

        $company->markAsPending();

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم إعادة الشركة للمراجعة بنجاح');
    }

    public function activate($id)
    {
        $company = ForeignCompany::with('representative')->findOrFail($id);

        if ($company->status != 'approved') {
            return redirect()->back()
                ->with('error', 'لا يمكن تفعيل الشركة في الحالة الحالية');
        }

        // Check if there's a paid invoice
        $paidInvoice = $company->invoices()
            ->where('status', 'paid')
            ->where('receipt_status', 'approved')
            ->first();

        if (!$paidInvoice) {
            return redirect()->back()
                ->with('error', 'لا يمكن تفعيل الشركة قبل الموافقة على إيصال الدفع');
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

        $message = 'تم تفعيل الشركة بنجاح';
        $message .= $emailFailed ? ' (تنبيه: فشل إرسال البريد الإلكتروني)' : '';

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', $message);
    }

    public function suspend(Request $request, $id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!in_array($company->status, ['active', 'expired'])) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعليق الشركة في حالتها الحالية');
        }

        $validated = $request->validate([
            'suspension_reason' => 'required|string|min:10',
        ]);

        $company->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['suspension_reason'],
        ]);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم تعليق الشركة بنجاح');
    }

    public function unsuspend($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->status != 'suspended') {
            return redirect()->back()
                ->with('error', 'الشركة غير معلقة');
        }

        $previousStatus = ($company->expires_at && $company->expires_at->isPast()) ? 'expired' : 'active';

        $company->update([
            'status' => $previousStatus,
            'suspension_reason' => null,
        ]);

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم إلغاء تعليق الشركة بنجاح');
    }

    public function requestRenewal($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!in_array($company->status, ['active', 'expired'])) {
            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('error', 'لا يمكن طلب تجديد الشركة في حالتها الحالية');
        }

        $hasRecentRenewal = $company->invoices()
            ->where('description', 'like', '%تجديد%')
            ->whereIn('status', ['pending', 'paid'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->exists();

        if ($hasRecentRenewal) {
            return redirect()->route('admin.foreign-companies.show', $company->id)
                ->with('error', 'يوجد فاتورة تجديد قائمة بالفعل');
        }

        $renewalFee = Setting::where('key', 'foreign_company_renewal_fee')->first()?->value ?? 1000.00;

        DB::transaction(function () use ($company, $renewalFee) {
            $invoice = $company->invoices()->create([
                'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                'amount' => $renewalFee,
                'description' => 'رسوم تجديد الشركة الأجنبية',
                'status' => 'pending',
                'issued_by' => auth()->id(),
            ]);

            if ($company->status === 'active' && $company->isExpired()) {
                $company->update(['status' => 'expired']);
            }
        });

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم إنشاء فاتورة التجديد بنجاح');
    }

    public function uploadCgmp(Request $request, $id)
    {
        $company = ForeignCompany::findOrFail($id);

        $request->validate([
            'cgmp_certificate' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'cgmp_certificate.required' => 'ملف الشهادة مطلوب',
            'cgmp_certificate.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'cgmp_certificate.mimes' => 'يجب أن يكون الملف من نوع PDF أو صورة',
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
            ->with('success', 'تم رفع شهادة CGMP بنجاح');
    }

    public function downloadCgmp($id)
    {
        $company = ForeignCompany::findOrFail($id);

        if (!$company->cgmp_certificate_path) {
            return redirect()->back()->with('error', 'لا توجد شهادة CGMP مرفوعة');
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
            ->with('success', 'تم حذف شهادة CGMP بنجاح');
    }

    private function getRegistrationFee(): float
    {
        $setting = Setting::where('key', 'foreign_company_initial_fee')->first();
        return $setting ? floatval($setting->value) : 1000.00;
    }

    private function getCountriesList(): array
    {
        return [
            'مصر', 'السعودية', 'الإمارات', 'الأردن', 'الكويت', 'قطر', 'البحرين', 'عمان',
            'المغرب', 'تونس', 'الجزائر', 'السودان', 'اليمن', 'لبنان', 'سوريا', 'العراق', 'فلسطين',
            'الصين', 'الهند', 'تركيا', 'إيران', 'باكستان',
            'الولايات المتحدة', 'المملكة المتحدة', 'ألمانيا', 'فرنسا', 'إيطاليا', 'إسبانيا',
            'سويسرا', 'بلجيكا', 'هولندا', 'السويد', 'الدنمارك', 'النرويج', 'فنلندا',
            'كندا', 'أستراليا', 'اليابان', 'كوريا الجنوبية', 'البرازيل', 'المكسيك', 'الأرجنتين',
            'جنوب أفريقيا', 'نيجيريا', 'كينيا', 'أخرى',
        ];
    }
}
