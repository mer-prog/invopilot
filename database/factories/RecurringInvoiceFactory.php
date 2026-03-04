<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Frequency;
use App\Models\Client;
use App\Models\Organization;
use App\Models\RecurringInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    protected $model = RecurringInvoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'frequency' => fake()->randomElement(Frequency::cases()),
            'next_issue_date' => fake()->dateTimeBetween('now', '+3 months'),
            'end_date' => fake()->optional(0.3)->dateTimeBetween('+6 months', '+2 years'),
            'items' => [
                [
                    'description' => fake()->sentence(3),
                    'quantity' => fake()->randomFloat(2, 1, 10),
                    'unit_price' => fake()->randomFloat(2, 100, 5000),
                ],
            ],
            'notes' => fake()->optional(0.3)->sentence(),
            'tax_rate' => fake()->randomElement([0, 5, 8, 10]),
            'is_active' => true,
            'last_generated_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
