<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductDocument;
use App\Models\ForeignCompany;
use App\Models\User;
use App\Notifications\NewPharmaceuticalProductRegistered;
use App\Notifications\PharmaceuticalProductSubmittedForReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class PharmaceuticalProductController extends Controller
{
    public function index()
    {
        $representative = auth('representative')->user();

        $products = PharmaceuticalProduct::with(['foreignCompany.localCompany'])
            ->where('representative_id', $representative->id)
            ->latest()
            ->paginate(15);

        $activeForeignCompaniesCount = ForeignCompany::where('representative_id', $representative->id)
            ->where('status', 'active')
            ->count();

        return view('representative.pharmaceutical-products.index', compact('products', 'activeForeignCompaniesCount'));
    }

    public function create()
    {
        $representative = auth('representative')->user();

        $foreignCompanies = ForeignCompany::where('representative_id', $representative->id)
            ->where('status', 'active')
            ->with('localCompany')
            ->get();

        if ($foreignCompanies->isEmpty()) {
            return redirect()->route('representative.dashboard')
                ->with('error', __('products.msg_no_active_foreign'));
        }

        return view('representative.pharmaceutical-products.create', compact('foreignCompanies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'pharmaceutical_form' => 'required|string|max:255',
            'concentration' => 'required|string|max:255',
            'usage_methods' => 'required|array|min:1',
            'usage_methods.*' => 'in:oral,injection,topical,inhalation,other',
            'other_usage_method' => 'required_if:usage_methods.*,other|nullable|string|max:255',
            'foreign_company_id' => 'required|exists:foreign_companies,id',
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
        ]);

        $representative = auth('representative')->user();

        $foreignCompany = ForeignCompany::where('id', $validated['foreign_company_id'])
            ->where('representative_id', $representative->id)
            ->where('status', 'active')
            ->firstOrFail();

        $productData = [
            'foreign_company_id' => $validated['foreign_company_id'],
            'representative_id' => $representative->id,
            'product_name' => $validated['product_name'],
            'scientific_name' => $validated['scientific_name'],
            'pharmaceutical_form' => $validated['pharmaceutical_form'],
            'concentration' => $validated['concentration'],
            'usage_methods' => $validated['usage_methods'],
            'other_usage_method' => $validated['other_usage_method'] ?? null,
            'status' => 'uploading_documents',
        ];

        if ($request->is_pre_registered) {
            $year = $request->pre_registration_year;
            $seq = (int) $request->pre_registration_sequence;
            $preRegNumber = "{$year}-{$seq}";

            $exists = PharmaceuticalProduct::where('pre_registration_number', $preRegNumber)->exists();
            $regExists = PharmaceuticalProduct::where('registration_number', $preRegNumber)->exists();

            if ($exists || $regExists) {
                return redirect()->back()
                    ->with('error', __('companies.msg_reg_number_exists', ['number' => $preRegNumber]))
                    ->withInput();
            }

            $productData['is_pre_registered'] = true;
            $productData['pre_registration_number'] = $preRegNumber;
            $productData['pre_registration_year'] = $year;
        } else {
            $productData['is_pre_registered'] = false;
        }

        $product = PharmaceuticalProduct::create($productData);

        $admins = User::role('admin')->get();
        Notification::send($admins, new NewPharmaceuticalProductRegistered($product, $representative));

        return redirect()->route('representative.pharmaceutical-products.show', $product)
            ->with('success', __('products.msg_registered_success'));
    }

    public function show(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        $pharmaceuticalProduct->load(['foreignCompany.localCompany', 'reviewedBy']);

        return view('representative.pharmaceutical-products.show', compact('pharmaceuticalProduct'));
    }

    public function edit(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', __('products.msg_cannot_edit_status'));
        }

        $foreignCompanies = ForeignCompany::where('representative_id', $representative->id)
            ->where('status', 'active')
            ->with('localCompany')
            ->get();

        return view('representative.pharmaceutical-products.edit', compact('pharmaceuticalProduct', 'foreignCompanies'));
    }

    public function update(Request $request, PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', __('products.msg_cannot_edit_status'));
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'pharmaceutical_form' => 'required|string|max:255',
            'concentration' => 'required|string|max:255',
            'usage_methods' => 'required|array|min:1',
            'usage_methods.*' => 'in:oral,injection,topical,inhalation,other',
            'other_usage_method' => 'required_if:usage_methods.*,other|nullable|string|max:255',
            'foreign_company_id' => 'required|exists:foreign_companies,id',
            'is_pre_registered' => 'nullable|boolean',
            'pre_registration_year' => 'required_if:is_pre_registered,1|nullable|integer|min:1990|max:' . date('Y'),
            'pre_registration_sequence' => 'required_if:is_pre_registered,1|nullable|integer|min:1',
        ]);

        $foreignCompany = ForeignCompany::where('id', $validated['foreign_company_id'])
            ->where('representative_id', $representative->id)
            ->where('status', 'active')
            ->firstOrFail();

        $updateData = [
            'foreign_company_id' => $validated['foreign_company_id'],
            'product_name' => $validated['product_name'],
            'scientific_name' => $validated['scientific_name'],
            'pharmaceutical_form' => $validated['pharmaceutical_form'],
            'concentration' => $validated['concentration'],
            'usage_methods' => $validated['usage_methods'],
            'other_usage_method' => $validated['other_usage_method'] ?? null,
        ];

        if ($request->is_pre_registered) {
            $year = $request->pre_registration_year;
            $seq = (int) $request->pre_registration_sequence;
            $preRegNumber = "{$year}-{$seq}";

            $exists = PharmaceuticalProduct::where('pre_registration_number', $preRegNumber)
                ->where('id', '!=', $pharmaceuticalProduct->id)
                ->exists();
            $regExists = PharmaceuticalProduct::where('registration_number', $preRegNumber)
                ->where('id', '!=', $pharmaceuticalProduct->id)
                ->exists();

            if ($exists || $regExists) {
                return redirect()->back()
                    ->with('error', __('companies.msg_reg_number_exists', ['number' => $preRegNumber]))
                    ->withInput();
            }

            $updateData['is_pre_registered'] = true;
            $updateData['pre_registration_number'] = $preRegNumber;
            $updateData['pre_registration_year'] = $year;
        } else {
            $updateData['is_pre_registered'] = false;
            $updateData['pre_registration_number'] = null;
        }

        $pharmaceuticalProduct->update($updateData);

        return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
            ->with('success', __('products.msg_updated_success'));
    }

    public function destroy(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status != 'uploading_documents') {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', __('products.msg_cannot_delete_status'));
        }

        $pharmaceuticalProduct->delete();

        return redirect()->route('representative.pharmaceutical-products.index')
            ->with('success', __('products.msg_deleted_success'));
    }

    public function getCompanyProducts(ForeignCompany $foreignCompany)
    {
        $representative = auth('representative')->user();

        if ($foreignCompany->representative_id != $representative->id) {
            abort(403);
        }

        $products = $foreignCompany->pharmaceuticalProducts()->latest()->get();

        return response()->json($products);
    }

    public function uploadDocument(Request $request, PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return back()->with('error', __('products.msg_cannot_upload_doc_status'));
        }

        $validated = $request->validate([
            'document_type' => 'required|in:' . implode(',', array_merge(PharmaceuticalProductDocument::getRequiredDocumentTypes(), PharmaceuticalProductDocument::getOptionalDocumentTypes())),
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $fileName = \Illuminate\Support\Str::random(32) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pharmaceutical-documents', $fileName, 'public');

        $pharmaceuticalProduct->documents()->create([
            'document_type' => $validated['document_type'],
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => $representative->id,
        ]);

        return back()->with('success', __('products.msg_doc_uploaded_success'));
    }

    public function deleteDocument(PharmaceuticalProduct $pharmaceuticalProduct, PharmaceuticalProductDocument $document)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($document->pharmaceutical_product_id != $pharmaceuticalProduct->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status != 'uploading_documents' && $pharmaceuticalProduct->status != 'rejected') {
            return back()->with('error', __('products.msg_cannot_delete_doc_status'));
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', __('products.msg_doc_deleted_success'));
    }

    public function updateDocument(Request $request, PharmaceuticalProduct $pharmaceuticalProduct, PharmaceuticalProductDocument $document)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($document->pharmaceutical_product_id != $pharmaceuticalProduct->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return back()->with('error', __('products.msg_cannot_update_doc_status'));
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        Storage::disk('public')->delete($document->file_path);

        $file = $request->file('document');
        $fileName = \Illuminate\Support\Str::random(32) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pharmaceutical_product_documents', $fileName, 'public');

        $document->update([
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', __('products.msg_doc_updated_success'));
    }

    public function submitForReview(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return back()->with('error', __('products.msg_cannot_submit_status'));
        }

        if (!$pharmaceuticalProduct->hasAllRequiredDocuments()) {
            return back()->with('error', __('products.msg_must_upload_all_docs'));
        }

        $pharmaceuticalProduct->update([
            'status' => 'pending_review',
            'rejection_reason' => null,
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new PharmaceuticalProductSubmittedForReview($pharmaceuticalProduct, $representative));

        return back()->with('success', __('products.msg_submitted_for_review'));
    }

    public function uploadReceipt(Request $request, PharmaceuticalProduct $pharmaceuticalProduct, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($invoice->pharmaceutical_product_id != $pharmaceuticalProduct->id) {
            abort(403);
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($invoice->receipt_path) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $file = $request->file('receipt');
        $fileName = \Illuminate\Support\Str::random(32) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pharmaceutical_product_invoices', $fileName, 'public');

        $invoice->update([
            'receipt_path' => $filePath,
            'status' => 'pending_review',
        ]);

        $pharmaceuticalProduct->update([
            'status' => 'payment_review',
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new \App\Notifications\PharmaceuticalProductReceiptUploaded($pharmaceuticalProduct, $invoice));

        return back()->with('success', __('products.msg_receipt_uploaded_success'));
    }

    public function editDetails(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status != 'preliminary_approved') {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', __('products.msg_cannot_edit_details_status'));
        }

        return view('representative.pharmaceutical-products.edit-details', compact('pharmaceuticalProduct'));
    }

    public function updateDetails(Request $request, PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status != 'preliminary_approved') {
            return back()->with('error', __('products.msg_cannot_edit_data_status'));
        }

        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'packaging' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'shelf_life_months' => 'required|integer|min:1',
            'storage_conditions' => 'required|string|max:255',
            'free_sale' => 'required|in:Free Sale,For Export Only',
            'samples' => 'required|in:Samples Provided,No Samples Provided',
            'pharmacopeal_ref' => 'required|string|max:50',
            'item_classification' => 'required|in:Requested Item,Alternative Item,Optional Item',
        ]);

        $pharmaceuticalProduct->update($validated);

        return back()->with('success', __('products.msg_data_saved_success'));
    }

    public function submitDetails(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id != $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status != 'preliminary_approved') {
            return back()->with('error', __('products.msg_cannot_submit_data_status'));
        }

        if (!$pharmaceuticalProduct->hasCompleteDetailedInfo()) {
            return back()->with('error', __('products.msg_must_complete_details'));
        }

        $pharmaceuticalProduct->update([
            'status' => 'pending_final_approval',
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new \App\Notifications\PharmaceuticalProductDetailsSubmitted($pharmaceuticalProduct, $pharmaceuticalProduct->representative));

        return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
            ->with('success', __('products.msg_details_submitted'));
    }
}
