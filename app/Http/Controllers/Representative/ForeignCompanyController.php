<?php

namespace App\Http\Controllers\Representative;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\ForeignCompany;
use App\Models\LocalCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForeignCompanyController extends Controller
{
    public function index()
    {
        $representative = auth('representative')->user();

        $companies = ForeignCompany::where('representative_id', $representative->id)
            ->with(['localCompany', 'documents', 'invoices'])
            ->latest()
            ->paginate(10);

        return view('representative.foreign-companies.index', compact('companies'));
    }

    public function create()
    {
        $representative = auth('representative')->user();

        // Check if representative has any supplier-type local company
        $localCompanies = LocalCompany::where('representative_id', $representative->id)
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->get();

        if ($localCompanies->isEmpty()) {
            return redirect()->route('representative.foreign-companies.index')
                ->with('error', __('products.msg_must_have_supplier'));
        }

        $countries = $this->getCountriesList();

        return view('representative.foreign-companies.create', compact('localCompanies', 'countries'));
    }

    public function store(Request $request)
    {
        $representative = auth('representative')->user();

        // Validate that representative has an active supplier company
        $localCompany = LocalCompany::where('id', $request->local_company_id)
            ->where('representative_id', $representative->id)
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->first();

        if (!$localCompany) {
            return redirect()->back()
                ->with('error', __('products.msg_local_invalid'))
                ->withInput();
        }

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
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
        ]);

        $validated['representative_id'] = $representative->id;
        $validated['status'] = 'uploading_documents';

        if ($request->is_pre_registered) {
            $validated['is_pre_registered'] = true;
            $validated['pre_registration_number'] = $request->pre_registration_year . '-' . $request->pre_registration_sequence;
        } else {
            $validated['is_pre_registered'] = false;
        }

        unset($validated['pre_registration_year'], $validated['pre_registration_sequence']);

        $company = ForeignCompany::create($validated);

        // Send notification to admins
        NotificationHelper::notifyAdmins(
            'company_created',
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_company_created'));
    }

    public function show($id)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $id)
            ->where('representative_id', $representative->id)
            ->with([
                'localCompany',
                'documents',
                'invoices.issuedBy',
                'invoices.approvedBy',
            ])
            ->firstOrFail();

        $documentTypes = \App\Models\ForeignCompanyDocument::getDocumentTypes();
        $requiredDocumentTypes = \App\Models\ForeignCompanyDocument::getRequiredDocumentTypes();

        // Check which required documents are missing
        $uploadedDocumentTypes = $company->documents->pluck('document_type')->toArray();
        $missingDocuments = [];

        foreach ($requiredDocumentTypes as $type) {
            if (!in_array($type, $uploadedDocumentTypes)) {
                $missingDocuments[] = $type;
            }
        }

        // Check FDA or EMEA
        $hasFdaOrEmea = $company->documents()
            ->whereIn('document_type', ['fda_certificate', 'emea_certificate'])
            ->exists();

        // Check CPP or FSC (minimum 5)
        $cppFscCount = $company->documents()
            ->whereIn('document_type', ['cpp_certificate', 'fsc_certificate'])
            ->count();

        $pendingInvoice = $company->getPendingInvoice();
        $invoiceAwaitingReview = $company->getInvoiceAwaitingReceiptReview();

        // Get available document types
        // Repeatable types should always be available
        $repeatableTypes = ['cpp_certificate', 'fsc_certificate', 'registration_certificates', 'other'];

        $availableDocumentTypes = [];
        foreach ($documentTypes as $type => $name) {
            // Include if: it's repeatable OR it hasn't been uploaded yet
            if (in_array($type, $repeatableTypes) || !in_array($type, $uploadedDocumentTypes)) {
                $availableDocumentTypes[$type] = $name;
            }
        }

        return view('representative.foreign-companies.show', compact(
            'company',
            'documentTypes',
            'requiredDocumentTypes',
            'missingDocuments',
            'hasFdaOrEmea',
            'cppFscCount',
            'pendingInvoice',
            'invoiceAwaitingReview',
            'availableDocumentTypes'
        ));
    }

    public function edit($id)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $id)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!$company->canEdit()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', __('companies.cannot_edit_current_status'));
        }

        $localCompanies = LocalCompany::where('representative_id', $representative->id)
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->get();

        $countries = $this->getCountriesList();

        return view('representative.foreign-companies.edit', compact('company', 'localCompanies', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $id)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!$company->canEdit()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', __('companies.cannot_edit_current_status'));
        }

        // Validate that the local company still belongs to this representative
        $localCompany = LocalCompany::where('id', $request->local_company_id)
            ->where('representative_id', $representative->id)
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->first();

        if (!$localCompany) {
            return redirect()->back()
                ->with('error', __('products.msg_local_invalid'))
                ->withInput();
        }

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
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
        ]);

        if ($request->is_pre_registered) {
            $validated['is_pre_registered'] = true;
            $validated['pre_registration_number'] = $request->pre_registration_year . '-' . $request->pre_registration_sequence;
        } else {
            $validated['is_pre_registered'] = false;
            $validated['pre_registration_number'] = null;
        }

        unset($validated['pre_registration_year'], $validated['pre_registration_sequence']);

        $company->update($validated);

        // Send notification to admins
        $action = $company->status == 'rejected' ? 'company_resubmitted' : 'company_updated';
        NotificationHelper::notifyAdmins(
            $action,
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_updated'));
    }

    public function submitForReview($id)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $id)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!in_array($company->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', __('companies.msg_cannot_submit_current_status'));
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', __('companies.msg_upload_docs_before_resubmit'));
        }

        $wasRejected = $company->status == 'rejected';

        $company->markAsPending();

        $action = $wasRejected ? 'company_resubmitted' : 'company_updated';

        NotificationHelper::notifyAdmins(
            $action,
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', __('companies.msg_resubmitted_success'));
    }

    private function getCountriesList(): array
    {
        return __('companies.countries_list');
    }
}
