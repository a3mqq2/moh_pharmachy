<?php

namespace App\Console\Commands;

use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Console\Command;

class CheckExpiredCompanies extends Command
{
    protected $signature = 'companies:check-expired';

    protected $description = 'Check for expired companies and create renewal invoices';

    public function handle()
    {
        $this->info('Checking for expired companies...');

        $expiredLocalCount = $this->checkLocalCompanies();
        $expiredForeignCount = $this->checkForeignCompanies();

        $this->info("Local companies expired: {$expiredLocalCount}");
        $this->info("Foreign companies expired: {$expiredForeignCount}");
        $this->info('Done!');

        return 0;
    }

    private function checkLocalCompanies(): int
    {
        $count = 0;

        $companies = LocalCompany::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $renewalFee = Setting::where('key', 'local_company_renewal_fee')->first()?->value ?? 500.00;

        foreach ($companies as $company) {
            $company->markAsExpired();

            $hasRecentRenewal = $company->invoices()
                ->where('type', 'renewal')
                ->whereIn('status', ['unpaid', 'paid'])
                ->where('created_at', '>=', now()->subMonths(6))
                ->exists();

            if (!$hasRecentRenewal) {
                $company->invoices()->create([
                    'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                    'type' => 'renewal',
                    'amount' => $renewalFee,
                    'description' => 'رسوم تجديد الشركة المحلية',
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                ]);
            }

            $count++;
        }

        return $count;
    }

    private function checkForeignCompanies(): int
    {
        $count = 0;

        $companies = ForeignCompany::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $renewalFee = Setting::where('key', 'foreign_company_renewal_fee')->first()?->value ?? 1000.00;

        foreach ($companies as $company) {
            $company->markAsExpired();

            $hasRecentRenewal = $company->invoices()
                ->where('description', 'like', '%تجديد%')
                ->whereIn('status', ['pending', 'paid'])
                ->where('created_at', '>=', now()->subMonths(6))
                ->exists();

            if (!$hasRecentRenewal) {
                $company->invoices()->create([
                    'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                    'amount' => $renewalFee,
                    'description' => 'رسوم تجديد الشركة الأجنبية',
                    'status' => 'pending',
                    'due_date' => now()->addDays(30),
                ]);
            }

            $count++;
        }

        return $count;
    }
}
