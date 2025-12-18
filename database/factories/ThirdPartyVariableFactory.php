<?php

namespace Database\Factories;

use App\Models\ThirdPartyProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ThirdPartyVariable>
 */
class ThirdPartyVariableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'is_secret' => $this->faker->boolean(30),
            'third_party_provider_id' => ThirdPartyProvider::factory(),
        ];
    }
}
