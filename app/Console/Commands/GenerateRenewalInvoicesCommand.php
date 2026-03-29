<?php

namespace App\Console\Commands;

use App\Jobs\GenerateRenewalInvoices;
use Illuminate\Console\Command;

class GenerateRenewalInvoicesCommand extends Command
{
    protected $signature = 'invoices:generate-renewals';

    protected $description = 'Generate annual renewal invoices for activated companies';

    public function handle()
    {
        $this->info(__('general.generating_annual_invoices'));

        GenerateRenewalInvoices::dispatchSync();

        $this->info(__('general.annual_invoices_generated'));

        return Command::SUCCESS;
    }
}
