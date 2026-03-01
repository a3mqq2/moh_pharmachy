<?php

namespace App\Console\Commands;

use App\Jobs\GenerateRenewalInvoices;
use Illuminate\Console\Command;

class GenerateRenewalInvoicesCommand extends Command
{
    protected $signature = 'invoices:generate-renewals';

    protected $description = 'إنشاء فواتير التجديد السنوية للشركات المفعلة';

    public function handle()
    {
        $this->info('جاري إنشاء فواتير التجديد السنوية...');

        GenerateRenewalInvoices::dispatchSync();

        $this->info('تم الانتهاء من إنشاء فواتير التجديد السنوية');

        return Command::SUCCESS;
    }
}
