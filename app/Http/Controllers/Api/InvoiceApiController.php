<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Organization;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceApiController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orgId = $this->resolveOrganizationId($request);

        $query = Invoice::query()
            ->forOrganization($orgId)
            ->with('client:id,name,company')
            ->latest('issue_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return InvoiceResource::collection($query->paginate(15));
    }

    public function store(StoreInvoiceRequest $request): InvoiceResource
    {
        $orgId = $this->resolveOrganizationId($request);
        $organization = Organization::query()->findOrFail($orgId);
        $validated = $request->validated();

        $invoice = $this->invoiceService->createInvoice(
            $organization,
            collect($validated)->except('items')->toArray(),
            $validated['items'],
        );

        $invoice->load(['client', 'items']);

        return new InvoiceResource($invoice);
    }

    public function show(Request $request, Invoice $invoice): InvoiceResource
    {
        $this->ensureOwnership($request, $invoice);

        $invoice->load(['client', 'items']);

        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $this->ensureOwnership($request, $invoice);

        $validated = $request->validated();

        $this->invoiceService->updateInvoice(
            $invoice,
            collect($validated)->except('items')->toArray(),
            $validated['items'],
        );

        $invoice->load(['client', 'items']);

        return new InvoiceResource($invoice->fresh());
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $this->ensureOwnership($request, $invoice);

        $invoice->delete();

        return response()->json(null, 204);
    }

    private ?int $resolvedOrganizationId = null;

    private function resolveOrganizationId(Request $request): int
    {
        if ($this->resolvedOrganizationId !== null) {
            return $this->resolvedOrganizationId;
        }

        $user = $request->user();
        $organization = $user->organizations()->first();

        abort_unless($organization, 403, 'No organization found.');

        return $this->resolvedOrganizationId = $organization->id;
    }

    private function ensureOwnership(Request $request, Invoice $invoice): void
    {
        $orgId = $this->resolveOrganizationId($request);
        abort_unless($invoice->organization_id === $orgId, 403);
    }
}
