<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForeignCompany;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyDocumentController extends Controller
{
    public function download($companyId, $documentId)
    {
        $company = ForeignCompany::findOrFail($companyId);
        $document = $company->documents()->findOrFail($documentId);

        if (!$document->exists()) {
            return redirect()->back()
                ->with('error', __('documents.file_not_found'));
        }

        if (request()->query('view') === '1') {
            return response()->file(Storage::disk('public')->path($document->file_path));
        }

        return Storage::disk('public')->download($document->file_path, $document->document_name);
    }
}
