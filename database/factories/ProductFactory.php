<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\Product;
use App\Models\PrivateFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'active' => true,
            'provider_type' => 'stripe',
            'provider_product_id' => $this->faker->uuid(),
            'metadata' => [],
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function stripe(): self
    {
        return $this->state(fn (array $attributes) => [
            'provider_type' => 'stripe',
        ]);
    }

    /**
     * Attach private files to the product after creation
     */
    public function withPrivateFiles(int $count = 1, array $fileAttributes = []): self
    {
        return $this->afterCreating(function (Product $product) use ($count, $fileAttributes) {
            $privateFiles = PrivateFile::factory()
                ->count($count)
                ->create(array_merge(
                    ['organisation_id' => $product->organisation_id],
                    $fileAttributes
                ));

            foreach ($privateFiles as $index => $privateFile) {
                $product->privateFiles()->attach($privateFile, [
                    'sort_order' => $index,
                    'metadata' => ['attached_at' => now()->toDateTimeString()],
                ]);
            }
        });
    }

    /**
     * Attach ebook files to the product after creation
     */
    public function withEbooks(int $count = 1): self
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $ebooks = PrivateFile::factory()
                ->count($count)
                ->ebook()
                ->create(['organisation_id' => $product->organisation_id]);

            foreach ($ebooks as $index => $ebook) {
                $product->privateFiles()->attach($ebook, [
                    'sort_order' => $index,
                    'metadata' => ['attached_at' => now()->toDateTimeString()],
                ]);
            }
        });
    }

    /**
     * Attach audiobook files to the product after creation
     */
    public function withAudiobooks(int $count = 1): self
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $audiobooks = PrivateFile::factory()
                ->count($count)
                ->audiobook()
                ->create(['organisation_id' => $product->organisation_id]);

            foreach ($audiobooks as $index => $audiobook) {
                $product->privateFiles()->attach($audiobook, [
                    'sort_order' => $index,
                    'metadata' => ['attached_at' => now()->toDateTimeString()],
                ]);
            }
        });
    }
} 