<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function edit(): Response
    {
        $orgId = (int) session('current_organization_id');
        $organization = Organization::query()->findOrFail($orgId);

        return Inertia::render('settings/organization', [
            'organization' => $organization,
        ]);
    }

    public function update(UpdateOrganizationRequest $request): RedirectResponse
    {
        $orgId = (int) session('current_organization_id');
        $organization = Organization::query()->findOrFail($orgId);

        $organization->update($request->validated());

        return back()->with('success', __('common.successfully_updated', ['item' => __('settings.organization')]));
    }
}
