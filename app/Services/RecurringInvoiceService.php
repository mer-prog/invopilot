<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Frequency;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use Carbon\CarbonInterface;

class RecurringInvoiceService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function generateInvoice(RecurringInvoice $recurringInvoice): Invoice
    {
        $organization = $recurringInvoice->organization;

        $items = collect($recurringInvoice->items)->map(fn (array $item): array => [
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'tax_rate' => (float) ($recurringInvoice->tax_rate ?? 0),
        ])->toArray();

        $invoice = $this->invoiceService->createInvoice(
            $organization,
            [
                'client_id' => $recurringInvoice->client_id,
                'issue_date' => $recurringInvoice->next_issue_date,
                'due_date' => $recurringInvoice->next_issue_date->addDays(
                    $organization->default_payment_terms,
                ),
                'notes' => $recurringInvoice->notes,
            ],
            $items,
        );

        $nextDate = $this->calculateNextDate(
            $recurringInvoice->next_issue_date,
            $recurringInvoice->frequency,
        );

        $recurringInvoice->update([
            'next_issue_date' => $nextDate,
            'last_generated_at' => now(),
        ]);

        if ($recurringInvoice->end_date && $nextDate->isAfter($recurringInvoice->end_date)) {
            $recurringInvoice->update(['is_active' => false]);
        }

        return $invoice;
    }

    private function calculateNextDate(CarbonInterface $current, Frequency $frequency): CarbonInterface
    {
        return match ($frequency) {
            Frequency::Weekly => $current->addWeek(),
            Frequency::Biweekly => $current->addWeeks(2),
            Frequency::Monthly => $current->addMonth(),
            Frequency::Quarterly => $current->addMonths(3),
            Frequency::Yearly => $current->addYear(),
        };
    }
}
