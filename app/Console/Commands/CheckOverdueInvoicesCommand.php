<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceOverdue;
use App\Models\Invoice;
use App\Notifications\OverdueReminderNotification;
use Illuminate\Console\Command;

class CheckOverdueInvoicesCommand extends Command
{
    protected $signature = 'invoices:check-overdue';

    protected $description = 'Mark sent invoices past due date as overdue and send reminders';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->where('status', InvoiceStatus::Sent)
            ->where('due_date', '<', now()->startOfDay())
            ->with('client')
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => InvoiceStatus::Overdue]);

            InvoiceOverdue::dispatch($invoice);

            if ($invoice->client?->email) {
                $invoice->client->notify(new OverdueReminderNotification($invoice));
            }
        }

        $this->info("Marked {$invoices->count()} invoices as overdue.");

        return self::SUCCESS;
    }
}
