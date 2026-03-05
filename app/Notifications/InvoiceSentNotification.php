<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $pdfService = app(PdfService::class);
        $pdfContent = $pdfService->generateInvoicePdf($invoice);

        return (new MailMessage)
            ->subject(__('emails.invoice_subject', ['number' => $invoice->invoice_number]))
            ->greeting(__('emails.invoice_greeting', ['name' => $notifiable->name ?? '']))
            ->line(__('emails.invoice_body', [
                'number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'currency' => $invoice->currency->value,
                'due_date' => $invoice->due_date->format('Y-m-d'),
            ]))
            ->attachData($pdfContent, $invoice->invoice_number.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
