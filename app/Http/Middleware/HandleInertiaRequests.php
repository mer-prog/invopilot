<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => $locale,
            'translations' => $this->getTranslations($locale),
            'currentOrganization' => $this->getCurrentOrganization($request),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getTranslations(string $locale): array
    {
        $translations = [];
        $langPath = lang_path($locale);

        if (! File::isDirectory($langPath)) {
            return $translations;
        }

        foreach (File::files($langPath) as $file) {
            if ($file->getExtension() === 'php') {
                $key = $file->getFilenameWithoutExtension();
                $translations[$key] = require $file->getPathname();
            }
        }

        return $translations;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getCurrentOrganization(Request $request): ?array
    {
        $organization = $request->attributes->get('organization');

        if ($organization instanceof Organization) {
            return [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'logo_url' => $organization->logo_url,
                'default_currency' => $organization->default_currency,
                'invoice_prefix' => $organization->invoice_prefix,
                'default_payment_terms' => $organization->default_payment_terms,
            ];
        }

        return null;
    }
}
