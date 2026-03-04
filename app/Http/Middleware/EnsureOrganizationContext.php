<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $organizationId = session('current_organization_id');

        if (! $organizationId) {
            $organization = $user->organizations()->first();

            if ($organization) {
                session(['current_organization_id' => $organization->id]);
                $organizationId = $organization->id;
            }
        }

        if ($organizationId) {
            $organization = Organization::query()->find($organizationId);

            if ($organization && $user->organizations()->where('organizations.id', $organizationId)->exists()) {
                app()->instance('current_organization', $organization);
                $request->attributes->set('organization', $organization);
            } else {
                session()->forget('current_organization_id');
            }
        }

        return $next($request);
    }
}
