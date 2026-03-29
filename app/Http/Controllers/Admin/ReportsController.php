<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\ForeignCompany;
use App\Models\LocalCompany;
use App\Models\PharmaceuticalProduct;
use App\Models\LocalCompanyInvoice;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_reports'),
        ];
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function localCompanies(Request $request)
    {
        $filtered = $request->hasAny(['status', 'from_date', 'to_date']);

        if (!$filtered) {
            $companies = collect();
            $stats = ['total' => 0, 'active' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
            return view('admin.reports.local-companies', compact('companies', 'stats', 'filtered'));
        }

        $query = LocalCompany::with('representative');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'uploading_documents'])->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];

        if ($request->has('print')) {
            $companies = $query->orderBy('created_at', 'desc')->get();
            return view('admin.reports.local-companies-print', compact('companies', 'stats'));
        }

        if ($request->has('export')) {
            $companies = $query->orderBy('created_at', 'desc')->get();
            return $this->exportLocalCompanies($companies, $stats);
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        return view('admin.reports.local-companies', compact('companies', 'stats', 'filtered'));
    }

    public function pharmaceuticalProducts(Request $request)
    {
        $filtered = $request->hasAny(['status', 'from_date', 'to_date']);

        if (!$filtered) {
            $products = collect();
            $stats = ['total' => 0, 'active' => 0, 'pending_review' => 0, 'preliminary_approved' => 0, 'pending_final_approval' => 0, 'pending_payment' => 0, 'payment_review' => 0, 'rejected' => 0];
            return view('admin.reports.pharmaceutical-products', compact('products', 'stats', 'filtered'));
        }

        $query = PharmaceuticalProduct::with(['foreignCompany', 'representative']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending_review' => (clone $query)->where('status', 'pending_review')->count(),
            'preliminary_approved' => (clone $query)->where('status', 'preliminary_approved')->count(),
            'pending_final_approval' => (clone $query)->where('status', 'pending_final_approval')->count(),
            'pending_payment' => (clone $query)->where('status', 'pending_payment')->count(),
            'payment_review' => (clone $query)->where('status', 'payment_review')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];

        if ($request->has('print')) {
            $products = $query->orderBy('created_at', 'desc')->get();
            return view('admin.reports.pharmaceutical-products-print', compact('products', 'stats'));
        }

        if ($request->has('export')) {
            $products = $query->orderBy('created_at', 'desc')->get();
            return $this->exportPharmaceuticalProducts($products, $stats);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        return view('admin.reports.pharmaceutical-products', compact('products', 'stats', 'filtered'));
    }

    public function foreignCompanies(Request $request)
    {
        $filtered = $request->hasAny(['status', 'country', 'from_date', 'to_date']);

        if (!$filtered) {
            $companies = collect();
            $stats = ['total' => 0, 'active' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'expired' => 0, 'suspended' => 0];
            return view('admin.reports.foreign-companies', compact('companies', 'stats', 'filtered'));
        }

        $query = ForeignCompany::with(['representative', 'localCompany']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'uploading_documents'])->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
        ];

        if ($request->has('print')) {
            $companies = $query->orderBy('created_at', 'desc')->get();
            return view('admin.reports.foreign-companies-print', compact('companies', 'stats'));
        }

        if ($request->has('export')) {
            $companies = $query->orderBy('created_at', 'desc')->get();
            return $this->exportForeignCompanies($companies, $stats);
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        return view('admin.reports.foreign-companies', compact('companies', 'stats', 'filtered'));
    }

    public function invoices(Request $request)
    {
        $filtered = $request->hasAny(['type', 'status', 'from_date', 'to_date']);

        if (!$filtered) {
            $localInvoices = collect();
            $pharmaInvoices = collect();
            $stats = ['local_total' => 0, 'local_paid' => 0, 'local_unpaid' => 0, 'local_revenue' => 0, 'pharma_total' => 0, 'pharma_paid' => 0, 'pharma_unpaid' => 0, 'pharma_revenue' => 0, 'total_invoices' => 0, 'total_revenue' => 0];
            $type = 'all';
            return view('admin.reports.invoices', compact('localInvoices', 'pharmaInvoices', 'stats', 'type', 'filtered'));
        }

        $type = $request->get('type', 'all');

        $localInvoices = collect();
        $pharmaInvoices = collect();
        $localStats = [];
        $pharmaStats = [];

        if ($type == 'all' || $type == 'local') {
            $localQuery = LocalCompanyInvoice::with('localCompany');

            if ($request->filled('status')) {
                $localQuery->where('status', $request->status);
            }

            if ($request->filled('from_date')) {
                $localQuery->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $localQuery->whereDate('created_at', '<=', $request->to_date);
            }

            $localStats = [
                'total' => (clone $localQuery)->count(),
                'paid' => (clone $localQuery)->where('status', 'paid')->count(),
                'unpaid' => (clone $localQuery)->where('status', 'unpaid')->count(),
                'revenue' => (clone $localQuery)->where('status', 'paid')->sum('amount'),
            ];

            if ($request->has('print') || $request->has('export')) {
                $localInvoices = $localQuery->orderBy('created_at', 'desc')->get();
            } else {
                $localInvoices = $localQuery->orderBy('created_at', 'desc')->paginate(30, ['*'], 'local_page')->withQueryString();
            }
        }

        if ($type == 'all' || $type == 'pharmaceutical') {
            $pharmaQuery = PharmaceuticalProductInvoice::with('pharmaceuticalProduct');

            if ($request->filled('status')) {
                $pharmaQuery->where('status', $request->status == 'paid' ? 'paid' : 'unpaid');
            }

            if ($request->filled('from_date')) {
                $pharmaQuery->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $pharmaQuery->whereDate('created_at', '<=', $request->to_date);
            }

            $pharmaStats = [
                'total' => (clone $pharmaQuery)->count(),
                'paid' => (clone $pharmaQuery)->where('status', 'paid')->count(),
                'unpaid' => (clone $pharmaQuery)->where('status', 'unpaid')->count(),
                'revenue' => (clone $pharmaQuery)->where('status', 'paid')->sum('amount'),
            ];

            if ($request->has('print') || $request->has('export')) {
                $pharmaInvoices = $pharmaQuery->orderBy('created_at', 'desc')->get();
            } else {
                $pharmaInvoices = $pharmaQuery->orderBy('created_at', 'desc')->paginate(30, ['*'], 'pharma_page')->withQueryString();
            }
        }

        $stats = [
            'local_total' => $localStats['total'] ?? 0,
            'local_paid' => $localStats['paid'] ?? 0,
            'local_unpaid' => $localStats['unpaid'] ?? 0,
            'local_revenue' => $localStats['revenue'] ?? 0,
            'pharma_total' => $pharmaStats['total'] ?? 0,
            'pharma_paid' => $pharmaStats['paid'] ?? 0,
            'pharma_unpaid' => $pharmaStats['unpaid'] ?? 0,
            'pharma_revenue' => $pharmaStats['revenue'] ?? 0,
        ];

        $stats['total_invoices'] = $stats['local_total'] + $stats['pharma_total'];
        $stats['total_revenue'] = $stats['local_revenue'] + $stats['pharma_revenue'];

        if ($request->has('print')) {
            return view('admin.reports.invoices-print', compact('localInvoices', 'pharmaInvoices', 'stats', 'type'));
        }

        if ($request->has('export')) {
            return $this->exportInvoices($localInvoices, $pharmaInvoices, $stats, $type);
        }

        return view('admin.reports.invoices', compact('localInvoices', 'pharmaInvoices', 'stats', 'type', 'filtered'));
    }

    private function exportLocalCompanies($companies, $stats)
    {
        $filename = 'local_companies_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [__('reports.csv_local_companies_report')]);
        fputcsv($output, [__('reports.csv_report_date') . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, [__('reports.csv_totals')]);
        fputcsv($output, [__('reports.csv_total_companies'), $stats['total']]);
        fputcsv($output, [__('reports.csv_active'), $stats['active']]);
        fputcsv($output, [__('reports.csv_pending_review'), $stats['pending']]);
        fputcsv($output, [__('reports.csv_approved'), $stats['approved']]);
        fputcsv($output, [__('reports.csv_rejected'), $stats['rejected']]);
        fputcsv($output, []);

        $allHeaders = [
            0 => '#',
            1 => __('reports.csv_company_name'),
            2 => __('companies.company_type'),
            3 => __('general.city'),
            4 => __('general.phone'),
            5 => __('general.email'),
            6 => __('companies.license_type'),
            7 => __('companies.license_specialty'),
            8 => __('companies.manager_name'),
            9 => __('reports.csv_representative'),
            10 => __('reports.csv_status'),
            11 => __('reports.csv_reg_date'),
            12 => __('companies.expiry_date'),
        ];

        $visibleCols = request('cols') ? array_map('intval', explode(',', request('cols'))) : array_keys($allHeaders);

        $headers = [];
        foreach ($allHeaders as $i => $label) {
            if (in_array($i, $visibleCols)) $headers[] = $label;
        }
        fputcsv($output, $headers);

        $counter = 0;
        foreach ($companies as $company) {
            $counter++;
            $allData = [
                0 => $counter,
                1 => $company->company_name,
                2 => $company->company_type_name,
                3 => $company->city ?? '-',
                4 => $company->phone ?? '-',
                5 => $company->email ?? '-',
                6 => $company->license_type_name,
                7 => $company->license_specialty_name,
                8 => $company->manager_name ?? '-',
                9 => $company->representative?->full_name ?? '-',
                10 => $company->status_name,
                11 => $company->created_at->format('Y-m-d'),
                12 => $company->expires_at ? $company->expires_at->format('Y-m-d') : '-',
            ];

            $row = [];
            foreach ($allData as $i => $val) {
                if (in_array($i, $visibleCols)) $row[] = $val;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function exportPharmaceuticalProducts($products, $stats)
    {
        $filename = 'pharmaceutical_products_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [__('reports.csv_pharma_report')]);
        fputcsv($output, [__('reports.csv_report_date') . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, [__('reports.csv_totals')]);
        fputcsv($output, [__('reports.csv_total_products'), $stats['total']]);
        fputcsv($output, [__('reports.csv_approved'), $stats['active']]);
        fputcsv($output, [__('reports.csv_pending_review'), $stats['pending_review']]);
        fputcsv($output, [__('reports.csv_preliminary_approved'), $stats['preliminary_approved']]);
        fputcsv($output, [__('reports.csv_pending_final'), $stats['pending_final_approval']]);
        fputcsv($output, [__('reports.csv_pending_payment'), $stats['pending_payment']]);
        fputcsv($output, [__('reports.csv_payment_review'), $stats['payment_review']]);
        fputcsv($output, [__('reports.csv_rejected'), $stats['rejected']]);
        fputcsv($output, []);

        fputcsv($output, [__('reports.csv_product_name'), __('reports.csv_pharma_form'), __('reports.csv_concentration'), __('reports.csv_foreign_company'), __('reports.csv_representative'), __('reports.csv_status'), __('reports.csv_reg_date')]);

        foreach ($products as $product) {
            fputcsv($output, [
                $product->product_name,
                $product->pharmaceutical_form,
                $product->concentration,
                $product->foreignCompany->company_name,
                $product->representative->name,
                $product->status_name,
                $product->created_at->format('Y-m-d'),
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportForeignCompanies($companies, $stats)
    {
        $filename = 'foreign_companies_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [__('reports.csv_foreign_companies_report')]);
        fputcsv($output, [__('reports.csv_report_date') . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, [__('reports.csv_totals')]);
        fputcsv($output, [__('reports.csv_total_companies'), $stats['total']]);
        fputcsv($output, [__('reports.csv_active'), $stats['active']]);
        fputcsv($output, [__('reports.csv_pending_review'), $stats['pending']]);
        fputcsv($output, [__('reports.csv_rejected'), $stats['rejected']]);
        fputcsv($output, [__('reports.csv_expired'), $stats['expired']]);
        fputcsv($output, []);

        $allHeaders = [
            0 => '#',
            1 => __('reports.csv_company_name'),
            2 => __('companies.entity_type'),
            3 => __('reports.csv_origin'),
            4 => __('general.email'),
            5 => __('reports.csv_production_line'),
            6 => __('companies.local_company'),
            7 => __('reports.csv_representative'),
            8 => __('general.registration_number'),
            9 => __('companies.meeting_number'),
            10 => __('companies.meeting_date'),
            11 => __('reports.csv_status'),
            12 => __('reports.csv_reg_date'),
            13 => __('reports.csv_expiry_date'),
        ];

        $visibleCols = request('cols') ? array_map('intval', explode(',', request('cols'))) : array_keys($allHeaders);

        $headers = [];
        foreach ($allHeaders as $i => $label) {
            if (in_array($i, $visibleCols)) $headers[] = $label;
        }
        fputcsv($output, $headers);

        $counter = 0;
        foreach ($companies as $company) {
            $counter++;
            $allData = [
                0 => $counter,
                1 => $company->company_name,
                2 => $company->entity_type_name,
                3 => $company->country,
                4 => $company->email ?? '-',
                5 => $company->activity_type_name,
                6 => $company->localCompany?->company_name ?? '-',
                7 => $company->representative?->full_name ?? '-',
                8 => $company->registration_number ?? '-',
                9 => $company->meeting_number ?? '-',
                10 => $company->meeting_date ? Carbon::parse($company->meeting_date)->format('Y-m-d') : '-',
                11 => $company->status_name,
                12 => $company->created_at->format('Y-m-d'),
                13 => $company->expires_at ? $company->expires_at->format('Y-m-d') : '-',
            ];

            $row = [];
            foreach ($allData as $i => $val) {
                if (in_array($i, $visibleCols)) $row[] = $val;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function exportInvoices($localInvoices, $pharmaInvoices, $stats, $type)
    {
        $filename = 'invoices_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [__('reports.csv_invoices_report')]);
        fputcsv($output, [__('reports.csv_report_date') . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, [__('reports.csv_totals')]);
        fputcsv($output, [__('reports.csv_total_invoices'), $stats['total_invoices']]);
        fputcsv($output, [__('reports.csv_total_revenue'), number_format($stats['total_revenue'], 2) . ' ' . __('general.lyd')]);
        fputcsv($output, []);

        if ($type == 'all' || $type == 'local') {
            fputcsv($output, [__('reports.csv_local_invoices')]);
            fputcsv($output, [__('reports.csv_total'), $stats['local_total']]);
            fputcsv($output, [__('reports.csv_paid'), $stats['local_paid']]);
            fputcsv($output, [__('reports.csv_unpaid'), $stats['local_unpaid']]);
            fputcsv($output, [__('reports.csv_revenue'), number_format($stats['local_revenue'], 2) . ' ' . __('general.lyd')]);
            fputcsv($output, []);
            fputcsv($output, [__('reports.csv_invoice_no'), __('reports.csv_company_name'), __('reports.csv_amount'), __('reports.csv_status'), __('reports.csv_issue_date')]);

            foreach ($localInvoices as $invoice) {
                fputcsv($output, [
                    $invoice->invoice_number,
                    $invoice->localCompany->company_name,
                    number_format($invoice->amount, 2),
                    $invoice->status_name,
                    $invoice->created_at->format('Y-m-d'),
                ]);
            }
            fputcsv($output, []);
        }

        if ($type == 'all' || $type == 'pharmaceutical') {
            fputcsv($output, [__('reports.csv_pharma_invoices')]);
            fputcsv($output, [__('reports.csv_total'), $stats['pharma_total']]);
            fputcsv($output, [__('reports.csv_paid'), $stats['pharma_paid']]);
            fputcsv($output, [__('reports.csv_unpaid'), $stats['pharma_unpaid']]);
            fputcsv($output, [__('reports.csv_revenue'), number_format($stats['pharma_revenue'], 2) . ' ' . __('general.lyd')]);
            fputcsv($output, []);
            fputcsv($output, [__('reports.csv_invoice_no'), __('reports.csv_product'), __('reports.csv_amount'), __('reports.csv_status'), __('reports.csv_issue_date')]);

            foreach ($pharmaInvoices as $invoice) {
                fputcsv($output, [
                    $invoice->invoice_number,
                    $invoice->pharmaceuticalProduct->product_name,
                    number_format($invoice->amount, 2),
                    $invoice->status_name,
                    $invoice->created_at->format('Y-m-d'),
                ]);
            }
        }

        fclose($output);
        exit;
    }
}
