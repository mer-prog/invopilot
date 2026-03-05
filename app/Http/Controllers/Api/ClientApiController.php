<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orgId = $this->resolveOrganizationId($request);

        $clients = Client::query()
            ->forOrganization($orgId)
            ->withCount('invoices')
            ->orderBy('name')
            ->paginate(15);

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): ClientResource
    {
        $orgId = $this->resolveOrganizationId($request);

        $client = Client::query()->create([
            ...$request->validated(),
            'organization_id' => $orgId,
        ]);

        return new ClientResource($client);
    }

    public function show(Request $request, Client $client): ClientResource
    {
        $this->ensureOwnership($request, $client);

        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->ensureOwnership($request, $client);

        $client->update($request->validated());

        return new ClientResource($client);
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->ensureOwnership($request, $client);

        $client->delete();

        return response()->json(null, 204);
    }

    private function resolveOrganizationId(Request $request): int
    {
        $user = $request->user();
        $organization = $user->organizations()->first();

        abort_unless($organization, 403, 'No organization found.');

        return $organization->id;
    }

    private function ensureOwnership(Request $request, Client $client): void
    {
        $orgId = $this->resolveOrganizationId($request);
        abort_unless($client->organization_id === $orgId, 403);
    }
}
