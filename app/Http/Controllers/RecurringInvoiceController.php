<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringInvoiceRequest;
use App\Http\Requests\UpdateRecurringInvoiceRequest;
use App\Models\Client;
use App\Models\RecurringInvoice;
use App\Services\RecurringInvoiceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RecurringInvoiceController extends Controller
{
    public function __construct(private RecurringInvoiceService $recurringInvoiceService) {}

    public function index(): Response
    {
        $this->authorizeAction('viewAny', RecurringInvoice::class);

        $orgId = (int) session('current_organization_id');

        $recurringInvoices = RecurringInvoice::query()
            ->forOrganization($orgId)
            ->with('client:id,name,company')
            ->latest()
            ->paginate(15);

        return Inertia::render('recurring-invoices/index', [
            'recurringInvoices' => $recurringInvoices,
        ]);
    }

    public function create(): Response
    {
        $this->authorizeAction('create', RecurringInvoice::class);

        $orgId = (int) session('current_organization_id');

        return Inertia::render('recurring-invoices/create', [
            'clients' => Client::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'company']),
        ]);
    }

    public function store(StoreRecurringInvoiceRequest $request): RedirectResponse
    {
        $this->authorizeAction('create', RecurringInvoice::class);

        $orgId = (int) session('current_organization_id');
        $validated = $request->validated();

        RecurringInvoice::query()->create([
            ...$validated,
            'organization_id' => $orgId,
            'is_active' => true,
        ]);

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('common.successfully_created', ['item' => __('recurring_invoices.singular')]));
    }

    public function edit(RecurringInvoice $recurringInvoice): Response
    {
        $this->authorizeAction('update', $recurringInvoice);

        $orgId = (int) session('current_organization_id');

        return Inertia::render('recurring-invoices/edit', [
            'recurringInvoice' => $recurringInvoice,
            'clients' => Client::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'company']),
        ]);
    }

    public function update(UpdateRecurringInvoiceRequest $request, RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $this->authorizeAction('update', $recurringInvoice);

        $recurringInvoice->update($request->validated());

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('common.successfully_updated', ['item' => __('recurring_invoices.singular')]));
    }

    public function destroy(RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $this->authorizeAction('delete', $recurringInvoice);

        $recurringInvoice->delete();

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('common.successfully_deleted', ['item' => __('recurring_invoices.singular')]));
    }

    public function toggleActive(RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $this->authorizeAction('update', $recurringInvoice);

        $recurringInvoice->update(['is_active' => ! $recurringInvoice->is_active]);

        $message = $recurringInvoice->is_active
            ? __('recurring_invoices.activated')
            : __('recurring_invoices.deactivated');

        return back()->with('success', $message);
    }

    private function authorizeAction(string $ability, RecurringInvoice|string $modelOrClass): void
    {
        if (is_string($modelOrClass)) {
            abort_unless(auth()->user()?->can($ability, $modelOrClass), 403);
        } else {
            abort_unless(auth()->user()?->can($ability, $modelOrClass), 403);
        }
    }
}
