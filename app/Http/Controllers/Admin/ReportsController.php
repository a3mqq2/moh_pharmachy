<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        fputcsv($output, ['تقرير الشركات المحلية']);
        fputcsv($output, ['تاريخ التقرير: ' . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, ['الإجماليات']);
        fputcsv($output, ['إجمالي الشركات', $stats['total']]);
        fputcsv($output, ['مفعلة', $stats['active']]);
        fputcsv($output, ['قيد المراجعة', $stats['pending']]);
        fputcsv($output, ['معتمدة', $stats['approved']]);
        fputcsv($output, ['مرفوضة', $stats['rejected']]);
        fputcsv($output, []);

        fputcsv($output, ['اسم الشركة', 'الممثل', 'الحالة', 'تاريخ التسجيل']);

        foreach ($companies as $company) {
            fputcsv($output, [
                $company->company_name,
                $company->representative?->name ?? '-',
                $company->status_name,
                $company->created_at->format('Y-m-d'),
            ]);
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

        fputcsv($output, ['تقرير الأصناف الدوائية']);
        fputcsv($output, ['تاريخ التقرير: ' . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, ['الإجماليات']);
        fputcsv($output, ['إجمالي الأصناف', $stats['total']]);
        fputcsv($output, ['معتمدة', $stats['active']]);
        fputcsv($output, ['قيد المراجعة', $stats['pending_review']]);
        fputcsv($output, ['موافقة مبدئية', $stats['preliminary_approved']]);
        fputcsv($output, ['قيد الموافقة النهائية', $stats['pending_final_approval']]);
        fputcsv($output, ['قيد السداد', $stats['pending_payment']]);
        fputcsv($output, ['قيد مراجعة السداد', $stats['payment_review']]);
        fputcsv($output, ['مرفوضة', $stats['rejected']]);
        fputcsv($output, []);

        fputcsv($output, ['اسم الصنف', 'الشكل الصيدلاني', 'التركيز', 'الشركة الأجنبية', 'الممثل', 'الحالة', 'تاريخ التسجيل']);

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

        fputcsv($output, ['تقرير الشركات الأجنبية']);
        fputcsv($output, ['تاريخ التقرير: ' . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, ['الإجماليات']);
        fputcsv($output, ['إجمالي الشركات', $stats['total']]);
        fputcsv($output, ['مفعلة', $stats['active']]);
        fputcsv($output, ['قيد المراجعة', $stats['pending']]);
        fputcsv($output, ['مرفوضة', $stats['rejected']]);
        fputcsv($output, ['منتهية', $stats['expired']]);
        fputcsv($output, []);

        fputcsv($output, ['اسم الشركة', 'الممثل', 'خط الإنتاج', 'المنشأ', 'الحالة', 'تاريخ الصلاحية']);

        foreach ($companies as $company) {
            fputcsv($output, [
                $company->company_name,
                $company->representative?->name ?? '-',
                $company->activity_type_name,
                $company->country,
                $company->status_name,
                $company->expires_at ? $company->expires_at->format('Y-m-d') : '-',
            ]);
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

        fputcsv($output, ['تقرير الفواتير']);
        fputcsv($output, ['تاريخ التقرير: ' . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, ['الإجماليات']);
        fputcsv($output, ['إجمالي الفواتير', $stats['total_invoices']]);
        fputcsv($output, ['إجمالي الإيرادات', number_format($stats['total_revenue'], 2) . ' د.ل']);
        fputcsv($output, []);

        if ($type == 'all' || $type == 'local') {
            fputcsv($output, ['فواتير الشركات المحلية']);
            fputcsv($output, ['إجمالي', $stats['local_total']]);
            fputcsv($output, ['مدفوعة', $stats['local_paid']]);
            fputcsv($output, ['غير مدفوعة', $stats['local_unpaid']]);
            fputcsv($output, ['الإيرادات', number_format($stats['local_revenue'], 2) . ' د.ل']);
            fputcsv($output, []);
            fputcsv($output, ['رقم الفاتورة', 'اسم الشركة', 'المبلغ', 'الحالة', 'تاريخ الإصدار']);

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
            fputcsv($output, ['فواتير الأصناف الدوائية']);
            fputcsv($output, ['إجمالي', $stats['pharma_total']]);
            fputcsv($output, ['مدفوعة', $stats['pharma_paid']]);
            fputcsv($output, ['غير مدفوعة', $stats['pharma_unpaid']]);
            fputcsv($output, ['الإيرادات', number_format($stats['pharma_revenue'], 2) . ' د.ل']);
            fputcsv($output, []);
            fputcsv($output, ['رقم الفاتورة', 'الصنف الدوائي', 'المبلغ', 'الحالة', 'تاريخ الإصدار']);

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
