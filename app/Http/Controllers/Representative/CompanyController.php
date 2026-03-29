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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'required|email|unique:local_companies,email',

            // Pre-registration fields
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),

            // Step 2: License Information
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:255',
            'license_issuer' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'food_drug_registration_number' => 'required|string|max:255',
            'chamber_of_commerce_number' => 'nullable|string|max:255',

            // Step 3: Manager Information
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_email' => 'nullable|email|max:255',
        ], [
            'food_drug_registration_number.required' => __('companies.val_food_drug_reg_required'),
            'pre_registration_sequence.required_if' => __('companies.val_pre_reg_sequence_required'),
            'pre_registration_year.required_if' => __('companies.val_pre_reg_year_required'),
            'pre_registration_year.min' => __('companies.val_pre_reg_year_min'),
            'pre_registration_year.max' => __('companies.val_pre_reg_year_max'),
        ]);

        if (!empty($validated['is_pre_registered']) && !empty($validated['pre_registration_sequence']) && !empty($validated['pre_registration_year'])) {
            $validated['pre_registration_number'] = $validated['pre_registration_year'] . '-' . $validated['pre_registration_sequence'];
        }
        unset($validated['pre_registration_sequence']);

        $representative = Auth::guard('representative')->user();

        $company = LocalCompany::create([
            ...$validated,
            'representative_id' => $representative->id,
            'status' => 'uploading_documents',
        ]);

        NotificationHelper::notifyAdmins(
            'company_created',
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('companies.msg_company_created'));
    }

    public function show(LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
            abort(403, __('companies.unauthorized_view'));
        }

        return view('representative.companies.show', compact('company'));
    }

    public function edit(LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
            abort(403, __('companies.unauthorized_edit'));
        }

        if (!in_array($company->status, ['rejected', 'uploading_documents'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', __('companies.cannot_edit_current_status'));
        }

        return view('representative.companies.edit', compact('company'));
    }

    public function update(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
            abort(403, __('companies.unauthorized_edit'));
        }

        if (!in_array($company->status, ['rejected', 'uploading_documents'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', __('companies.cannot_edit_current_status'));
        }

        $validated = $request->validate([
            // Step 1: Company Information
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:distributor,supplier',
            'company_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'street' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'required|email|unique:local_companies,email,' . $company->id,

            // Pre-registration fields
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),

            // Step 2: License Information
            'license_type' => 'required|in:company,partnership,authorized_agent',
            'license_specialty' => 'required|in:medicines,medical_supplies,medical_equipment',
            'license_number' => 'nullable|string|max:255',
            'license_issuer' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'food_drug_registration_number' => 'required|string|max:255',
            'chamber_of_commerce_number' => 'nullable|string|max:255',

            // Step 3: Manager Information
            'manager_name' => 'required|string|max:255',
            'manager_position' => 'nullable|string|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_email' => 'nullable|email|max:255',
        ], [
            'food_drug_registration_number.required' => __('companies.val_food_drug_reg_required'),
            'pre_registration_sequence.required_if' => __('companies.val_pre_reg_sequence_required'),
            'pre_registration_year.required_if' => __('companies.val_pre_reg_year_required'),
            'pre_registration_year.min' => __('companies.val_pre_reg_year_min'),
            'pre_registration_year.max' => __('companies.val_pre_reg_year_max'),
        ]);

        if (!empty($validated['is_pre_registered']) && !empty($validated['pre_registration_sequence']) && !empty($validated['pre_registration_year'])) {
            $validated['pre_registration_number'] = $validated['pre_registration_year'] . '-' . $validated['pre_registration_sequence'];
        }
        unset($validated['pre_registration_sequence']);

        $company->update($validated);

        $wasRejected = $company->status == 'rejected';
        if ($wasRejected) {
            $company->update([
                'status' => 'uploading_documents',
                'rejection_reason' => null,
            ]);
            $company->logActivity('resubmitted', __('companies.log_resubmitted_after_edit'));
        }

        $action = $wasRejected ? 'company_resubmitted' : 'company_updated';
        NotificationHelper::notifyAdmins(
            $action,
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('companies.msg_updated'));
    }

    public function saveTab(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
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

        if ($company->representative_id != $representative->id) {
            abort(403, __('companies.unauthorized_resubmit'));
        }

        if ($company->status != 'rejected') {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', __('companies.msg_resubmit_only_rejected'));
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', __('companies.msg_upload_docs_before_resubmit'));
        }

        $company->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        $company->logActivity('resubmitted', __('companies.log_resubmitted_for_review'));

        NotificationHelper::notifyAdmins(
            'company_resubmitted',
            'local',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('companies.msg_resubmitted_success'));
    }
}
