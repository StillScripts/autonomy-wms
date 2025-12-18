<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Membership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organisation_id' => Organisation::factory(),
            'role' => $this->faker->randomElement(['owner', 'admin', 'member']),
        ];
    }

    /**
     * Indicate that the membership is for an owner.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }

    /**
     * Indicate that the membership is for an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the membership is for a member.
     */
    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'member',
        ]);
    }
} 