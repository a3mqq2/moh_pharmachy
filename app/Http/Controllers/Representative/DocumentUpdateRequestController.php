<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\DocumentUpdateRequest;
use App\Models\ForeignCompanyDocument;
use App\Models\LocalCompanyDocument;
use App\Models\PharmaceuticalProductDocument;
use Illuminate\Http\Request;

class DocumentUpdateRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'documentable_type' => 'required|in:local_company_document,foreign_company_document,pharmaceutical_product_document',
            'documentable_id' => 'required|integer',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'reason' => 'nullable|string|max:1000',
        ], [
            'file.required' => __('documents.validation_file_required'),
            'file.max' => __('documents.validation_file_max'),
            'reason.max' => __('documents.validation_reason_max'),
        ]);

        $representative = auth('representative')->user();

        $modelClass = match ($request->documentable_type) {
            'local_company_document' => LocalCompanyDocument::class,
            'foreign_company_document' => ForeignCompanyDocument::class,
            'pharmaceutical_product_document' => PharmaceuticalProductDocument::class,
        };

        $document = $modelClass::findOrFail($request->documentable_id);

        if ($document instanceof LocalCompanyDocument) {
            $company = $document->localCompany;
            if ($company->representative_id !== $representative->id) {
                abort(403);
            }
        } elseif ($document instanceof ForeignCompanyDocument) {
            $company = $document->foreignCompany;
            if ($company->local_company_id) {
                $localCompany = $company->localCompany;
                if ($localCompany->representative_id !== $representative->id) {
                    abort(403);
                }
            }
        } elseif ($document instanceof PharmaceuticalProductDocument) {
            $product = $document->pharmaceuticalProduct;
            $foreignCompany = $product->foreignCompany;
            if ($foreignCompany && $foreignCompany->local_company_id) {
                $localCompany = $foreignCompany->localCompany;
                if ($localCompany->representative_id !== $representative->id) {
                    abort(403);
                }
            }
        }

        $existingPending = DocumentUpdateRequest::where('documentable_type', $modelClass)
            ->where('documentable_id', $document->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('error', __('documents.pending_request_exists'));
        }

        $file = $request->file('file');
        $path = $file->store('document-update-requests/' . $request->documentable_type, 'public');

        DocumentUpdateRequest::create([
            'documentable_type' => $modelClass,
            'documentable_id' => $document->id,
            'representative_id' => $representative->id,
            'new_file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_extension' => $file->getClientOriginalExtension(),
            'reason' => $request->reason,
        ]);

        return back()->with('success', __('documents.update_request_sent'));
    }
}
