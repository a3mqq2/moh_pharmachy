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
                ->with('error', 'يجب أن يكون لديك شركة محلية من نوع "مورد" مفعلة لتتمكن من تسجيل شركة أجنبية');
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
                ->with('error', 'الشركة المحلية المختارة غير صالحة أو غير مفعلة')
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
        ]);

        $validated['representative_id'] = $representative->id;
        $validated['status'] = 'uploading_documents';

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
            ->with('success', 'تم إنشاء الشركة الأجنبية بنجاح. يرجى رفع المستندات المطلوبة');
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
                ->with('error', 'لا يمكن تعديل بيانات الشركة في الحالة الحالية');
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
                ->with('error', 'لا يمكن تعديل بيانات الشركة في الحالة الحالية');
        }

        // Validate that the local company still belongs to this representative
        $localCompany = LocalCompany::where('id', $request->local_company_id)
            ->where('representative_id', $representative->id)
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->first();

        if (!$localCompany) {
            return redirect()->back()
                ->with('error', 'الشركة المحلية المختارة غير صالحة أو غير مفعلة')
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
        ]);

        $company->update($validated);

        // Send notification to admins
        $action = $company->status === 'rejected' ? 'company_resubmitted' : 'company_updated';
        NotificationHelper::notifyAdmins(
            $action,
            'foreign',
            $company->company_name,
            $company->id,
            $representative->name
        );

        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }

    public function submitForReview($id)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $id)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!in_array($company->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', 'لا يمكن إرسال الطلب للمراجعة في الحالة الحالية');
        }

        if (!$company->hasAllRequiredDocuments()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', 'يجب رفع جميع المستندات المطلوبة قبل إرسال الطلب للمراجعة');
        }

        $wasRejected = $company->status === 'rejected';

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
            ->with('success', 'تم إرسال الطلب للمراجعة بنجاح');
    }

    private function getCountriesList(): array
    {
        return [
            'مصر',
            'السعودية',
            'الإمارات',
            'الأردن',
            'الكويت',
            'قطر',
            'البحرين',
            'عمان',
            'المغرب',
            'تونس',
            'الجزائر',
            'السودان',
            'اليمن',
            'لبنان',
            'سوريا',
            'العراق',
            'فلسطين',
            'الصين',
            'الهند',
            'تركيا',
            'إيران',
            'باكستان',
            'الولايات المتحدة',
            'المملكة المتحدة',
            'ألمانيا',
            'فرنسا',
            'إيطاليا',
            'إسبانيا',
            'سويسرا',
            'بلجيكا',
            'هولندا',
            'السويد',
            'الدنمارك',
            'النرويج',
            'فنلندا',
            'كندا',
            'أستراليا',
            'اليابان',
            'كوريا الجنوبية',
            'البرازيل',
            'المكسيك',
            'الأرجنتين',
            'جنوب أفريقيا',
            'نيجيريا',
            'كينيا',
            'أخرى',
        ];
    }
}
