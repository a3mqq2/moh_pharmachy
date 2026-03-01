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
                ->with('error', 'لا توجد شركات أجنبية مفعلة. يجب أن يكون لديك شركة أجنبية مفعلة لتسجيل صنف دوائي.');
        }

        return view('representative.pharmaceutical-products.create', compact('foreignCompanies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'pharmaceutical_form' => 'required|string|max:255',
            'concentration' => 'required|string|max:255',
            'usage_methods' => 'required|array|min:1',
            'usage_methods.*' => 'in:oral,injection,topical,inhalation,other',
            'other_usage_method' => 'required_if:usage_methods.*,other|nullable|string|max:255',
            'foreign_company_id' => 'required|exists:foreign_companies,id',
        ]);

        $representative = auth('representative')->user();

        $foreignCompany = ForeignCompany::where('id', $validated['foreign_company_id'])
            ->where('representative_id', $representative->id)
            ->where('status', 'active')
            ->firstOrFail();

        $product = PharmaceuticalProduct::create([
            'foreign_company_id' => $validated['foreign_company_id'],
            'representative_id' => $representative->id,
            'product_name' => $validated['product_name'],
            'pharmaceutical_form' => $validated['pharmaceutical_form'],
            'concentration' => $validated['concentration'],
            'usage_methods' => $validated['usage_methods'],
            'other_usage_method' => $validated['other_usage_method'] ?? null,
            'status' => 'uploading_documents',
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new NewPharmaceuticalProductRegistered($product, $representative));

        return redirect()->route('representative.pharmaceutical-products.show', $product)
            ->with('success', 'تم تسجيل طلب الصنف الدوائي بنجاح. الحالة: قيد رفع المستندات');
    }

    public function show(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        $pharmaceuticalProduct->load(['foreignCompany.localCompany', 'reviewedBy']);

        return view('representative.pharmaceutical-products.show', compact('pharmaceuticalProduct'));
    }

    public function edit(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', 'لا يمكن تعديل هذا الصنف في حالته الحالية.');
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

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', 'لا يمكن تعديل هذا الصنف في حالته الحالية.');
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'pharmaceutical_form' => 'required|string|max:255',
            'concentration' => 'required|string|max:255',
            'usage_methods' => 'required|array|min:1',
            'usage_methods.*' => 'in:oral,injection,topical,inhalation,other',
            'other_usage_method' => 'required_if:usage_methods.*,other|nullable|string|max:255',
            'foreign_company_id' => 'required|exists:foreign_companies,id',
        ]);

        $foreignCompany = ForeignCompany::where('id', $validated['foreign_company_id'])
            ->where('representative_id', $representative->id)
            ->where('status', 'active')
            ->firstOrFail();

        $pharmaceuticalProduct->update([
            'foreign_company_id' => $validated['foreign_company_id'],
            'product_name' => $validated['product_name'],
            'pharmaceutical_form' => $validated['pharmaceutical_form'],
            'concentration' => $validated['concentration'],
            'usage_methods' => $validated['usage_methods'],
            'other_usage_method' => $validated['other_usage_method'] ?? null,
        ]);

        return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
            ->with('success', 'تم تحديث بيانات الصنف الدوائي بنجاح.');
    }

    public function destroy(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'uploading_documents') {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', 'لا يمكن حذف هذا الصنف في حالته الحالية.');
        }

        $pharmaceuticalProduct->delete();

        return redirect()->route('representative.pharmaceutical-products.index')
            ->with('success', 'تم حذف الصنف الدوائي بنجاح.');
    }

    public function getCompanyProducts(ForeignCompany $foreignCompany)
    {
        $representative = auth('representative')->user();

        if ($foreignCompany->representative_id !== $representative->id) {
            abort(403);
        }

        $products = $foreignCompany->pharmaceuticalProducts()->latest()->get();

        return response()->json($products);
    }

    public function uploadDocument(Request $request, PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'uploading_documents') {
            return back()->with('error', 'لا يمكن رفع مستندات في الحالة الحالية.');
        }

        $validated = $request->validate([
            'document_type' => 'required|in:' . implode(',', PharmaceuticalProductDocument::getRequiredDocumentTypes()),
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . uniqid() . '_' . $pharmaceuticalProduct->id . '_' . $validated['document_type'] . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pharmaceutical-documents', $fileName, 'public');

        $pharmaceuticalProduct->documents()->create([
            'document_type' => $validated['document_type'],
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => $representative->id,
        ]);

        return back()->with('success', 'تم رفع المستند بنجاح.');
    }

    public function deleteDocument(PharmaceuticalProduct $pharmaceuticalProduct, PharmaceuticalProductDocument $document)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($document->pharmaceutical_product_id !== $pharmaceuticalProduct->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'uploading_documents' && $pharmaceuticalProduct->status !== 'rejected') {
            return back()->with('error', 'لا يمكن حذف مستندات في الحالة الحالية.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'تم حذف المستند بنجاح.');
    }

    public function updateDocument(Request $request, PharmaceuticalProduct $pharmaceuticalProduct, PharmaceuticalProductDocument $document)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($document->pharmaceutical_product_id !== $pharmaceuticalProduct->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return back()->with('error', 'لا يمكن تعديل مستندات في الحالة الحالية.');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        Storage::disk('public')->delete($document->file_path);

        $file = $request->file('document');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pharmaceutical_product_documents', $fileName, 'public');

        $document->update([
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'تم تحديث المستند بنجاح.');
    }

    public function submitForReview(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if (!in_array($pharmaceuticalProduct->status, ['uploading_documents', 'rejected'])) {
            return back()->with('error', 'لا يمكن إرسال الطلب في الحالة الحالية.');
        }

        if (!$pharmaceuticalProduct->hasAllRequiredDocuments()) {
            return back()->with('error', 'يجب رفع جميع المستندات المطلوبة قبل الإرسال.');
        }

        $pharmaceuticalProduct->update([
            'status' => 'pending_review',
            'rejection_reason' => null,
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new PharmaceuticalProductSubmittedForReview($pharmaceuticalProduct, $representative));

        return back()->with('success', 'تم إرسال الطلب للمراجعة بنجاح.');
    }

    public function uploadReceipt(Request $request, PharmaceuticalProduct $pharmaceuticalProduct, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($invoice->pharmaceutical_product_id !== $pharmaceuticalProduct->id) {
            abort(403);
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($invoice->receipt_path) {
            Storage::disk('public')->delete($invoice->receipt_path);
        }

        $file = $request->file('receipt');
        $fileName = 'receipt_' . $invoice->id . '_' . time() . '.' . $file->getClientOriginalExtension();
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

        return back()->with('success', 'تم رفع الإيصال بنجاح وإرساله للمراجعة.');
    }

    public function editDetails(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'preliminary_approved') {
            return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
                ->with('error', 'لا يمكن تعديل البيانات التفصيلية في الحالة الحالية.');
        }

        return view('representative.pharmaceutical-products.edit-details', compact('pharmaceuticalProduct'));
    }

    public function updateDetails(Request $request, PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'preliminary_approved') {
            return back()->with('error', 'لا يمكن تعديل البيانات في الحالة الحالية.');
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

        return back()->with('success', 'تم حفظ البيانات بنجاح.');
    }

    public function submitDetails(PharmaceuticalProduct $pharmaceuticalProduct)
    {
        $representative = auth('representative')->user();

        if ($pharmaceuticalProduct->representative_id !== $representative->id) {
            abort(403);
        }

        if ($pharmaceuticalProduct->status !== 'preliminary_approved') {
            return back()->with('error', 'لا يمكن إرسال البيانات في الحالة الحالية.');
        }

        if (!$pharmaceuticalProduct->hasCompleteDetailedInfo()) {
            return back()->with('error', 'يجب استكمال جميع البيانات التفصيلية قبل الإرسال.');
        }

        $pharmaceuticalProduct->update([
            'status' => 'pending_final_approval',
        ]);

        $admins = User::role('admin')->get();
        Notification::send($admins, new \App\Notifications\PharmaceuticalProductDetailsSubmitted($pharmaceuticalProduct, $pharmaceuticalProduct->representative));

        return redirect()->route('representative.pharmaceutical-products.show', $pharmaceuticalProduct)
            ->with('success', 'تم إرسال البيانات التفصيلية للمراجعة النهائية.');
    }
}
