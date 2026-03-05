<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Client::class);

        $orgId = (int) session('current_organization_id');

        $clients = Client::query()
            ->forOrganization($orgId)
            ->withCount('invoices')
            ->withSum('invoices', 'total')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('clients/index', [
            'clients' => $clients,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('clients/create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        $orgId = (int) session('current_organization_id');

        Client::query()->create([
            ...$request->validated(),
            'organization_id' => $orgId,
        ]);

        return redirect()->route('clients.index')
            ->with('success', __('common.successfully_created', ['item' => __('clients.singular')]));
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load([
            'invoices' => fn ($query) => $query->latest('issue_date')->limit(20),
            'invoices.client',
        ]);

        return Inertia::render('clients/show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('clients/edit', [
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return redirect()->route('clients.show', $client)
            ->with('success', __('common.successfully_updated', ['item' => __('clients.singular')]));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', __('common.successfully_deleted', ['item' => __('clients.singular')]));
    }

    private function authorize(string $ability, Client|string $clientOrClass): void
    {
        if (is_string($clientOrClass)) {
            abort_unless(auth()->user()?->can($ability, $clientOrClass), 403);
        } else {
            abort_unless(auth()->user()?->can($ability, $clientOrClass), 403);
        }
    }
}
