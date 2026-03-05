<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\InvoiceStatus;
use App\Events\PaymentRecorded;

class UpdateInvoiceStatus
{
    public function handle(PaymentRecorded $event): void
    {
        $invoice = $event->invoice;
        $totalPaid = (float) $invoice->payments()->sum('amount');

        if ($totalPaid >= (float) $invoice->total) {
            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
            ]);
        }
    }
}
