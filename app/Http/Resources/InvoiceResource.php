<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'footer' => $this->footer,
            'sent_at' => $this->sent_at,
            'paid_at' => $this->paid_at,
            'cancelled_at' => $this->cancelled_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'items' => $this->whenLoaded('items'),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
