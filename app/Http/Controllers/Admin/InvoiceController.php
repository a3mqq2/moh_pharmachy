<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompanyInvoice;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_invoices'),
        ];
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $localInvoices = collect();
        $foreignInvoices = collect();
        $pharmaceuticalInvoices = collect();

        if ($type == 'all' || $type == 'local') {
            $query = LocalCompanyInvoice::with(['localCompany', 'creator']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('localCompany', function ($q) use ($search) {
                          $q->where('company_name', 'like', "%{$search}%");
                      });
                });
            }

            $localInvoices = $query->get()->map(function ($invoice) {
                $invoice->company_type = 'local';
                $invoice->company = $invoice->localCompany;
                return $invoice;
            });
        }

        if ($type == 'all' || $type == 'foreign') {
            $query = ForeignCompanyInvoice::with(['foreignCompany', 'issuedBy']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('foreignCompany', function ($q) use ($search) {
                          $q->where('company_name', 'like', "%{$search}%");
                      });
                });
            }

            $foreignInvoices = $query->get()->map(function ($invoice) {
                $invoice->company_type = 'foreign';
                $invoice->company = $invoice->foreignCompany;
                return $invoice;
            });
        }

        if ($type == 'all' || $type == 'pharmaceutical') {
            $query = PharmaceuticalProductInvoice::with(['pharmaceuticalProduct.representative']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('pharmaceuticalProduct', function ($q) use ($search) {
                          $q->where('product_name', 'like', "%{$search}%");
                      });
                });
            }

            $pharmaceuticalInvoices = $query->get()->map(function ($invoice) {
                $invoice->company_type = 'pharmaceutical';
                $invoice->company = (object) [
                    'company_name' => $invoice->pharmaceuticalProduct->product_name ?? __('general.not_available')
                ];
                $invoice->description = __('invoices.pharma_registration_invoice');
                return $invoice;
            });
        }

        $invoices = $localInvoices->merge($foreignInvoices)->merge($pharmaceuticalInvoices);

        if ($sortBy == 'created_at') {
            $invoices = $sortOrder == 'desc'
                ? $invoices->sortByDesc('created_at')
                : $invoices->sortBy('created_at');
        } elseif ($sortBy == 'amount') {
            $invoices = $sortOrder == 'desc'
                ? $invoices->sortByDesc('amount')
                : $invoices->sortBy('amount');
        }

        $stats = [
            'total' => $invoices->count(),
            'local_total' => $localInvoices->count(),
            'foreign_total' => $foreignInvoices->count(),
            'pharmaceutical_total' => $pharmaceuticalInvoices->count(),
            'pending' => $invoices->filter(fn($i) => in_array($i->status, ['unpaid', 'pending', 'pending_review']))->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            'total_revenue' => $invoices->where('status', 'paid')->sum('amount'),
        ];

        if ($request->has('print')) {
            return view('admin.invoices.print', compact('invoices', 'stats'));
        }

        if ($request->has('export')) {
            return $this->exportInvoices($invoices, $stats);
        }

        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $invoices->count();
        $invoicesPage = $invoices->forPage($currentPage, $perPage);

        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $invoicesPage,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.invoices.index', [
            'invoices' => $pagination,
            'stats' => $stats,
        ]);
    }

    private function exportInvoices($invoices, $stats)
    {
        $filename = 'invoices_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [__('reports.csv_invoices_report')]);
        fputcsv($output, [__('reports.csv_report_date') . date('Y-m-d')]);
        fputcsv($output, []);
        fputcsv($output, [__('reports.csv_total_invoices'), $stats['total']]);
        fputcsv($output, [__('reports.csv_paid'), $stats['paid']]);
        fputcsv($output, [__('reports.csv_pending'), $stats['pending']]);
        fputcsv($output, [__('reports.csv_total_revenue'), number_format($stats['total_revenue'], 2) . ' ' . __('general.lyd')]);
        fputcsv($output, []);

        fputcsv($output, [__('reports.csv_invoice_no'), __('reports.csv_type'), __('reports.csv_company_name'), __('reports.csv_amount'), __('reports.csv_status'), __('reports.csv_created_date')]);

        foreach ($invoices as $invoice) {
            $typeName = match($invoice->company_type) {
                'local' => __('companies.local'),
                'foreign' => __('companies.foreign'),
                default => __('products.pharmaceutical'),
            };

            fputcsv($output, [
                $invoice->invoice_number,
                $typeName,
                $invoice->company?->company_name ?? __('general.not_available'),
                number_format($invoice->amount, 2),
                $invoice->status,
                $invoice->created_at->format('Y-m-d'),
            ]);
        }

        fclose($output);
        exit;
    }
}
