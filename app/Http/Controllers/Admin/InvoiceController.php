<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompanyInvoice;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $localInvoices = collect();
        $foreignInvoices = collect();
        $pharmaceuticalInvoices = collect();

        if ($type == 'all' || $type == 'local') {
            $query = LocalCompanyInvoice::with(['localCompany', 'creator']);

            if ($status) {
                $query->where('status', $status);
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
                    'company_name' => $invoice->pharmaceuticalProduct->product_name ?? 'غير متوفر'
                ];
                $invoice->description = 'فاتورة تسجيل صنف دوائي';
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

        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $invoices->count();
        $invoices = $invoices->forPage($currentPage, $perPage);

        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $invoices,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total' => LocalCompanyInvoice::count() + ForeignCompanyInvoice::count() + PharmaceuticalProductInvoice::count(),
            'local_total' => LocalCompanyInvoice::count(),
            'foreign_total' => ForeignCompanyInvoice::count(),
            'pharmaceutical_total' => PharmaceuticalProductInvoice::count(),
            'pending' => LocalCompanyInvoice::where('status', 'unpaid')->count() +
                        ForeignCompanyInvoice::where('status', 'pending')->count() +
                        PharmaceuticalProductInvoice::where('status', 'unpaid')->count() +
                        PharmaceuticalProductInvoice::where('status', 'pending_review')->count(),
            'paid' => LocalCompanyInvoice::where('status', 'paid')->count() +
                     ForeignCompanyInvoice::where('status', 'paid')->count() +
                     PharmaceuticalProductInvoice::where('status', 'paid')->count(),
        ];

        return view('admin.invoices.index', [
            'invoices' => $pagination,
            'stats' => $stats,
        ]);
    }
}
