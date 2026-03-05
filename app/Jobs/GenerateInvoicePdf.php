<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function handle(PdfService $pdfService): void
    {
        $pdfService->generateInvoicePdf($this->invoice);
    }
}
