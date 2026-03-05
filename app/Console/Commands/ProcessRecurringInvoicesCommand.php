<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use App\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class ProcessRecurringInvoicesCommand extends Command
{
    protected $signature = 'invoices:process-recurring';

    protected $description = 'Generate invoices for due recurring invoice templates';

    public function handle(RecurringInvoiceService $recurringInvoiceService): int
    {
        $dueRecurringInvoices = RecurringInvoice::query()
            ->due()
            ->with(['organization', 'client'])
            ->get();

        foreach ($dueRecurringInvoices as $recurring) {
            $recurringInvoiceService->generateInvoice($recurring);
        }

        $this->info("Generated {$dueRecurringInvoices->count()} invoices from recurring templates.");

        return self::SUCCESS;
    }
}
