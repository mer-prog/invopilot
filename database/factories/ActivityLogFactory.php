<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'sent', 'paid']),
            'subject_type' => Invoice::class,
            'subject_id' => fake()->randomNumber(3),
            'description' => fake()->sentence(),
            'metadata' => null,
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
