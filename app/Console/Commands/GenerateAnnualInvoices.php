<?php

namespace App\Console\Commands;

use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateAnnualInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-annual
                            {--test : Run in test mode without creating invoices}
                            {--company-type= : Generate invoices for specific company type (local/foreign)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate annual renewal invoices for active companies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testMode = $this->option('test');
        $companyType = $this->option('company-type');

        $this->info('Starting annual invoice generation...');
        $this->info('Test mode: ' . ($testMode ? 'Yes' : 'No'));

        $stats = [
            'foreign_companies_processed' => 0,
            'foreign_invoices_created' => 0,
            'local_companies_processed' => 0,
            'local_invoices_created' => 0,
            'errors' => 0,
        ];

        try {
            // Generate invoices for foreign companies
            if (!$companyType || $companyType == 'foreign') {
                $this->info('Processing foreign companies...');
                $this->generateForeignCompanyInvoices($testMode, $stats);
            }

            // Generate invoices for local companies
            if (!$companyType || $companyType == 'local') {
                $this->info('Processing local companies...');
                $this->generateLocalCompanyInvoices($testMode, $stats);
            }

            // Display summary
            $this->newLine();
            $this->info('== Summary ==');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Foreign Companies Processed', $stats['foreign_companies_processed']],
                    ['Foreign Invoices Created', $stats['foreign_invoices_created']],
                    ['Local Companies Processed', $stats['local_companies_processed']],
                    ['Local Invoices Created', $stats['local_invoices_created']],
                    ['Errors', $stats['errors']],
                ]
            );

            if ($testMode) {
                $this->warn('Test mode - No invoices were actually created');
            }

            Log::info('Annual invoices generation completed', $stats);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Annual invoices generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Generate annual invoices for foreign companies
     */
    private function generateForeignCompanyInvoices(bool $testMode, array &$stats): void
    {
        $annualFee = Setting::get('foreign_company_annual_fee', 5000);

        // Get active companies that need annual renewal
        $companies = ForeignCompany::where('status', 'active')
            ->whereNotNull('activated_at')
            ->get();

        $this->info("Found {$companies->count()} active foreign companies");

        foreach ($companies as $company) {
            $stats['foreign_companies_processed']++;

            try {
                // Check if company activation was at least 1 year ago
                $activationDate = Carbon::parse($company->activated_at);
                $lastAnniversary = $activationDate->copy();

                // Find the most recent anniversary
                while ($lastAnniversary->addYear()->isPast()) {
                    // Keep adding years
                }
                $lastAnniversary->subYear(); // Go back to the last anniversary that has passed

                // Check if we already have an invoice for this anniversary
                $existingInvoice = $company->invoices()
                    ->where('description', 'LIKE', '%رسوم تجديد سنوي%')
                    ->where('created_at', '>=', $lastAnniversary)
                    ->where('created_at', '<', $lastAnniversary->copy()->addDays(30))
                    ->first();

                if ($existingInvoice) {
                    $this->line("Skipping {$company->company_name} - Invoice already exists");
                    continue;
                }

                // Check if the anniversary is within the current month
                $now = Carbon::now();
                if ($lastAnniversary->month != $now->month || $lastAnniversary->year != $now->year) {
                    continue;
                }

                if (!$testMode) {
                    DB::transaction(function () use ($company, $annualFee, $activationDate, $lastAnniversary) {
                        $yearNumber = $lastAnniversary->year - $activationDate->year;

                        ForeignCompanyInvoice::create([
                            'foreign_company_id' => $company->id,
                            'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                            'amount' => $annualFee,
                            'description' => "رسوم تجديد سنوي للشركة الأجنبية - السنة {$yearNumber}",
                            'status' => 'pending',
                            'issued_by' => 1, // System user
                            'due_date' => now()->addDays(30),
                        ]);

                        // Update company status if needed
                        if ($company->status == 'active') {
                            $company->update(['status' => 'pending_payment']);
                        }
                    });

                    $stats['foreign_invoices_created']++;
                    $this->info("Created invoice for: {$company->company_name}");
                } else {
                    $this->line("Would create invoice for: {$company->company_name} (Amount: {$annualFee})");
                    $stats['foreign_invoices_created']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("Error processing {$company->company_name}: " . $e->getMessage());
                Log::error("Error creating annual invoice for foreign company {$company->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Generate annual invoices for local companies
     */
    private function generateLocalCompanyInvoices(bool $testMode, array &$stats): void
    {
        $annualFee = Setting::get('local_company_annual_fee', 3000);

        // Get active companies that need annual renewal
        $companies = LocalCompany::where('status', 'active')
            ->whereNotNull('activated_at')
            ->get();

        $this->info("Found {$companies->count()} active local companies");

        foreach ($companies as $company) {
            $stats['local_companies_processed']++;

            try {
                // Check if company activation was at least 1 year ago
                $activationDate = Carbon::parse($company->activated_at);
                $lastAnniversary = $activationDate->copy();

                // Find the most recent anniversary
                while ($lastAnniversary->addYear()->isPast()) {
                    // Keep adding years
                }
                $lastAnniversary->subYear(); // Go back to the last anniversary that has passed

                // Check if we already have an invoice for this anniversary
                $existingInvoice = $company->invoices()
                    ->where('description', 'LIKE', '%رسوم تجديد سنوي%')
                    ->where('created_at', '>=', $lastAnniversary)
                    ->where('created_at', '<', $lastAnniversary->copy()->addDays(30))
                    ->first();

                if ($existingInvoice) {
                    $this->line("Skipping {$company->company_name} - Invoice already exists");
                    continue;
                }

                // Check if the anniversary is within the current month
                $now = Carbon::now();
                if ($lastAnniversary->month != $now->month || $lastAnniversary->year != $now->year) {
                    continue;
                }

                if (!$testMode) {
                    DB::transaction(function () use ($company, $annualFee, $activationDate, $lastAnniversary) {
                        $yearNumber = $lastAnniversary->year - $activationDate->year;

                        LocalCompanyInvoice::create([
                            'local_company_id' => $company->id,
                            'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                            'amount' => $annualFee,
                            'description' => "رسوم تجديد سنوي للشركة المحلية - السنة {$yearNumber}",
                            'status' => 'pending',
                            'issued_by' => 1, // System user
                            'due_date' => now()->addDays(30),
                        ]);

                        // Update company status if needed
                        if ($company->status == 'active') {
                            $company->update(['status' => 'pending_payment']);
                        }
                    });

                    $stats['local_invoices_created']++;
                    $this->info("Created invoice for: {$company->company_name}");
                } else {
                    $this->line("Would create invoice for: {$company->company_name} (Amount: {$annualFee})");
                    $stats['local_invoices_created']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("Error processing {$company->company_name}: " . $e->getMessage());
                Log::error("Error creating annual invoice for local company {$company->id}: " . $e->getMessage());
            }
        }
    }
}
