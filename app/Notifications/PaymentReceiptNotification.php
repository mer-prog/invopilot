<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public Invoice $invoice,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.payment_receipt_subject', ['number' => $this->invoice->invoice_number]))
            ->greeting(__('emails.invoice_greeting', ['name' => $notifiable->name ?? '']))
            ->line(__('emails.payment_receipt_body', [
                'amount' => $this->payment->amount,
                'number' => $this->invoice->invoice_number,
            ]));
    }
}
