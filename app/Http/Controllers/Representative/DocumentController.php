<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\LocalCompany;
use App\Models\LocalCompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, LocalCompany $company)
    {
        $representative = Auth::guard('representative')->user();

        // التحقق من أن الشركة تخص الممثل الحالي
        if ($company->representative_id != $representative->id) {
            abort(403, 'غير مصرح لك برفع مستندات لهذه الشركة');
        }

        // التحقق من أن الشركة قيد رفع المستندات أو مرفوضة
        if (!in_array($company->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'لا يمكن رفع مستندات للشركة في هذه الحالة');
        }

        $request->validate([
            'document_type' => 'required|string',
            'custom_name' => 'required_if:document_type,other|nullable|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp,zip,rar',
            'notes' => 'nullable|string|max:1000',
        ], [
            'document_type.required' => 'نوع المستند مطلوب',
            'custom_name.required_if' => 'اسم المستند مطلوب عند اختيار "أخرى"',
            'file.required' => 'الملف مطلوب',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'file.mimes' => 'نوع الملف غير مدعوم',
        ]);

        $file = $request->file('file');
        $path = $file->store('local-companies/' . $company->id . '/documents', 'public');

        $document = LocalCompanyDocument::create([
            'local_company_id' => $company->id,
            'document_type' => $request->document_type,
            'custom_name' => $request->custom_name,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'uploaded_by' => null, // Representative upload doesn't have user_id
        ]);

        // Check if all required documents are uploaded
        if ($company->hasAllRequiredDocuments() && $company->status == 'uploading_documents') {
            $company->update(['status' => 'pending']);

            session(['active_tab_' . $company->id => 'documents']);
            return redirect()->route('representative.companies.show', $company)
                ->with('success', 'تم رفع المستند بنجاح. تم إرسال الطلب للمراجعة من قبل الإدارة.');
        }

        // Keep documents tab active
        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم رفع المستند بنجاح');
    }

    public function download(LocalCompany $company, LocalCompanyDocument $document)
    {
        $representative = Auth::guard('representative')->user();

        // التحقق من أن الشركة تخص الممثل الحالي
        if ($company->representative_id != $representative->id) {
            abort(403);
        }

        if ($document->local_company_id != $company->id) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function update(Request $request, LocalCompany $company, LocalCompanyDocument $document)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
            abort(403);
        }

        if ($document->local_company_id != $company->id) {
            abort(404);
        }

        if (!in_array($company->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'لا يمكن تعديل مستندات للشركة في هذه الحالة');
        }

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp,zip,rar',
            'notes' => 'nullable|string|max:1000',
        ], [
            'file.required' => 'الملف مطلوب',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'file.mimes' => 'نوع الملف غير مدعوم',
        ]);

        Storage::disk('public')->delete($document->file_path);

        $file = $request->file('file');
        $path = $file->store('local-companies/' . $company->id . '/documents', 'public');

        $document->update([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
        ]);

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم تحديث المستند بنجاح');
    }

    public function destroy(LocalCompany $company, LocalCompanyDocument $document)
    {
        $representative = Auth::guard('representative')->user();

        if ($company->representative_id != $representative->id) {
            abort(403);
        }

        if ($document->local_company_id != $company->id) {
            abort(404);
        }

        if (!in_array($company->status, ['uploading_documents', 'rejected'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', 'لا يمكن حذف مستندات للشركة في هذه الحالة');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', 'تم حذف المستند بنجاح');
    }
}
