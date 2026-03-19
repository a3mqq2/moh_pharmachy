<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocalCompanyController;
use App\Http\Controllers\Admin\LocalCompanyDocumentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\LocalCompanyInvoiceController;
use App\Http\Controllers\Admin\ForeignCompanyController;
use App\Http\Controllers\Admin\ForeignCompanyDocumentController;
use App\Http\Controllers\Admin\ForeignCompanyInvoiceController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\CompanyRepresentativeController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('users', UserController::class);
Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle');
Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk');

Route::resource('departments', DepartmentController::class)->except(['show']);
Route::patch('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
Route::get('departments/{department}/members', [DepartmentController::class, 'members'])->name('departments.members');
Route::post('departments/{department}/assign-members', [DepartmentController::class, 'assignMembers'])->name('departments.assign-members');
Route::delete('departments/{department}/members/{user}', [DepartmentController::class, 'removeMember'])->name('departments.remove-member');

Route::get('local-companies/print', [LocalCompanyController::class, 'print'])->name('local-companies.print');
Route::resource('local-companies', LocalCompanyController::class);
Route::post('local-companies/{localCompany}/approve', [LocalCompanyController::class, 'approve'])->name('local-companies.approve');
Route::post('local-companies/{localCompany}/activate', [LocalCompanyController::class, 'activate'])->name('local-companies.activate');
Route::post('local-companies/{localCompany}/reject', [LocalCompanyController::class, 'reject'])->name('local-companies.reject');
Route::post('local-companies/{localCompany}/restore-pending', [LocalCompanyController::class, 'restorePending'])->name('local-companies.restore-pending');
Route::get('local-companies/{localCompany}/certificate', [LocalCompanyController::class, 'certificate'])->name('local-companies.certificate');
Route::post('local-companies/{localCompany}/suspend', [LocalCompanyController::class, 'suspend'])->name('local-companies.suspend');
Route::post('local-companies/{localCompany}/unsuspend', [LocalCompanyController::class, 'unsuspend'])->name('local-companies.unsuspend');
Route::post('local-companies/{localCompany}/request-renewal', [LocalCompanyController::class, 'requestRenewal'])->name('local-companies.request-renewal');

Route::post('local-companies/{localCompany}/documents', [LocalCompanyDocumentController::class, 'store'])->name('local-companies.documents.store');
Route::get('local-companies/{localCompany}/documents/{localCompanyDocument}/download', [LocalCompanyDocumentController::class, 'download'])->name('local-companies.documents.download');
Route::delete('local-companies/{localCompany}/documents/{localCompanyDocument}', [LocalCompanyDocumentController::class, 'destroy'])->name('local-companies.documents.destroy');

Route::post('local-companies/{localCompany}/invoices', [LocalCompanyInvoiceController::class, 'store'])->name('local-companies.invoices.store');
Route::put('local-companies/{localCompany}/invoices/{invoice}', [LocalCompanyInvoiceController::class, 'update'])->name('local-companies.invoices.update');
Route::delete('local-companies/{localCompany}/invoices/{invoice}', [LocalCompanyInvoiceController::class, 'destroy'])->name('local-companies.invoices.destroy');
Route::post('local-companies/{localCompany}/invoices/{invoice}/mark-paid', [LocalCompanyInvoiceController::class, 'markAsPaid'])->name('local-companies.invoices.mark-paid');
Route::post('local-companies/{localCompany}/invoices/{invoice}/mark-unpaid', [LocalCompanyInvoiceController::class, 'markAsUnpaid'])->name('local-companies.invoices.mark-unpaid');
Route::post('local-companies/{localCompany}/invoices/{invoice}/upload-receipt', [LocalCompanyInvoiceController::class, 'uploadReceipt'])->name('local-companies.invoices.upload-receipt');
Route::get('local-companies/{localCompany}/invoices/{invoice}/download-receipt', [LocalCompanyInvoiceController::class, 'downloadReceipt'])->name('local-companies.invoices.download-receipt');
Route::post('local-companies/{localCompany}/invoices/{invoice}/approve-receipt', [LocalCompanyInvoiceController::class, 'approveReceipt'])->name('local-companies.invoices.approve-receipt');
Route::post('local-companies/{localCompany}/invoices/{invoice}/reject-receipt', [LocalCompanyInvoiceController::class, 'rejectReceipt'])->name('local-companies.invoices.reject-receipt');

// Foreign Companies Routes
Route::get('foreign-companies/print', [ForeignCompanyController::class, 'print'])->name('foreign-companies.print');
Route::resource('foreign-companies', ForeignCompanyController::class)->only(['index', 'create', 'store', 'show']);
Route::get('foreign-companies/{foreignCompany}/certificate', [ForeignCompanyController::class, 'certificate'])->name('foreign-companies.certificate');
Route::post('foreign-companies/{foreignCompany}/approve', [ForeignCompanyController::class, 'approve'])->name('foreign-companies.approve');
Route::post('foreign-companies/{foreignCompany}/reject', [ForeignCompanyController::class, 'reject'])->name('foreign-companies.reject');
Route::post('foreign-companies/{foreignCompany}/restore-pending', [ForeignCompanyController::class, 'restorePending'])->name('foreign-companies.restore-pending');
Route::post('foreign-companies/{foreignCompany}/activate', [ForeignCompanyController::class, 'activate'])->name('foreign-companies.activate');
Route::post('foreign-companies/{foreignCompany}/suspend', [ForeignCompanyController::class, 'suspend'])->name('foreign-companies.suspend');
Route::post('foreign-companies/{foreignCompany}/unsuspend', [ForeignCompanyController::class, 'unsuspend'])->name('foreign-companies.unsuspend');
Route::post('foreign-companies/{foreignCompany}/request-renewal', [ForeignCompanyController::class, 'requestRenewal'])->name('foreign-companies.request-renewal');
Route::post('foreign-companies/{foreignCompany}/cgmp-upload', [ForeignCompanyController::class, 'uploadCgmp'])->name('foreign-companies.cgmp-upload');
Route::get('foreign-companies/{foreignCompany}/cgmp-download', [ForeignCompanyController::class, 'downloadCgmp'])->name('foreign-companies.cgmp-download');
Route::delete('foreign-companies/{foreignCompany}/cgmp-delete', [ForeignCompanyController::class, 'deleteCgmp'])->name('foreign-companies.cgmp-delete');

// Foreign Company Documents Routes
Route::get('foreign-companies/{foreignCompany}/documents/{document}/download', [ForeignCompanyDocumentController::class, 'download'])->name('foreign-companies.documents.download');

// Foreign Company Invoices Routes
Route::get('foreign-company-invoices', [ForeignCompanyInvoiceController::class, 'index'])->name('foreign-company-invoices.index');
Route::get('foreign-company-invoices/{invoice}', [ForeignCompanyInvoiceController::class, 'show'])->name('foreign-company-invoices.show');
Route::post('foreign-companies/{foreignCompany}/invoices', [ForeignCompanyInvoiceController::class, 'store'])->name('foreign-companies.invoices.store');
Route::delete('foreign-companies/{foreignCompany}/invoices/{invoice}', [ForeignCompanyInvoiceController::class, 'destroy'])->name('foreign-companies.invoices.destroy');
Route::get('foreign-company-invoices/{invoice}/edit', [ForeignCompanyInvoiceController::class, 'edit'])->name('foreign-company-invoices.edit');
Route::put('foreign-company-invoices/{invoice}', [ForeignCompanyInvoiceController::class, 'update'])->name('foreign-company-invoices.update');
Route::post('foreign-companies/{foreignCompany}/invoices/{invoice}/approve-receipt', [ForeignCompanyInvoiceController::class, 'approveReceipt'])->name('foreign-companies.invoices.approve-receipt');
Route::post('foreign-companies/{foreignCompany}/invoices/{invoice}/reject-receipt', [ForeignCompanyInvoiceController::class, 'rejectReceipt'])->name('foreign-companies.invoices.reject-receipt');
Route::get('foreign-companies/{foreignCompany}/invoices/{invoice}/download-receipt', [ForeignCompanyInvoiceController::class, 'downloadReceipt'])->name('foreign-companies.invoices.download-receipt');
Route::get('foreign-company-invoices/{invoice}/download', [ForeignCompanyInvoiceController::class, 'downloadInvoice'])->name('foreign-company-invoices.download');
Route::post('foreign-company-invoices/{invoice}/cancel', [ForeignCompanyInvoiceController::class, 'cancel'])->name('foreign-company-invoices.cancel');

// Settings & Notifications Routes
Route::get('app-settings', [SettingsController::class, 'index'])->name('app-settings.index');
Route::put('app-settings', [SettingsController::class, 'update'])->name('app-settings.update');
Route::get('notifications', [SettingsController::class, 'notifications'])->name('notifications.index');
Route::post('notifications/{id}/mark-as-read', [SettingsController::class, 'markAsRead'])->name('notifications.mark-as-read');
Route::post('notifications/mark-all-as-read', [SettingsController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
Route::delete('notifications/{id}', [SettingsController::class, 'destroy'])->name('notifications.destroy');
Route::post('notifications/delete-all', [SettingsController::class, 'deleteAll'])->name('notifications.delete-all');

// Invoices Routes
Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');

// Pharmaceutical Products Routes
Route::get('pharmaceutical-products', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'index'])->name('pharmaceutical-products.index');
Route::get('pharmaceutical-products/{product}', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'show'])->name('pharmaceutical-products.show');
Route::get('pharmaceutical-products/{product}/certificate', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'printCertificate'])->name('pharmaceutical-products.certificate');
Route::post('pharmaceutical-products/{product}/approve', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'approve'])->name('pharmaceutical-products.approve');
Route::post('pharmaceutical-products/{product}/final-approve', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'finalApprove'])->name('pharmaceutical-products.final-approve');
Route::post('pharmaceutical-products/{product}/reject', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'reject'])->name('pharmaceutical-products.reject');
Route::post('pharmaceutical-products/{product}/invoices/{invoice}/approve-receipt', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'approveReceipt'])->name('pharmaceutical-products.invoices.approve-receipt');
Route::post('pharmaceutical-products/{product}/invoices/{invoice}/reject-receipt', [\App\Http\Controllers\Admin\PharmaceuticalProductController::class, 'rejectReceipt'])->name('pharmaceutical-products.invoices.reject-receipt');

