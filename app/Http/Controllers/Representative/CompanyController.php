<?php

namespace App\Http\Controllers\Representative;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\LocalCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {
        $representative = Auth::guard('representative')->user();
        $companies = $representative->companies()->latest()->get();

        return view('representative.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('representative.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Step 1: Company Information
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:distributor,supplier',
            'company_address' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'required|email|unique:local_companies,email',

            // Pre-registration fields
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_number' => 'required_if:is_pre_registered,1|nullable|string|max:255',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),

            // Step 2: License Information
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:255',
            'license_issuer' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'food_drug_registration_number' => 'nullable|string|max:255',
            'chamber_of_commerce_number' => 'nullable|string|max:255',

            // Step 3: Manager Information
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_email' => 'nullable|email|max:255',
        ], [
            'pre_registration_number.required_if' => 'رقم القيد السابق مطلوب عند تحديد الشركة كمسجلة مسبقاً',
            'pre_registration_year.required_if' => 'سنة التسجيل مطلوبة عند تحديد الشركة كمسجلة مسبقاً',
            'pre_registration_year.min' => 'سنة التسجيل يجب أن تكون 1990 أو أحدث',
            'pre_registration_year.max' => 'سنة التسجيل لا يمكن أن تكون في المستقبل',
        ]);

        $representative = Auth::guard('representative')->user();

        $company = LocalCompany::create([
            ...$validated,
            'representative_id' => $representative->id,
            'status' => 'uploading_documents',
        ]);

        // Send notification to admins
        NotificationHelper::notifyAdmins(
            'company_created',
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        // Set session to open documents tab
        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم تسجيل الشركة بنجاح. يرجى رفع جميع المستندات المطلوبة لإكمال عملية التسجيل.');
    }

    public function show(LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        // التحقق من أن الشركة تخص الممثل الحالي
        if ($company->representative_id !== $representative->id) {
            abort(403, 'غير مصرح لك بعرض هذه الشركة');
        }

        return view('representative.companies.show', compact('company'));
    }

    public function edit(LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        // التحقق من أن الشركة تخص الممثل الحالي
        if ($company->representative_id !== $representative->id) {
            abort(403, 'غير مصرح لك بتعديل هذه الشركة');
        }

        // التحقق من أن الشركة مرفوضة أو قيد رفع المستندات
        if (!in_array($company->status, ['rejected', 'uploading_documents'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'لا يمكن تعديل الشركة في هذه الحالة');
        }

        return view('representative.companies.edit', compact('company'));
    }

    public function update(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        // التحقق من أن الشركة تخص الممثل الحالي
        if ($company->representative_id !== $representative->id) {
            abort(403, 'غير مصرح لك بتعديل هذه الشركة');
        }

        // التحقق من أن الشركة مرفوضة أو قيد رفع المستندات
        if (!in_array($company->status, ['rejected', 'uploading_documents'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'لا يمكن تعديل الشركة في هذه الحالة');
        }

        $validated = $request->validate([
            // Step 1: Company Information
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:distributor,supplier',
            'company_address' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'required|email|unique:local_companies,email,' . $company->id,

            // Pre-registration fields
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_number' => 'required_if:is_pre_registered,1|nullable|string|max:255',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),

            // Step 2: License Information
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:255',
            'license_issuer' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'food_drug_registration_number' => 'nullable|string|max:255',
            'chamber_of_commerce_number' => 'nullable|string|max:255',

            // Step 3: Manager Information
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_email' => 'nullable|email|max:255',
        ], [
            'pre_registration_number.required_if' => 'رقم القيد السابق مطلوب عند تحديد الشركة كمسجلة مسبقاً',
            'pre_registration_year.required_if' => 'سنة التسجيل مطلوبة عند تحديد الشركة كمسجلة مسبقاً',
            'pre_registration_year.min' => 'سنة التسجيل يجب أن تكون 1990 أو أحدث',
            'pre_registration_year.max' => 'سنة التسجيل لا يمكن أن تكون في المستقبل',
        ]);

        $company->update($validated);

        // إذا كانت الشركة مرفوضة، نعيدها لحالة uploading_documents
        $wasRejected = $company->status === 'rejected';
        if ($wasRejected) {
            $company->update([
                'status' => 'uploading_documents',
                'rejection_reason' => null,
            ]);
            $company->logActivity('resubmitted', 'تم إعادة تقديم الطلب بعد التعديل');
        }

        // Send notification to admins
        $action = $wasRejected ? 'company_resubmitted' : 'company_updated';
        NotificationHelper::notifyAdmins(
            $action,
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }

    public function saveTab(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id !== $representative->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'active_tab' => 'required|string|in:basic,license,manager,documents,registration',
        ]);

        session(['active_tab_' . $company->id => $validated['active_tab']]);

        return response()->json(['success' => true]);
    }

    public function resubmit(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id !== $representative->id) {
            abort(403, 'غير مصرح لك بإعادة تقديم هذه الشركة');
        }

        if ($company->status !== 'rejected') {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'يمكن إعادة التقديم فقط للشركات المرفوضة');
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'يجب رفع جميع المستندات المطلوبة قبل إعادة التقديم');
        }

        $company->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        $company->logActivity('resubmitted', 'تم إعادة تقديم الطلب للمراجعة');

        NotificationHelper::notifyAdmins(
            'company_resubmitted',
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم إعادة تقديم الطلب للمراجعة بنجاح');
    }
}
