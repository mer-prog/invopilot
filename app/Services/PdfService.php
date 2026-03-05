<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    public function generateInvoicePdf(Invoice $invoice): string
    {
        $invoice->load(['organization', 'client', 'items']);

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'organization' => $invoice->organization,
            'client' => $invoice->client,
            'items' => $invoice->items->sortBy('sort_order'),
        ]);

        return $pdf->output();
    }

    public function downloadInvoicePdf(Invoice $invoice): Response
    {
        $invoice->load(['organization', 'client', 'items']);

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'organization' => $invoice->organization,
            'client' => $invoice->client,
            'items' => $invoice->items->sortBy('sort_order'),
        ]);

        $filename = $invoice->invoice_number.'.pdf';

        return $pdf->download($filename);
    }
}
