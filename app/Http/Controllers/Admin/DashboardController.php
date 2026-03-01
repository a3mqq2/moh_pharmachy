<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LocalCompany;
use App\Models\ForeignCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\CompanyRepresentative;
use App\Models\PharmaceuticalProductInvoice;
use App\Models\PharmaceuticalProduct;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $localCompaniesTotal = LocalCompany::count();
        $localCompaniesActive = LocalCompany::where('status', 'active')->count();
        $localCompaniesPending = LocalCompany::whereIn('status', ['pending', 'uploading_documents'])->count();
        $localCompaniesRejected = LocalCompany::where('status', 'rejected')->count();
        $localCompaniesApproved = LocalCompany::where('status', 'approved')->count();

        $foreignCompaniesTotal = ForeignCompany::count();
        $foreignCompaniesActive = ForeignCompany::where('status', 'active')->count();

        $representativesTotal = CompanyRepresentative::count();
        $representativesActive = CompanyRepresentative::where('is_verified', 1)->count();

        $invoicesTotal = LocalCompanyInvoice::count();
        $invoicesPaid = LocalCompanyInvoice::where('status', 'paid')->count();
        $invoicesUnpaid = LocalCompanyInvoice::where('status', 'unpaid')->count();
        $totalRevenue = LocalCompanyInvoice::where('status', 'paid')->sum('amount');

        $recentLocalCompanies = LocalCompany::with('representative')
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = LocalCompanyInvoice::with('localCompany')
            ->latest()
            ->take(5)
            ->get();

        $pharmaceuticalInvoicesNeedReceipt = PharmaceuticalProductInvoice::with(['pharmaceuticalProduct.representative'])
            ->where('status', 'unpaid')
            ->latest()
            ->take(10)
            ->get();

        $recentPharmaceuticalProducts = PharmaceuticalProduct::with(['foreignCompany', 'representative'])
            ->latest()
            ->take(5)
            ->get();

        $pharmaceuticalProductsNeedApproval = PharmaceuticalProduct::with(['foreignCompany', 'representative'])
            ->whereIn('status', ['pending_review', 'pending_final_approval'])
            ->latest()
            ->take(10)
            ->get();

        $recentForeignCompanies = ForeignCompany::with('localCompany')
            ->latest()
            ->take(5)
            ->get();

        $monthlyRevenue = LocalCompanyInvoice::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as local_revenue')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyPharmaceuticalRevenue = PharmaceuticalProductInvoice::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as pharma_revenue')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $pharmaceuticalProductsTotal = PharmaceuticalProduct::count();
        $pharmaceuticalProductsActive = PharmaceuticalProduct::where('status', 'active')->count();
        $pharmaceuticalProductsPendingReview = PharmaceuticalProduct::where('status', 'pending_review')->count();
        $pharmaceuticalProductsPreliminaryApproved = PharmaceuticalProduct::where('status', 'preliminary_approved')->count();
        $pharmaceuticalProductsPendingFinalApproval = PharmaceuticalProduct::where('status', 'pending_final_approval')->count();
        $pharmaceuticalProductsPendingPayment = PharmaceuticalProduct::where('status', 'pending_payment')->count();
        $pharmaceuticalProductsPaymentReview = PharmaceuticalProduct::where('status', 'payment_review')->count();
        $pharmaceuticalProductsRejected = PharmaceuticalProduct::where('status', 'rejected')->count();

        $pharmaceuticalInvoicesTotal = PharmaceuticalProductInvoice::count();
        $pharmaceuticalInvoicesPaid = PharmaceuticalProductInvoice::where('status', 'paid')->count();
        $pharmaceuticalInvoicesUnpaid = PharmaceuticalProductInvoice::where('status', 'unpaid')->count();
        $pharmaceuticalRevenue = PharmaceuticalProductInvoice::where('status', 'paid')->sum('amount');

        $monthlyRegistrations = LocalCompany::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyPharmaceuticalRegistrations = PharmaceuticalProduct::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $stats = [
            'local_companies_total' => $localCompaniesTotal,
            'local_companies_active' => $localCompaniesActive,
            'local_companies_pending' => $localCompaniesPending,
            'local_companies_rejected' => $localCompaniesRejected,
            'local_companies_approved' => $localCompaniesApproved,
            'foreign_companies_total' => $foreignCompaniesTotal,
            'foreign_companies_active' => $foreignCompaniesActive,
            'representatives_total' => $representativesTotal,
            'representatives_active' => $representativesActive,
            'invoices_total' => $invoicesTotal,
            'invoices_paid' => $invoicesPaid,
            'invoices_unpaid' => $invoicesUnpaid,
            'total_revenue' => $totalRevenue,
            'pharmaceutical_products_total' => $pharmaceuticalProductsTotal,
            'pharmaceutical_products_active' => $pharmaceuticalProductsActive,
            'pharmaceutical_products_pending_review' => $pharmaceuticalProductsPendingReview,
            'pharmaceutical_products_preliminary_approved' => $pharmaceuticalProductsPreliminaryApproved,
            'pharmaceutical_products_pending_final_approval' => $pharmaceuticalProductsPendingFinalApproval,
            'pharmaceutical_products_pending_payment' => $pharmaceuticalProductsPendingPayment,
            'pharmaceutical_products_payment_review' => $pharmaceuticalProductsPaymentReview,
            'pharmaceutical_products_rejected' => $pharmaceuticalProductsRejected,
            'pharmaceutical_invoices_total' => $pharmaceuticalInvoicesTotal,
            'pharmaceutical_invoices_paid' => $pharmaceuticalInvoicesPaid,
            'pharmaceutical_invoices_unpaid' => $pharmaceuticalInvoicesUnpaid,
            'pharmaceutical_revenue' => $pharmaceuticalRevenue,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentLocalCompanies',
            'recentInvoices',
            'pharmaceuticalInvoicesNeedReceipt',
            'monthlyRegistrations',
            'monthlyPharmaceuticalRegistrations',
            'recentPharmaceuticalProducts',
            'pharmaceuticalProductsNeedApproval',
            'recentForeignCompanies',
            'monthlyRevenue',
            'monthlyPharmaceuticalRevenue'
        ));
    }
}
