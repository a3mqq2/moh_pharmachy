<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnifiedAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Representative\AuthController as RepresentativeAuthController;

/*
|--------------------------------------------------------------------------
| Unified Login Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Route::middleware(['guest:web', 'guest:representative'])->group(function () {
    Route::get('/login', [UnifiedAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UnifiedAuthController::class, 'login'])->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| Representative Routes (Main Portal)
|--------------------------------------------------------------------------
*/

// Representative Guest Routes
Route::middleware('guest:representative')->group(function () {

    Route::get('/verify-login-otp', [RepresentativeAuthController::class, 'showVerifyLoginOtpForm'])->name('verify-login-otp');
    Route::post('/verify-login-otp', [RepresentativeAuthController::class, 'verifyLoginOtp'])->name('verify-login-otp.submit');

    Route::get('/register', [RepresentativeAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RepresentativeAuthController::class, 'register'])->name('register.submit');

    Route::get('/verify-otp', [RepresentativeAuthController::class, 'showVerifyOtpForm'])->name('verify-otp');
    Route::post('/verify-otp', [RepresentativeAuthController::class, 'verifyOtp'])->name('verify-otp.submit');
    Route::post('/resend-otp', [RepresentativeAuthController::class, 'resendOtp'])->name('resend-otp');

    Route::get('/set-password', [RepresentativeAuthController::class, 'showSetPasswordForm'])->name('set-password');
    Route::post('/set-password', [RepresentativeAuthController::class, 'setPassword'])->name('set-password.submit');

    Route::get('/forgot-password', [RepresentativeAuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('/forgot-password', [RepresentativeAuthController::class, 'forgotPassword'])->name('forgot-password.submit');

    Route::get('/verify-password-reset-otp', [RepresentativeAuthController::class, 'showVerifyPasswordResetOtpForm'])->name('verify-password-reset-otp');
    Route::post('/verify-password-reset-otp', [RepresentativeAuthController::class, 'verifyPasswordResetOtp'])->name('verify-password-reset-otp.submit');

    Route::get('/reset-password', [RepresentativeAuthController::class, 'showResetPasswordForm'])->name('reset-password');
    Route::post('/reset-password', [RepresentativeAuthController::class, 'resetPassword'])->name('reset-password.submit');
});

// Representative Authenticated Routes
Route::middleware('auth:representative')->prefix('clea')->name('representative.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Representative\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [UnifiedAuthController::class, 'logout'])->name('logout');

    // Companies Management
    Route::resource('companies', \App\Http\Controllers\Representative\CompanyController::class)->except(['destroy']);
    Route::patch('companies/{company}/save-tab', [\App\Http\Controllers\Representative\CompanyController::class, 'saveTab'])->name('companies.save-tab');
    Route::post('companies/{company}/resubmit', [\App\Http\Controllers\Representative\CompanyController::class, 'resubmit'])->name('companies.resubmit');

    // Company Documents
    Route::post('companies/{company}/documents', [\App\Http\Controllers\Representative\DocumentController::class, 'store'])->name('companies.documents.store');
    Route::get('companies/{company}/documents/{document}/download', [\App\Http\Controllers\Representative\DocumentController::class, 'download'])->name('companies.documents.download');
    Route::put('companies/{company}/documents/{document}', [\App\Http\Controllers\Representative\DocumentController::class, 'update'])->name('companies.documents.update');
    Route::delete('companies/{company}/documents/{document}', [\App\Http\Controllers\Representative\DocumentController::class, 'destroy'])->name('companies.documents.destroy');

    // Invoices & Payments
    Route::get('invoices', [\App\Http\Controllers\Representative\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [\App\Http\Controllers\Representative\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/upload-receipt', [\App\Http\Controllers\Representative\InvoiceController::class, 'uploadReceipt'])->name('invoices.upload-receipt');
    Route::get('invoices/{invoice}/download-receipt', [\App\Http\Controllers\Representative\InvoiceController::class, 'downloadReceipt'])->name('invoices.download-receipt');
    Route::delete('invoices/{invoice}/delete-receipt', [\App\Http\Controllers\Representative\InvoiceController::class, 'deleteReceipt'])->name('invoices.delete-receipt');

    // Profile & Settings
    Route::get('settings', [\App\Http\Controllers\Representative\ProfileController::class, 'settings'])->name('settings');
    Route::patch('settings/update-password', [\App\Http\Controllers\Representative\ProfileController::class, 'updatePassword'])->name('settings.update-password');
    Route::patch('settings/update-profile', [\App\Http\Controllers\Representative\ProfileController::class, 'updateProfile'])->name('settings.update-profile');

    // Foreign Companies Management
    Route::resource('foreign-companies', \App\Http\Controllers\Representative\ForeignCompanyController::class)->except(['destroy']);
    Route::post('foreign-companies/{foreignCompany}/submit-for-review', [\App\Http\Controllers\Representative\ForeignCompanyController::class, 'submitForReview'])->name('foreign-companies.submit-for-review');

    // Foreign Company Documents
    Route::post('foreign-companies/{foreignCompany}/documents', [\App\Http\Controllers\Representative\ForeignCompanyDocumentController::class, 'store'])->name('foreign-companies.documents.store');
    Route::get('foreign-companies/{foreignCompany}/documents/{document}/download', [\App\Http\Controllers\Representative\ForeignCompanyDocumentController::class, 'download'])->name('foreign-companies.documents.download');
    Route::delete('foreign-companies/{foreignCompany}/documents/{document}', [\App\Http\Controllers\Representative\ForeignCompanyDocumentController::class, 'destroy'])->name('foreign-companies.documents.destroy');
    Route::post('foreign-companies/{foreignCompany}/documents/{document}/replace', [\App\Http\Controllers\Representative\ForeignCompanyDocumentController::class, 'replace'])->name('foreign-companies.documents.replace');

    // Foreign Company Invoices
    Route::get('foreign-companies/{foreignCompany}/invoices', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'index'])->name('foreign-companies.invoices.index');
    Route::get('foreign-companies/{foreignCompany}/invoices/{invoice}', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'show'])->name('foreign-companies.invoices.show');
    Route::post('foreign-companies/{foreignCompany}/invoices/{invoice}/upload-receipt', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'uploadReceipt'])->name('foreign-companies.invoices.upload-receipt');
    Route::get('foreign-companies/{foreignCompany}/invoices/{invoice}/download-receipt', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'downloadReceipt'])->name('foreign-companies.invoices.download-receipt');
    Route::delete('foreign-companies/{foreignCompany}/invoices/{invoice}/delete-receipt', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'deleteReceipt'])->name('foreign-companies.invoices.delete-receipt');
    Route::get('foreign-companies/{foreignCompany}/invoices/{invoice}/download', [\App\Http\Controllers\Representative\ForeignCompanyInvoiceController::class, 'downloadInvoice'])->name('foreign-companies.invoices.download');

    // Pharmaceutical Products
    Route::resource('pharmaceutical-products', \App\Http\Controllers\Representative\PharmaceuticalProductController::class);
    Route::get('foreign-companies/{foreignCompany}/products', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'getCompanyProducts'])->name('foreign-companies.products');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/upload-document', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'uploadDocument'])->name('pharmaceutical-products.upload-document');
    Route::delete('pharmaceutical-products/{pharmaceuticalProduct}/documents/{document}', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'deleteDocument'])->name('pharmaceutical-products.delete-document');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/documents/{document}/update', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'updateDocument'])->name('pharmaceutical-products.update-document');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/submit-for-review', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'submitForReview'])->name('pharmaceutical-products.submit-for-review');
    Route::get('pharmaceutical-products/{pharmaceuticalProduct}/edit-details', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'editDetails'])->name('pharmaceutical-products.edit-details');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/update-details', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'updateDetails'])->name('pharmaceutical-products.update-details');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/submit-details', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'submitDetails'])->name('pharmaceutical-products.submit-details');
    Route::post('pharmaceutical-products/{pharmaceuticalProduct}/invoices/{invoice}/upload-receipt', [\App\Http\Controllers\Representative\PharmaceuticalProductController::class, 'uploadReceipt'])->name('pharmaceutical-products.invoices.upload-receipt');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [UnifiedAuthController::class, 'logout'])->name('logout');

        require __DIR__.'/web/admin.php';
    });
});
