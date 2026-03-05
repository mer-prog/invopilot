<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organization;

class InvoiceService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createInvoice(Organization $organization, array $data, array $items): Invoice
    {
        $invoiceNumber = $organization->generateNextInvoiceNumber();

        $invoice = Invoice::query()->create([
            ...$data,
            'organization_id' => $organization->id,
            'invoice_number' => $invoiceNumber,
            'status' => InvoiceStatus::Draft,
            'currency' => $data['currency'] ?? $organization->default_currency->value,
        ]);

        $this->syncItems($invoice, $items);
        $this->recalculateTotals($invoice);

        return $invoice;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function updateInvoice(Invoice $invoice, array $data, array $items): Invoice
    {
        $invoice->update($data);

        $this->syncItems($invoice, $items);
        $this->recalculateTotals($invoice);

        return $invoice->fresh(['items', 'client']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $index => $itemData) {
            $quantity = (float) ($itemData['quantity'] ?? 0);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0);
            $taxRate = (float) ($itemData['tax_rate'] ?? 0);
            $amount = round($quantity * $unitPrice * (1 + $taxRate / 100), 2);

            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'description' => $itemData['description'] ?? '',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'amount' => $amount,
                'sort_order' => $index,
            ]);
        }
    }

    public function recalculateTotals(Invoice $invoice): void
    {
        $invoice->load('items');

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($invoice->items as $item) {
            $lineSubtotal = (float) $item->quantity * (float) $item->unit_price;
            $subtotal += $lineSubtotal;
            $taxAmount += $lineSubtotal * (float) $item->tax_rate / 100;
        }

        $discountAmount = (float) $invoice->discount_amount;
        $total = round($subtotal + $taxAmount - $discountAmount, 2);

        $invoice->update([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => $total,
        ]);
    }

    public function sendInvoice(Invoice $invoice): void
    {
        $invoice->update([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function markPaid(Invoice $invoice): void
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function cancelInvoice(Invoice $invoice): void
    {
        $invoice->update([
            'status' => InvoiceStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function duplicateInvoice(Invoice $invoice): Invoice
    {
        $organization = $invoice->organization;
        $newNumber = $organization->generateNextInvoiceNumber();

        $newInvoice = $invoice->replicate(['invoice_number', 'status', 'sent_at', 'paid_at', 'cancelled_at']);
        $newInvoice->invoice_number = $newNumber;
        $newInvoice->status = InvoiceStatus::Draft;
        $newInvoice->issue_date = now();
        $newInvoice->due_date = now()->addDays($organization->default_payment_terms);
        $newInvoice->save();

        foreach ($invoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return $newInvoice;
    }
}
