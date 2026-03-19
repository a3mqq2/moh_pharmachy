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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $stats = Cache::remember('dashboard_stats', 300, function () use ($today, $weekStart, $monthStart) {
            $localStats = LocalCompany::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status IN ('pending', 'uploading_documents') THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month
            ", [$today, $weekStart, $monthStart])->first();

            $foreignStats = ForeignCompany::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'uploading_documents' THEN 1 END) as uploading_documents,
                COUNT(CASE WHEN status = 'pending_payment' THEN 1 END) as pending_payment,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired,
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month
            ", [$today, $weekStart, $monthStart])->first();

            $pharmaStats = PharmaceuticalProduct::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'pending_review' THEN 1 END) as pending_review,
                COUNT(CASE WHEN status = 'preliminary_approved' THEN 1 END) as preliminary_approved,
                COUNT(CASE WHEN status = 'pending_final_approval' THEN 1 END) as pending_final_approval,
                COUNT(CASE WHEN status = 'pending_payment' THEN 1 END) as pending_payment,
                COUNT(CASE WHEN status = 'payment_review' THEN 1 END) as payment_review,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month
            ", [$today, $weekStart, $monthStart])->first();

            $repStats = CompanyRepresentative::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN is_verified = 1 THEN 1 END) as active
            ")->first();

            $localRevenue = LocalCompanyInvoice::selectRaw("
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total,
                SUM(CASE WHEN status = 'paid' AND DATE(updated_at) = ? THEN amount ELSE 0 END) as today,
                SUM(CASE WHEN status = 'paid' AND updated_at >= ? THEN amount ELSE 0 END) as week,
                SUM(CASE WHEN status = 'paid' AND updated_at >= ? THEN amount ELSE 0 END) as month,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_count
            ", [$today, $weekStart, $monthStart])->first();

            $pharmaRevenue = PharmaceuticalProductInvoice::selectRaw("
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total,
                SUM(CASE WHEN status = 'paid' AND DATE(updated_at) = ? THEN amount ELSE 0 END) as today,
                SUM(CASE WHEN status = 'paid' AND updated_at >= ? THEN amount ELSE 0 END) as week,
                SUM(CASE WHEN status = 'paid' AND updated_at >= ? THEN amount ELSE 0 END) as month,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_count
            ", [$today, $weekStart, $monthStart])->first();

            return [
                'local_companies' => [
                    'total' => $localStats->total,
                    'active' => $localStats->active,
                    'pending' => $localStats->pending,
                    'rejected' => $localStats->rejected,
                    'approved' => $localStats->approved,
                    'today' => $localStats->today,
                    'week' => $localStats->week,
                    'month' => $localStats->month,
                ],
                'foreign_companies' => [
                    'total' => $foreignStats->total,
                    'active' => $foreignStats->active,
                    'pending' => $foreignStats->pending,
                    'uploading_documents' => $foreignStats->uploading_documents,
                    'pending_payment' => $foreignStats->pending_payment,
                    'approved' => $foreignStats->approved,
                    'rejected' => $foreignStats->rejected,
                    'suspended' => $foreignStats->suspended,
                    'expired' => $foreignStats->expired,
                    'today' => $foreignStats->today,
                    'week' => $foreignStats->week,
                    'month' => $foreignStats->month,
                ],
                'pharmaceutical_products' => [
                    'total' => $pharmaStats->total,
                    'active' => $pharmaStats->active,
                    'pending_review' => $pharmaStats->pending_review,
                    'preliminary_approved' => $pharmaStats->preliminary_approved,
                    'pending_final_approval' => $pharmaStats->pending_final_approval,
                    'pending_payment' => $pharmaStats->pending_payment,
                    'payment_review' => $pharmaStats->payment_review,
                    'rejected' => $pharmaStats->rejected,
                    'today' => $pharmaStats->today,
                    'week' => $pharmaStats->week,
                    'month' => $pharmaStats->month,
                ],
                'representatives' => [
                    'total' => $repStats->total,
                    'active' => $repStats->active,
                ],
                'revenue' => [
                    'local_total' => $localRevenue->total ?? 0,
                    'local_today' => $localRevenue->today ?? 0,
                    'local_week' => $localRevenue->week ?? 0,
                    'local_month' => $localRevenue->month ?? 0,
                    'pharma_total' => $pharmaRevenue->total ?? 0,
                    'pharma_today' => $pharmaRevenue->today ?? 0,
                    'pharma_week' => $pharmaRevenue->week ?? 0,
                    'pharma_month' => $pharmaRevenue->month ?? 0,
                    'invoices_paid' => ($localRevenue->paid_count ?? 0) + ($pharmaRevenue->paid_count ?? 0),
                    'invoices_unpaid' => ($localRevenue->unpaid_count ?? 0) + ($pharmaRevenue->unpaid_count ?? 0),
                    'total' => ($localRevenue->total ?? 0) + ($pharmaRevenue->total ?? 0),
                    'today' => ($localRevenue->today ?? 0) + ($pharmaRevenue->today ?? 0),
                    'week' => ($localRevenue->week ?? 0) + ($pharmaRevenue->week ?? 0),
                    'month' => ($localRevenue->month ?? 0) + ($pharmaRevenue->month ?? 0),
                ],
            ];
        });

        $pendingApprovalProducts = PharmaceuticalProduct::with(['foreignCompany', 'representative'])
            ->whereIn('status', ['pending_review', 'pending_final_approval'])
            ->latest()
            ->take(10)
            ->get();

        $pendingReceiptInvoices = PharmaceuticalProductInvoice::with(['pharmaceuticalProduct.representative'])
            ->where('status', 'unpaid')
            ->latest()
            ->take(10)
            ->get();

        $pendingLocalCompanies = LocalCompany::with('representative')
            ->whereIn('status', ['pending'])
            ->latest()
            ->take(10)
            ->get();

        $pendingForeignCompanies = ForeignCompany::with(['localCompany', 'representative'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingApprovalProducts',
            'pendingReceiptInvoices',
            'pendingLocalCompanies',
            'pendingForeignCompanies'
        ));
    }
}
