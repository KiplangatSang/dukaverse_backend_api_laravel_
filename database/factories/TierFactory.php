<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tier>
 */
class TierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ownerable_id' => 1, // set default ownerable id; override in specific tests
            'ownerable_type' => 'App\\Models\\User', // default ownerable type; override as needed
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'price' => 0.0, // Added default price to satisfy NOT NULL constraint
            'billing_duration' => 'month', // default valid billing duration for tests
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
