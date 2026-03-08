<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForeignCompanyActivated;
use App\Mail\ForeignCompanyApproved;
use App\Mail\ForeignCompanyRejected;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForeignCompanyController extends Controller
{
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

    public function approve($id)
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

        DB::transaction(function () use ($company) {
            // Mark company as approved
            $company->markAsApproved(auth()->id());

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

        // Send email notification to representative
        if ($company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyApproved($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company approved email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تمت الموافقة على الشركة وتم إصدار الفاتورة بنجاح');
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

        $company->markAsRejected($validated['rejection_reason']);

        // Send email notification to representative
        if ($company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyRejected($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company rejected email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم رفض الشركة بنجاح');
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

        // Send email notification to representative
        if ($company->representative && $company->representative->email) {
            try {
                Mail::to($company->representative->email)->send(new ForeignCompanyActivated($company));
            } catch (\Exception $e) {
                Log::error('Failed to send foreign company activated email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم تفعيل الشركة بنجاح');
    }

    public function suspend(Request $request, $id)
    {
        $company = ForeignCompany::findOrFail($id);

        if ($company->status != 'active') {
            return redirect()->back()
                ->with('error', 'يمكن فقط تعليق الشركات المفعلة');
        }

        $validated = $request->validate([
            'suspension_reason' => 'required|string|min:10',
        ]);

        $company->update([
            'status' => 'suspended',
            'rejection_reason' => $validated['suspension_reason'],
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

        $company->markAsActive();

        return redirect()->route('admin.foreign-companies.show', $company->id)
            ->with('success', 'تم إلغاء تعليق الشركة بنجاح');
    }

    private function getRegistrationFee(): float
    {
        // Get registration fee from settings or return default
        $setting = Setting::where('key', 'foreign_company_initial_fee')->first();
        return $setting ? floatval($setting->value) : 1000.00;
    }
}