// Announcements Routes
Route::get('announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('announcements/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('announcements.create');
Route::post('announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('announcements.store');
Route::get('announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'show'])->name('announcements.show');
Route::delete('announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
Route::post('announcements/{announcement}/resend', [\App\Http\Controllers\Admin\AnnouncementController::class, 'resend'])->name('announcements.resend');

// Document Center Routes
Route::get('document-center/admin-documents', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'adminDocuments'])->name('document-center.admin-documents');
Route::post('document-center/admin-documents', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'storeAdminDocument'])->name('document-center.admin-documents.store');
Route::get('document-center/admin-documents/{document}/download', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'downloadAdminDocument'])->name('document-center.admin-documents.download');
Route::delete('document-center/admin-documents/{document}', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'destroyAdminDocument'])->name('document-center.admin-documents.destroy');
Route::get('document-center/company-archive', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'companyArchive'])->name('document-center.company-archive');
Route::get('document-center/product-archive', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'productArchive'])->name('document-center.product-archive');
Route::get('document-center/update-requests', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'updateRequests'])->name('document-center.update-requests');
Route::post('document-center/update-requests/{documentUpdateRequest}/approve', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'approveUpdateRequest'])->name('document-center.update-requests.approve');
Route::post('document-center/update-requests/{documentUpdateRequest}/reject', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'rejectUpdateRequest'])->name('document-center.update-requests.reject');
Route::get('document-center/shared-files', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'sharedFiles'])->name('document-center.shared-files');
Route::post('document-center/shared-files', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'storeSharedFile'])->name('document-center.shared-files.store');
Route::get('document-center/shared-files/{sharedFile}/download', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'downloadSharedFile'])->name('document-center.shared-files.download');
Route::delete('document-center/shared-files/{sharedFile}', [\App\Http\Controllers\Admin\DocumentCenterController::class, 'destroySharedFile'])->name('document-center.shared-files.destroy');

Route::get('company-representatives', [CompanyRepresentativeController::class, 'index'])->name('company-representatives.index');
Route::get('company-representatives/{representative}', [CompanyRepresentativeController::class, 'show'])->name('company-representatives.show');

Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
Route::get('reports/local-companies', [\App\Http\Controllers\Admin\ReportsController::class, 'localCompanies'])->name('reports.local-companies');
Route::get('reports/foreign-companies', [\App\Http\Controllers\Admin\ReportsController::class, 'foreignCompanies'])->name('reports.foreign-companies');
Route::get('reports/pharmaceutical-products', [\App\Http\Controllers\Admin\ReportsController::class, 'pharmaceuticalProducts'])->name('reports.pharmaceutical-products');
Route::get('reports/invoices', [\App\Http\Controllers\Admin\ReportsController::class, 'invoices'])->name('reports.invoices');
