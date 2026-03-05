<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentRecorded;
use App\Notifications\PaymentReceiptNotification;

class SendPaymentReceipt
{
    public function handle(PaymentRecorded $event): void
    {
        $invoice = $event->invoice;
        $invoice->load('client');

        if (! $invoice->client?->email) {
            return;
        }

        $invoice->client->notify(new PaymentReceiptNotification($event->payment, $invoice));
    }
}
