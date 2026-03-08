<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyDocumentController extends Controller
{
    public function store(Request $request, $companyId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!$company->canUploadDocuments()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', 'لا يمكن رفع المستندات في الحالة الحالية');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(ForeignCompanyDocument::getDocumentTypes())),
            'document_name' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string',
        ]);

        // Auto-generate document_name from document_type if not provided
        if (empty($validated['document_name'])) {
            $documentTypes = ForeignCompanyDocument::getDocumentTypes();
            $validated['document_name'] = $documentTypes[$validated['document_type']] ?? 'مستند';
        }

        // Check if document type already exists (for non-repeatable types)
        // Repeatable types: cpp_certificate, fsc_certificate, registration_certificates, other
        $repeatableTypes = ['cpp_certificate', 'fsc_certificate', 'registration_certificates', 'other'];

        // Core documents (can only upload once) + FDA/EMEA (can only upload once each)
        $nonRepeatableTypes = [
            'official_registration_request',
            'agency_agreement',
            'registration_forms',
            'gmp_certificate',
            'fda_certificate',
            'emea_certificate',
            'manufacturing_license',
            'financial_report',
            'products_list',
            'site_master_file',
            'exclusive_agency_contract',
        ];

        // Only check for duplicates if it's a non-repeatable type
        if (!in_array($validated['document_type'], $repeatableTypes)) {
            $existingDocument = $company->documents()
                ->where('document_type', $validated['document_type'])
                ->first();

            if ($existingDocument) {
                return redirect()->back()
                    ->with('error', 'تم رفع هذا النوع من المستندات مسبقاً. المستندات الاختيارية (CPP, FSC, شهادات تسجيل) يمكن رفعها أكثر من مرة.');
            }
        }

        // Store the file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs(
            'foreign_companies/' . $company->id . '/documents',
            $fileName,
            'public'
        );

        // Create document record
        $document = $company->documents()->create([
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_name'],
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);


        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', 'تم رفع المستند بنجاح');
    }

    public function download($companyId, $documentId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        $document = $company->documents()->findOrFail($documentId);

        if (!$document->exists()) {
            return redirect()->back()
                ->with('error', 'الملف غير موجود');
        }

        return Storage::disk('public')->download($document->file_path, $document->document_name);
    }

    public function destroy($companyId, $documentId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!$company->canUploadDocuments()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', 'لا يمكن حذف المستندات في الحالة الحالية');
        }

        $document = $company->documents()->findOrFail($documentId);

        // Can only delete pending or rejected documents
        if (!in_array($document->status, ['pending', 'rejected'])) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف هذا المستند في حالته الحالية');
        }

        // Log activity before deletion

        // Delete the document (will also delete file from storage)
        $document->delete();

        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', 'تم حذف المستند بنجاح');
    }

    public function replace(Request $request, $companyId, $documentId)
    {
        $representative = auth('representative')->user();

        $company = ForeignCompany::where('id', $companyId)
            ->where('representative_id', $representative->id)
            ->firstOrFail();

        if (!$company->canUploadDocuments()) {
            return redirect()->route('representative.foreign-companies.show', $company->id)
                ->with('error', 'لا يمكن استبدال المستندات في الحالة الحالية');
        }

        $document = $company->documents()->findOrFail($documentId);

        // Can only replace rejected documents
        if ($document->status != 'rejected') {
            return redirect()->back()
                ->with('error', 'يمكن فقط استبدال المستندات المرفوضة');
        }

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string',
        ]);

        // Delete old file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Store new file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs(
            'foreign_companies/' . $company->id . '/documents',
            $fileName,
            'public'
        );

        // Update document
        $document->update([
            'document_name' => $validated['document_name'],
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);


        return redirect()->route('representative.foreign-companies.show', $company->id)
            ->with('success', 'تم استبدال المستند بنجاح');
    }
}
