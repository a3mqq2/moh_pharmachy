<?php

namespace App\Jobs;

use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateRenewalInvoices implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $renewalFee = Setting::get('local_company_renewal_fee', 0);

        if ($renewalFee <= 0) {
            Log::info('GenerateRenewalInvoices: رسوم التجديد غير محددة أو صفر');
            return;
        }

        $companies = LocalCompany::where('status', 'approved')
            ->whereNotNull('registration_date')
            ->get();

        $currentYear = now()->year;
        $generatedCount = 0;

        foreach ($companies as $company) {
            $hasRenewalForCurrentYear = $company->invoices()
                ->where('type', 'renewal')
                ->whereYear('created_at', $currentYear)
                ->exists();

            if ($hasRenewalForCurrentYear) {
                continue;
            }

            $invoice = $company->invoices()->create([
                'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                'type' => 'renewal',
                'description' => 'رسوم تجديد سنوية - ' . $currentYear,
                'amount' => $renewalFee,
                'due_date' => now()->addDays(30),
                'created_by' => null,
            ]);

            $company->logActivity('invoice_created', 'تم إنشاء فاتورة تجديد سنوية تلقائية رقم: ' . $invoice->invoice_number);

            $generatedCount++;
        }

        Log::info("GenerateRenewalInvoices: تم إنشاء {$generatedCount} فاتورة تجديد سنوية");
    }
}
