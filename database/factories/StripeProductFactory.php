<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StripeProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class StripeProductFactory extends Factory
{
    protected $model = StripeProduct::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stripe_id' => 'prod_' . $this->faker->uuid(),
            'stripe_price_id' => 'price_' . $this->faker->uuid(),
            'stripe_environment' => $this->faker->randomElement(['test', 'live']),
            'stripe_metadata' => [],
        ];
    }

    public function test(): self
    {
        return $this->state(fn (array $attributes) => [
            'stripe_environment' => 'test',
        ]);
    }

    public function live(): self
    {
        return $this->state(fn (array $attributes) => [
            'stripe_environment' => 'live',
        ]);
    }
} 