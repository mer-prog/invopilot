<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional(0.7)->numerify('REF-######'),
            'paid_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
