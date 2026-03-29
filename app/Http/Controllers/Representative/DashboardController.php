<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ForeignCompany;
use App\Models\LocalCompany;
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
                    'company_name' => __('dashboard.pharma_product_prefix') . $invoice->pharmaceuticalProduct->product_name,
                    'amount' => $invoice->amount,
                    'type' => 'pharmaceutical',
                    'route' => route('representative.pharmaceutical-products.show', $invoice->pharmaceuticalProduct->id),
                ];
            }));

        $hasLocalCompanies = $representative->companies()->exists();
        $hasForeignCompanies = $representative->foreignCompanies()->exists();

        $announcementsQuery = Announcement::latest()->take(5);
        if ($hasLocalCompanies && !$hasForeignCompanies) {
            $announcementsQuery->whereIn('target', ['all', 'local']);
        } elseif (!$hasLocalCompanies && $hasForeignCompanies) {
            $announcementsQuery->whereIn('target', ['all', 'foreign']);
        }

        $announcements = $announcementsQuery->get();

        $threeMonthsFromNow = now()->addMonths(3);

        $expiringItems = collect();

        $expiringLocalCompanies = LocalCompany::where('representative_id', $representative->id)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', $threeMonthsFromNow)
            ->get();

        foreach ($expiringLocalCompanies as $company) {
            $renewalInvoice = $company->invoices()
                ->where('description', __('general.local_company_renewal_fee_desc'))
                ->where('status', 'unpaid')
                ->first();

            $expiringItems->push([
                'type' => 'local_company',
                'name' => $company->company_name,
                'expires_at' => $company->expires_at->format('Y-m-d'),
                'days_remaining' => (int) now()->diffInDays($company->expires_at),
                'invoice_route' => $renewalInvoice ? route('representative.invoices.show', $renewalInvoice->id) : null,
            ]);
        }

        $expiringForeignCompanies = ForeignCompany::where('representative_id', $representative->id)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', $threeMonthsFromNow)
            ->get();

        foreach ($expiringForeignCompanies as $company) {
            $renewalInvoice = $company->invoices()
                ->where('description', __('general.foreign_company_renewal_fee_desc'))
                ->where('status', 'pending')
                ->first();

            $expiringItems->push([
                'type' => 'foreign_company',
                'name' => $company->company_name,
                'expires_at' => $company->expires_at->format('Y-m-d'),
                'days_remaining' => (int) now()->diffInDays($company->expires_at),
                'invoice_route' => $renewalInvoice ? route('representative.foreign-companies.invoices.show', [$company->id, $renewalInvoice->id]) : null,
            ]);
        }

        return view('representative.dashboard', compact('representative', 'hasActiveSupplierCompany', 'pendingInvoices', 'announcements', 'expiringItems'));
    }
}
