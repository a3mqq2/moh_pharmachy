<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\LocalCompanyInvoice;
use App\Models\ForeignCompanyInvoice;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $representative = Auth::guard('representative')->user();

        $hasActiveSupplierCompany = $representative->companies()
            ->where('company_type', 'supplier')
            ->where('status', 'active')
            ->exists();

        $localCompanyIds = $representative->companies()->pluck('id');

        $pendingLocalInvoices = LocalCompanyInvoice::whereIn('local_company_id', $localCompanyIds)
            ->where('status', 'unpaid')
            ->whereNull('receipt_path')
            ->with('localCompany')
            ->get();

        $foreignCompanyIds = $representative->foreignCompanies()->pluck('id');

        $pendingForeignInvoices = ForeignCompanyInvoice::whereIn('foreign_company_id', $foreignCompanyIds)
            ->where('status', 'pending')
            ->whereNull('receipt_path')
            ->with('foreignCompany')
            ->get();

        $pendingPharmaceuticalInvoices = PharmaceuticalProductInvoice::whereHas('pharmaceuticalProduct', function($query) use ($representative) {
                $query->where('representative_id', $representative->id);
            })
            ->where('status', 'unpaid')
            ->whereNull('receipt_path')
            ->with('pharmaceuticalProduct')
            ->get();

        $pendingInvoices = collect()
            ->concat($pendingLocalInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'company_name' => $invoice->localCompany->company_name,
                    'amount' => $invoice->amount,
                    'type' => 'local',
                    'route' => route('representative.invoices.show', $invoice->id),
                ];
            }))
            ->concat($pendingForeignInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'company_name' => $invoice->foreignCompany->company_name,
                    'amount' => $invoice->amount,
                    'type' => 'foreign',
                    'route' => route('representative.foreign-companies.invoices.show', [$invoice->foreign_company_id, $invoice->id]),
                ];
            }))
            ->concat($pendingPharmaceuticalInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'company_name' => 'صنف دوائي: ' . $invoice->pharmaceuticalProduct->product_name,
                    'amount' => $invoice->amount,
                    'type' => 'pharmaceutical',
                    'route' => route('representative.pharmaceutical-products.show', $invoice->pharmaceuticalProduct->id),
                ];
            }));

        return view('representative.dashboard', compact('representative', 'hasActiveSupplierCompany', 'pendingInvoices'));
    }
}
