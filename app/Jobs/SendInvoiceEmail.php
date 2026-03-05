<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceSentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInvoiceEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        $this->invoice->load('client');

        if (! $this->invoice->client?->email) {
            return;
        }

        $this->invoice->client->notify(new InvoiceSentNotification($this->invoice));
    }
}
