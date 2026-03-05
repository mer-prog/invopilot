<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RecordPaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request): Response
    {
        $this->authorizeAction('viewAny', Invoice::class);

        $orgId = (int) session('current_organization_id');

        $query = Invoice::query()
            ->forOrganization($orgId)
            ->with('client:id,name,company')
            ->latest('issue_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->where('issue_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('issue_date', '<=', $request->input('to'));
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'desc');
            $query->reorder($request->input('sort'), $direction);
        }

        return Inertia::render('invoices/index', [
            'invoices' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'from', 'to', 'sort', 'direction']),
        ]);
    }

    public function create(): Response
    {
        $this->authorizeAction('create', Invoice::class);

        $orgId = (int) session('current_organization_id');
        $organization = Organization::query()->findOrFail($orgId);

        return Inertia::render('invoices/create', [
            'clients' => Client::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'company']),
            'defaultCurrency' => $organization->default_currency->value,
            'defaultPaymentTerms' => $organization->default_payment_terms,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorizeAction('create', Invoice::class);

        $orgId = (int) session('current_organization_id');
        $organization = Organization::query()->findOrFail($orgId);
        $validated = $request->validated();

        $invoice = $this->invoiceService->createInvoice(
            $organization,
            collect($validated)->except('items')->toArray(),
            $validated['items'],
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('common.successfully_created', ['item' => __('invoices.singular')]));
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorizeAction('view', $invoice);

        $invoice->load(['client', 'items', 'payments']);

        return Inertia::render('invoices/show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorizeAction('update', $invoice);

        $orgId = (int) session('current_organization_id');

        $invoice->load('items');

        return Inertia::render('invoices/edit', [
            'invoice' => $invoice,
            'clients' => Client::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'company']),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeAction('update', $invoice);

        $validated = $request->validated();

        $this->invoiceService->updateInvoice(
            $invoice,
            collect($validated)->except('items')->toArray(),
            $validated['items'],
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('common.successfully_updated', ['item' => __('invoices.singular')]));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorizeAction('delete', $invoice);

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', __('common.successfully_deleted', ['item' => __('invoices.singular')]));
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->authorizeAction('send', $invoice);

        $this->invoiceService->sendInvoice($invoice);

        return back()->with('success', __('invoices.sent_successfully'));
    }

    public function markPaid(RecordPaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeAction('markPaid', $invoice);

        $this->invoiceService->recordPayment($invoice, $request->validated());

        return back()->with('success', __('invoices.payment_recorded'));
    }

    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $this->authorizeAction('view', $invoice);

        $invoice->load('items');
        $newInvoice = $this->invoiceService->duplicateInvoice($invoice);

        return redirect()->route('invoices.edit', $newInvoice)
            ->with('success', __('invoices.duplicated'));
    }

    public function pdf(Invoice $invoice, PdfService $pdfService): HttpResponse
    {
        $this->authorizeAction('view', $invoice);

        return $pdfService->downloadInvoicePdf($invoice);
    }

    private function authorizeAction(string $ability, Invoice|string $invoiceOrClass): void
    {
        if (is_string($invoiceOrClass)) {
            abort_unless(auth()->user()?->can($ability, $invoiceOrClass), 403);
        } else {
            abort_unless(auth()->user()?->can($ability, $invoiceOrClass), 403);
        }
    }
}
