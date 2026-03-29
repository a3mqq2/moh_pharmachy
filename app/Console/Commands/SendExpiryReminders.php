<?php

namespace App\Console\Commands;

use App\Mail\ExpiryReminderMail;
use App\Models\ForeignCompany;
use App\Models\ForeignCompanyInvoice;
use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature = 'companies:send-expiry-reminders';

    protected $description = 'Send expiry reminder emails and create renewal invoices 3 months before expiry';

    public function handle()
    {
        $this->info('Checking for companies expiring within 3 months...');

        $threeMonthsFromNow = now()->addMonths(3);

        $localCompanies = LocalCompany::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', $threeMonthsFromNow)
            ->with('representative')
            ->get();

        $foreignCompanies = ForeignCompany::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', $threeMonthsFromNow)
            ->with('representative')
            ->get();

        $localRenewalFee = Setting::where('key', 'local_company_renewal_fee')->first()?->value ?? 500.00;
        $foreignRenewalFee = Setting::where('key', 'foreign_company_renewal_fee')->first()?->value ?? 1000.00;

        $grouped = [];

        foreach ($localCompanies as $company) {
            $hasRenewalInvoice = $company->invoices()
                ->where('type', 'renewal')
                ->whereIn('status', ['unpaid', 'paid'])
                ->where('created_at', '>=', now()->subMonths(6))
                ->exists();

            if (!$hasRenewalInvoice) {
                $company->invoices()->create([
                    'invoice_number' => LocalCompanyInvoice::generateInvoiceNumber(),
                    'amount' => $localRenewalFee,
                    'description' => __('general.local_company_renewal_fee_desc'),
                    'status' => 'unpaid',
                    'due_date' => $company->expires_at,
                ]);
            }

            if (!$company->representative || !$company->representative->email) {
                continue;
            }

            $repId = $company->representative_id;
            if (!isset($grouped[$repId])) {
                $grouped[$repId] = [
                    'email' => $company->representative->email,
                    'name' => $company->representative->name,
                    'items' => [],
                ];
            }

            $grouped[$repId]['items'][] = [
                'type' => 'local_company',
                'name' => $company->company_name,
                'expires_at' => $company->expires_at->format('Y-m-d'),
                'days_remaining' => (int) now()->diffInDays($company->expires_at),
            ];
        }

        foreach ($foreignCompanies as $company) {
            $hasRenewalInvoice = $company->invoices()
                ->where('description', 'like', '%' . __('companies.invoice_desc_foreign_renewal') . '%')
                ->whereIn('status', ['pending', 'paid'])
                ->where('created_at', '>=', now()->subMonths(6))
                ->exists();

            if (!$hasRenewalInvoice) {
                $company->invoices()->create([
                    'invoice_number' => ForeignCompanyInvoice::generateInvoiceNumber(),
                    'amount' => $foreignRenewalFee,
                    'description' => __('general.foreign_company_renewal_fee_desc'),
                    'status' => 'pending',
                    'due_date' => $company->expires_at,
                ]);
            }

            if (!$company->representative || !$company->representative->email) {
                continue;
            }

            $repId = $company->representative_id;
            if (!isset($grouped[$repId])) {
                $grouped[$repId] = [
                    'email' => $company->representative->email,
                    'name' => $company->representative->name,
                    'items' => [],
                ];
            }

            $grouped[$repId]['items'][] = [
                'type' => 'foreign_company',
                'name' => $company->company_name,
                'expires_at' => $company->expires_at->format('Y-m-d'),
                'days_remaining' => (int) now()->diffInDays($company->expires_at),
            ];
        }

        $sentCount = 0;

        foreach ($grouped as $repData) {
            try {
                Mail::to($repData['email'])
                    ->queue(new ExpiryReminderMail($repData['items'], $repData['name']));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send expiry reminder to ' . $repData['email'] . ': ' . $e->getMessage());
            }
        }

        $this->info("Sent {$sentCount} expiry reminder emails.");
        $this->info('Local companies expiring: ' . $localCompanies->count());
        $this->info('Foreign companies expiring: ' . $foreignCompanies->count());

        return 0;
    }
}
