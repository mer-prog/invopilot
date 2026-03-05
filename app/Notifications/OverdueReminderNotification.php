<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueReminderNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject(__('emails.overdue_subject', ['number' => $this->invoice->invoice_number]))
            ->greeting(__('emails.invoice_greeting', ['name' => $notifiable->name ?? '']))
            ->line(__('emails.overdue_body', [
                'number' => $this->invoice->invoice_number,
                'due_date' => $this->invoice->due_date->format('Y-m-d'),
            ]));
    }
}
