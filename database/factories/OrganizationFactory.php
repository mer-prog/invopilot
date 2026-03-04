<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'owner_id' => User::factory(),
            'logo_url' => null,
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'tax_id' => fake()->numerify('T#############'),
            'default_currency' => fake()->randomElement(Currency::cases()),
            'invoice_prefix' => fake()->randomElement(['INV', 'BILL', 'RCP']),
            'invoice_next_number' => 1,
            'default_payment_terms' => fake()->randomElement([15, 30, 45, 60]),
            'default_notes' => null,
        ];
    }

    public function withJpy(): static
    {
        return $this->state(fn (array $attributes): array => [
            'default_currency' => Currency::Jpy,
        ]);
    }
}
