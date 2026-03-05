<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'string', 'url', 'max:500'],
            'default_currency' => ['required', 'string', Rule::enum(Currency::class)],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'default_payment_terms' => ['required', 'integer', 'min:1', 'max:365'],
            'default_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
