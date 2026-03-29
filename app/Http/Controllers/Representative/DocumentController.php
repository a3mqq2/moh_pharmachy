<?php

namespace App\Http\Controllers\Representative;

use App\Helpers\NotificationHelper;
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

        if ($company->representative_id != $representative->id) {
            abort(403, __('documents.unauthorized_upload'));
        }

        if (!in_array($company->status, ['uploading_documents', 'rejected', 'active', 'approved', 'payment_review'])) {
            return redirect()->route('representative.companies.show', $company)
                ->with('error', __('documents.cannot_upload_in_status'));
        }

        $request->validate([
            'document_type' => 'required|string',
            'custom_name' => 'required_if:document_type,other|nullable|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string|max:1000',
        ], [
            'document_type.required' => __('documents.validation_type_required'),
            'custom_name.required_if' => __('documents.validation_name_required_if_other'),
            'file.required' => __('documents.validation_file_required'),
            'file.max' => __('documents.validation_file_max'),
            'file.mimes' => __('documents.validation_file_mimes'),
        ]);

        $file = $request->file('file');
        $path = $file->store('local-companies/' . $company->id . '/documents', 'public');

        $isActiveCompany = in_array($company->status, ['active', 'approved', 'payment_review']);

        $document = LocalCompanyDocument::create([
            'local_company_id' => $company->id,
            'document_type' => $request->document_type,
            'custom_name' => $request->custom_name,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'uploaded_by' => null,
            'status' => $isActiveCompany ? 'pending' : 'approved',
        ]);

        if (in_array($company->status, ['active', 'approved', 'payment_review'])) {
            NotificationHelper::notifyAdmins(
                'document_updated',
                'local',
                $company->company_name,
                $company->id,
                $representative->name,
                [__('documents.document_label') => $request->document_type]
            );

            session(['active_tab_' . $company->id => 'documents']);
            return redirect()->route('representative.companies.show', $company)
                ->with('success', __('documents.upload_success_pending_review'));
        }

        if ($company->hasAllRequiredDocuments() && $company->status == 'uploading_documents') {
            $company->update(['status' => 'pending']);

            session(['active_tab_' . $company->id => 'documents']);
            return redirect()->route('representative.companies.show', $company)
                ->with('success', __('documents.upload_success_submitted'));
        }

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('documents.upload_success'));
    }

    public function download(LocalCompany $company, LocalCompanyDocument $document)
    {
        $representative = Auth::guard('representative')->user();

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
                ->with('error', __('documents.cannot_update_in_status'));
        }

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string|max:1000',
        ], [
            'file.required' => __('documents.validation_file_required'),
            'file.max' => __('documents.validation_file_max'),
            'file.mimes' => __('documents.validation_file_mimes'),
        ]);

        Storage::disk('public')->delete($document->file_path);

        $file = $request->file('file');
        $path = $file->store('local-companies/' . $company->id . '/documents', 'public');

        $isActiveCompany = in_array($company->status, ['active', 'approved', 'payment_review']);

        $document->update([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'status' => $isActiveCompany ? 'pending' : 'approved',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        if ($isActiveCompany) {
            NotificationHelper::notifyAdmins(
                'document_updated',
                'local',
                $company->company_name,
                $company->id,
                $representative->name,
                [__('documents.document_label') => $document->document_type]
            );
        }

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('documents.update_success_pending_review'));
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
                ->with('error', __('documents.cannot_delete_in_status'));
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        session(['active_tab_' . $company->id => 'documents']);

        return redirect()->route('representative.companies.show', $company)
            ->with('success', __('documents.delete_success'));
    }
}
