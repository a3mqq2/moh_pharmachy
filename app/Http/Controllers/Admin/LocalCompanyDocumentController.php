<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalCompany;
use App\Models\LocalCompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocalCompanyDocumentController extends Controller
{
    public function store(Request $request, LocalCompany $localCompany)
    {
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
        $path = $file->store('local-companies/' . $localCompany->id . '/documents', 'public');

        $document = LocalCompanyDocument::create([
            'local_company_id' => $localCompany->id,
            'document_type' => $request->document_type,
            'custom_name' => $request->custom_name,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
        ]);

        $localCompany->logActivity('document_uploaded', 'تم رفع مستند: ' . $document->display_name);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم رفع المستند بنجاح');
    }

    public function download(LocalCompany $localCompany, LocalCompanyDocument $localCompanyDocument)
    {
        if ($localCompanyDocument->local_company_id !== $localCompany->id) {
            abort(404);
        }

        return Storage::disk('public')->download($localCompanyDocument->file_path, $localCompanyDocument->original_name);
    }

    public function destroy(LocalCompany $localCompany, LocalCompanyDocument $localCompanyDocument)
    {
        if ($localCompanyDocument->local_company_id !== $localCompany->id) {
            abort(404);
        }

        $docName = $localCompanyDocument->display_name;
        Storage::disk('public')->delete($localCompanyDocument->file_path);
        $localCompanyDocument->delete();

        $localCompany->logActivity('document_deleted', 'تم حذف مستند: ' . $docName);

        return redirect()->route('admin.local-companies.show', $localCompany)
            ->with('success', 'تم حذف المستند بنجاح');
    }
}
