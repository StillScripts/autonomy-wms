<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organisation>
 */
class OrganisationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'personal_organisation' => false,
        ];
    }

    public function personal(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'personal_organisation' => true,
            ];
        });
    }

    public function superOrg(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_super_org' => true,
            ];  
        });
    } 
} 