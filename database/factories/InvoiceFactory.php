<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-6 months', 'now');
        $dueDate = (clone $issueDate)->modify('+30 days');
        $subtotal = fake()->randomFloat(2, 100, 50000);
        $taxAmount = round($subtotal * 0.1, 2);
        $discountAmount = fake()->optional(0.2)->randomFloat(2, 0, $subtotal * 0.1) ?? 0;
        $total = $subtotal + $taxAmount - $discountAmount;

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => fake()->unique()->numerify('INV-#####'),
            'status' => fake()->randomElement(InvoiceStatus::cases()),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => round($total, 2),
            'currency' => Currency::Usd,
            'notes' => fake()->optional(0.3)->sentence(),
            'footer' => null,
            'sent_at' => null,
            'paid_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Draft,
            'sent_at' => null,
            'paid_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Sent,
            'sent_at' => fake()->dateTimeBetween($attributes['issue_date'], 'now'),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Paid,
            'sent_at' => fake()->dateTimeBetween($attributes['issue_date'], 'now'),
            'paid_at' => fake()->dateTimeBetween($attributes['issue_date'], 'now'),
        ]);
    }

    public function overdue(): static
    {
        $issueDate = fake()->dateTimeBetween('-6 months', '-2 months');
        $dueDate = (clone $issueDate)->modify('+30 days');

        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Overdue,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'sent_at' => $issueDate,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Cancelled,
            'cancelled_at' => fake()->dateTimeBetween($attributes['issue_date'], 'now'),
        ]);
    }
}
