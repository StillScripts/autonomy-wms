<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ThirdPartyProvider>
 */
class ThirdPartyProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Stripe',
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
