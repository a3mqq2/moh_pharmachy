<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDocument;
use App\Models\Department;
use App\Models\ForeignCompanyDocument;
use App\Models\LocalCompanyDocument;
use App\Models\DocumentUpdateRequest;
use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductDocument;
use App\Models\SharedFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class DocumentCenterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_admin_documents', only: ['adminDocuments', 'storeAdminDocument', 'downloadAdminDocument', 'destroyAdminDocument']),
            new Middleware('permission:view_company_archive', only: ['companyArchive']),
            new Middleware('permission:view_pharmaceutical_products', only: ['productArchive']),
            new Middleware('permission:view_company_archive', only: ['updateRequests', 'approveUpdateRequest', 'rejectUpdateRequest']),
            new Middleware('permission:manage_shared_files', only: ['sharedFiles', 'storeSharedFile', 'downloadSharedFile', 'destroySharedFile']),
        ];
    }

    public function adminDocuments(Request $request)
    {
        $query = AdminDocument::with(['uploader', 'department', 'authorizedUsers'])->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%')
                  ->orWhere('original_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('file_type')) {
            $extensions = match($request->file_type) {
                'pdf' => ['pdf'],
                'word' => ['doc', 'docx'],
                'excel' => ['xls', 'xlsx'],
                'image' => ['jpg', 'jpeg', 'png'],
                'archive' => ['zip', 'rar'],
                'presentation' => ['ppt', 'pptx'],
                default => [],
            };
            if ($extensions) {
                $query->whereIn('file_extension', $extensions);
            }
        }

        if ($request->filled('uploaded_by')) {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $documents = $query->paginate(15);
        $categories = AdminDocument::categories();
        $departments = Department::where('is_active', true)->orderBy('sort_order')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $uploaders = User::whereIn('id', AdminDocument::distinct()->pluck('uploaded_by'))->orderBy('name')->get();

        $categoryCounts = ['all' => AdminDocument::count()];
        foreach ($categories as $key => $label) {
            $categoryCounts[$key] = AdminDocument::where('category', $key)->count();
        }

        return view('admin.document-center.admin-documents', compact('documents', 'categories', 'departments', 'users', 'uploaders', 'categoryCounts'));
    }

    public function storeAdminDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(AdminDocument::categories())),
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar',
            'notes' => 'nullable|string|max:1000',
            'visibility' => 'required|string|in:all,department,specific',
            'department_id' => 'required_if:visibility,department|nullable|exists:departments,id',
            'authorized_users' => 'required_if:visibility,specific|nullable|array',
            'authorized_users.*' => 'exists:users,id',
        ], [
            'title.required' => __('documents.validation_title_required'),
            'category.required' => __('documents.validation_category_required'),
            'file.required' => __('documents.validation_file_required'),
            'file.max' => __('documents.validation_file_max_20'),
            'visibility.required' => __('documents.validation_visibility_required'),
            'department_id.required_if' => __('documents.validation_department_required'),
            'authorized_users.required_if' => __('documents.validation_users_required'),
        ]);

        $file = $request->file('file');
        $path = $file->store('admin-documents/' . $request->category, 'public');

        $document = AdminDocument::create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
            'visibility' => $request->visibility,
            'department_id' => $request->visibility === 'department' ? $request->department_id : null,
        ]);

        if ($request->visibility === 'specific' && $request->filled('authorized_users')) {
            $document->authorizedUsers()->attach($request->authorized_users);
        }

        return redirect()->route('admin.document-center.admin-documents')
            ->with('success', __('documents.upload_success'));
    }

    public function downloadAdminDocument(AdminDocument $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyAdminDocument(AdminDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.document-center.admin-documents')
            ->with('success', __('documents.delete_success'));
    }

    public function companyArchive(Request $request)
    {
        $companyType = $request->get('company_type', 'all');
        $search = $request->get('search');
        $docStatus = $request->get('doc_status', 'all');

        $companies = collect();

        if ($companyType !== 'foreign') {
            $localQuery = \App\Models\LocalCompany::with('documents')
                ->when($search, fn($q) => $q->where('company_name', 'like', "%{$search}%"));

            $localCompanies = $localQuery->get()->map(function ($company) {
                $docs = $company->documents;
                $requiredTypes = LocalCompanyDocument::requiredDocumentTypes();
                $uploadedTypes = $docs->pluck('document_type')->unique()->toArray();
                $missingCount = count(array_diff($requiredTypes, $uploadedTypes));
                $totalRequired = count($requiredTypes);
                $uploadedCount = $totalRequired - $missingCount;

                return [
                    'id' => $company->id,
                    'name' => $company->company_name,
                    'type' => 'local',
                    'type_label' => __('documents.local'),
                    'status' => $company->status,
                    'route' => route('admin.local-companies.show', $company->id),
                    'total_docs' => $docs->count(),
                    'required_count' => $totalRequired,
                    'uploaded_required' => $uploadedCount,
                    'missing_count' => $missingCount,
                    'completion' => $totalRequired > 0 ? round(($uploadedCount / $totalRequired) * 100) : 0,
                    'pending_docs' => $docs->where('status', 'pending')->count(),
                    'approved_docs' => $docs->where('status', 'approved')->count(),
                    'is_complete' => $missingCount === 0,
                    'documents' => $docs->map(fn($doc) => [
                        'id' => $doc->id,
                        'type_name' => $doc->document_type_name,
                        'original_name' => $doc->original_name,
                        'file_size' => $doc->file_size,
                        'status' => $doc->status,
                        'created_at' => $doc->created_at,
                        'file_url' => Storage::url($doc->file_path),
                        'download_route' => route('admin.local-companies.documents.download', [$company->id, $doc->id]),
                    ])->sortByDesc('created_at')->values(),
                ];
            });
            $companies = $companies->concat($localCompanies);
        }

        if ($companyType !== 'local') {
            $foreignQuery = \App\Models\ForeignCompany::with('documents')
                ->when($search, fn($q) => $q->where('company_name', 'like', "%{$search}%"));

            $foreignCompanies = $foreignQuery->get()->map(function ($company) {
                $docs = $company->documents;
                $requiredTypes = [
                    'official_registration_request',
                    'agency_agreement',
                    'registration_forms',
                    'gmp_certificate',
                    'product_specifications',
                    'stability_studies',
                    'samples_analysis',
                ];
                $uploadedTypes = $docs->pluck('document_type')->unique()->toArray();
                $missingCount = count(array_diff($requiredTypes, $uploadedTypes));
                $totalRequired = count($requiredTypes);
                $uploadedCount = $totalRequired - $missingCount;

                return [
                    'id' => $company->id,
                    'name' => $company->company_name,
                    'type' => 'foreign',
                    'type_label' => __('documents.foreign'),
                    'status' => $company->status,
                    'route' => route('admin.foreign-companies.show', $company->id),
                    'total_docs' => $docs->count(),
                    'required_count' => $totalRequired,
                    'uploaded_required' => $uploadedCount,
                    'missing_count' => $missingCount,
                    'completion' => $totalRequired > 0 ? round(($uploadedCount / $totalRequired) * 100) : 0,
                    'pending_docs' => $docs->where('status', 'pending')->count(),
                    'approved_docs' => $docs->where('status', 'approved')->count(),
                    'is_complete' => $missingCount === 0,
                    'documents' => $docs->map(fn($doc) => [
                        'id' => $doc->id,
                        'type_name' => $doc->document_type_name,
                        'original_name' => $doc->document_name,
                        'file_size' => $doc->file_size,
                        'status' => $doc->status,
                        'created_at' => $doc->created_at,
                        'file_url' => Storage::url($doc->file_path),
                        'download_route' => route('admin.foreign-companies.documents.download', [$company->id, $doc->id]),
                    ])->sortByDesc('created_at')->values(),
                ];
            });
            $companies = $companies->concat($foreignCompanies);
        }

        if ($docStatus === 'complete') {
            $companies = $companies->where('is_complete', true);
        } elseif ($docStatus === 'incomplete') {
            $companies = $companies->where('is_complete', false);
        } elseif ($docStatus === 'has_pending') {
            $companies = $companies->where('pending_docs', '>', 0);
        }

        $companies = $companies->sortBy('completion')->values();

        $stats = [
            'total_companies' => $companies->count(),
            'complete' => $companies->where('is_complete', true)->count(),
            'incomplete' => $companies->where('is_complete', false)->count(),
            'has_pending' => $companies->where('pending_docs', '>', 0)->count(),
            'total_docs' => $companies->sum('total_docs'),
        ];

        return view('admin.document-center.company-archive', compact('companies', 'stats', 'companyType', 'docStatus'));
    }

    public function productArchive(Request $request)
    {
        $search = $request->get('search');
        $docStatus = $request->get('doc_status', 'all');
        $productStatus = $request->get('product_status', 'all');

        $query = PharmaceuticalProduct::with(['documents', 'foreignCompany'])
            ->when($search, fn($q) => $q->where('trade_name', 'like', "%{$search}%")
                ->orWhere('scientific_name', 'like', "%{$search}%"))
            ->when($productStatus !== 'all', fn($q) => $q->where('status', $productStatus));

        $products = $query->get()->map(function ($product) {
            $docs = $product->documents;
            $requiredTypes = PharmaceuticalProductDocument::getRequiredDocumentTypes();
            $uploadedTypes = $docs->pluck('document_type')->unique()->toArray();
            $missingCount = count(array_diff($requiredTypes, $uploadedTypes));
            $totalRequired = count($requiredTypes);
            $uploadedCount = $totalRequired - $missingCount;

            return [
                'id' => $product->id,
                'trade_name' => $product->trade_name,
                'scientific_name' => $product->scientific_name,
                'status' => $product->status,
                'company_name' => $product->foreignCompany->company_name ?? '-',
                'route' => route('admin.pharmaceutical-products.show', $product->id),
                'total_docs' => $docs->count(),
                'required_count' => $totalRequired,
                'uploaded_required' => $uploadedCount,
                'missing_count' => $missingCount,
                'completion' => $totalRequired > 0 ? round(($uploadedCount / $totalRequired) * 100) : 0,
                'is_complete' => $missingCount === 0,
                'documents' => $docs->map(fn($doc) => [
                    'id' => $doc->id,
                    'type_name' => $doc->document_type_name,
                    'original_name' => $doc->original_name,
                    'file_size' => $doc->file_size,
                    'created_at' => $doc->created_at,
                    'file_url' => Storage::url($doc->file_path),
                ])->sortByDesc('created_at')->values(),
            ];
        });

        if ($docStatus === 'complete') {
            $products = $products->where('is_complete', true);
        } elseif ($docStatus === 'incomplete') {
            $products = $products->where('is_complete', false);
        }

        $products = $products->sortBy('completion')->values();

        $stats = [
            'total_products' => $products->count(),
            'complete' => $products->where('is_complete', true)->count(),
            'incomplete' => $products->where('is_complete', false)->count(),
            'total_docs' => $products->sum('total_docs'),
        ];

        return view('admin.document-center.product-archive', compact('products', 'stats', 'docStatus', 'productStatus'));
    }

    public function updateRequests(Request $request)
    {
        $status = $request->get('status', 'pending');
        $docType = $request->get('doc_type', 'all');
        $search = $request->get('search');

        $query = DocumentUpdateRequest::with(['documentable', 'representative', 'reviewer'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($docType === 'local', fn($q) => $q->where('documentable_type', LocalCompanyDocument::class))
            ->when($docType === 'foreign', fn($q) => $q->where('documentable_type', ForeignCompanyDocument::class))
            ->when($docType === 'product', fn($q) => $q->where('documentable_type', PharmaceuticalProductDocument::class))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('original_name', 'like', "%{$search}%")
                       ->orWhere('reason', 'like', "%{$search}%")
                       ->orWhereHas('representative', fn($r) => $r->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest();

        $requests = $query->paginate(15);

        $pendingCount = DocumentUpdateRequest::where('status', 'pending')->count();

        return view('admin.document-center.update-requests', compact('requests', 'status', 'pendingCount', 'docType'));
    }

    public function approveUpdateRequest(DocumentUpdateRequest $documentUpdateRequest)
    {
        if ($documentUpdateRequest->status !== 'pending') {
            return back()->with('error', __('documents.request_already_processed'));
        }

        $documentUpdateRequest->approve(auth()->id());

        return back()->with('success', __('documents.approve_success'));
    }

    public function rejectUpdateRequest(Request $request, DocumentUpdateRequest $documentUpdateRequest)
    {
        if ($documentUpdateRequest->status !== 'pending') {
            return back()->with('error', __('documents.request_already_processed'));
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $documentUpdateRequest->reject(auth()->id(), $request->rejection_reason);

        return back()->with('success', __('documents.reject_success'));
    }

    public function sharedFiles(Request $request)
    {
        $view = $request->get('view', 'sent');

        if ($view === 'received') {
            $files = SharedFile::whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
                ->with(['sharer', 'users'])
                ->latest()
                ->paginate(15);
        } else {
            $files = SharedFile::where('shared_by', auth()->id())
                ->with(['sharer', 'users'])
                ->latest()
                ->paginate(15);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.document-center.shared-files', compact('files', 'users', 'view'));
    }

    public function storeSharedFile(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ], [
            'title.required' => __('documents.validation_file_title_required'),
            'file.required' => __('documents.validation_file_required'),
            'users.required' => __('documents.validation_users_required'),
        ]);

        $file = $request->file('file');
        $path = $file->store('shared-files', 'public');

        $sharedFile = SharedFile::create([
            'title' => $request->title,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'shared_by' => auth()->id(),
        ]);

        $sharedFile->users()->attach($request->users);

        return redirect()->route('admin.document-center.shared-files')
            ->with('success', __('documents.share_success'));
    }

    public function downloadSharedFile(SharedFile $sharedFile)
    {
        $sharedFile->users()->where('user_id', auth()->id())->update(['seen_at' => now()]);

        return Storage::disk('public')->download($sharedFile->file_path, $sharedFile->original_name);
    }

    public function destroySharedFile(SharedFile $sharedFile)
    {
        if ($sharedFile->shared_by !== auth()->id()) {
            abort(403);
        }

        Storage::disk('public')->delete($sharedFile->file_path);
        $sharedFile->delete();

        return redirect()->route('admin.document-center.shared-files')
            ->with('success', __('documents.delete_file_success'));
    }
}
