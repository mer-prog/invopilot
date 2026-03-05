<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Events\InvoiceOverdue;
use App\Events\InvoiceSent;
use App\Events\PaymentRecorded;
use App\Models\ActivityLog;
use App\Models\Invoice;

class LogActivity
{
    public function handle(InvoiceCreated|InvoiceSent|PaymentRecorded|InvoiceOverdue $event): void
    {
        $invoice = $this->getInvoice($event);
        $action = $this->getAction($event);
        $metadata = $this->getMetadata($event);

        ActivityLog::query()->create([
            'organization_id' => $invoice->organization_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->id,
            'description' => $this->getDescription($event, $invoice),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    private function getInvoice(InvoiceCreated|InvoiceSent|PaymentRecorded|InvoiceOverdue $event): Invoice
    {
        return $event->invoice;
    }

    private function getAction(InvoiceCreated|InvoiceSent|PaymentRecorded|InvoiceOverdue $event): string
    {
        return match ($event::class) {
            InvoiceCreated::class => 'created',
            InvoiceSent::class => 'sent',
            PaymentRecorded::class => 'payment_recorded',
            InvoiceOverdue::class => 'overdue',
        };
    }

    private function getDescription(InvoiceCreated|InvoiceSent|PaymentRecorded|InvoiceOverdue $event, Invoice $invoice): string
    {
        return match ($event::class) {
            InvoiceCreated::class => __('activity.invoice_created', ['number' => $invoice->invoice_number]),
            InvoiceSent::class => __('activity.invoice_sent', ['number' => $invoice->invoice_number]),
            PaymentRecorded::class => __('activity.payment_recorded', [
                'amount' => $event->payment->amount,
                'number' => $invoice->invoice_number,
            ]),
            InvoiceOverdue::class => __('activity.invoice_overdue', ['number' => $invoice->invoice_number]),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getMetadata(InvoiceCreated|InvoiceSent|PaymentRecorded|InvoiceOverdue $event): array
    {
        if ($event instanceof PaymentRecorded) {
            return [
                'payment_id' => $event->payment->id,
                'amount' => (float) $event->payment->amount,
                'method' => $event->payment->method->value,
            ];
        }

        return [];
    }
}
