<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\StripeProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class StripeProductSeeder extends Seeder
{
    /**
     * Run the database seeds. This uses real products from two distinct Stripe 
     * sandbox accounts.
     */
    public function run(): Product|null
    {
        // Get the test organisation
        $organisation = Organisation::where('name', 'Test User\'s Organisation')->first();
        
        if (!$organisation) {
            Log::error('Test organisation not found. Please run the DatabaseSeeder first.');
            return null;
        }

        // Seed product types
        $productTypes = [
            [
                'id' => 1,
                'name' => 'Audiobook',
                'slug' => 'audiobook',
                'description' => 'Digital audio content',
                'created_at' => '2025-06-15 02:57:45',
                'updated_at' => '2025-06-15 02:57:45',
            ],
            [
                'id' => 2,
                'name' => 'E-Book',
                'slug' => 'e-book',
                'description' => 'Digital book content',
                'created_at' => '2025-06-15 02:57:45',
                'updated_at' => '2025-06-15 02:57:45',
            ],
            [
                'id' => 3,
                'name' => 'Bundle',
                'slug' => 'bundle',
                'description' => 'Combined product offering',
                'created_at' => '2025-06-15 02:57:45',
                'updated_at' => '2025-06-15 02:57:45',
            ],
            [
                'id' => 4,
                'name' => 'Video',
                'slug' => 'video',
                'description' => 'Digital video content',
                'created_at' => '2025-06-15 02:57:45',
                'updated_at' => '2025-06-15 02:57:45',
            ],
            [
                'id' => 5,
                'name' => 'Document',
                'slug' => 'document',
                'description' => 'Digital document content',
                'created_at' => '2025-06-15 02:57:45',
                'updated_at' => '2025-06-15 02:57:45',
            ],
        ];

        foreach ($productTypes as $type) {
            ProductType::updateOrCreate(
                ['id' => $type['id']],
                $type
            );
        }

        // Seed products
        $products = [
            [
                'id' => 1,
                'organisation_id' => $organisation->id,
                'name' => 'The Return of the King',
                'description' => 'The third book in the series',
                'price' => 9,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV1zyffUsg8St0',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 2,
                'organisation_id' => $organisation->id,
                'name' => 'The Two Towers',
                'description' => 'The second book in the series',
                'price' => 9,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV1y4Wo1i1mpo5',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 3,
                'organisation_id' => $organisation->id,
                'name' => 'The Fellowship of The Ring',
                'description' => 'The first book in the series',
                'price' => 9,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV1ybAUPViLULv',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 4,
                'organisation_id' => $organisation->id,
                'name' => 'The Return of the King',
                'description' => 'The third book in the series',
                'price' => 12.99,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV6v0xNkRGHw5c',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
            [
                'id' => 5,
                'organisation_id' => $organisation->id,
                'name' => 'The Two Towers',
                'description' => 'The second book in the series',
                'price' => 12.99,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV6vO7lYQMMDr9',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
            [
                'id' => 6,
                'organisation_id' => $organisation->id,
                'name' => 'The Fellowship of the Ring',
                'description' => 'First book of the series',
                'price' => 12.99,
                'currency' => 'aud',
                'active' => 1,
                'provider_type' => 'stripe',
                'provider_product_id' => 'prod_SV6ujnXtrtov8l',
                'metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['id' => $productData['id']],
                $productData
            );
        }

        // Seed stripe products
        $stripeProducts = [
            [
                'id' => 1,
                'product_id' => 1,
                'stripe_id' => 'prod_SV1zyffUsg8St0',
                'stripe_price_id' => 'price_1Ra1vLIJ7sKzLulWhNYWdDr6',
                'stripe_environment' => 'test',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'stripe_id' => 'prod_SV1y4Wo1i1mpo5',
                'stripe_price_id' => 'price_1Ra1uvIJ7sKzLulW2HrRyAmU',
                'stripe_environment' => 'test',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 3,
                'product_id' => 3,
                'stripe_id' => 'prod_SV1ybAUPViLULv',
                'stripe_price_id' => 'price_1Ra1uXIJ7sKzLulWChz2J1i0',
                'stripe_environment' => 'test',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:03',
                'updated_at' => '2025-06-15 02:58:03',
            ],
            [
                'id' => 4,
                'product_id' => 4,
                'stripe_id' => 'prod_SV6v0xNkRGHw5c',
                'stripe_price_id' => 'price_1Ra6hdROhK379dzVcCFkUePN',
                'stripe_environment' => 'live',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
            [
                'id' => 5,
                'product_id' => 5,
                'stripe_id' => 'prod_SV6vO7lYQMMDr9',
                'stripe_price_id' => 'price_1Ra6hFROhK379dzV1I9KxMby',
                'stripe_environment' => 'live',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
            [
                'id' => 6,
                'product_id' => 6,
                'stripe_id' => 'prod_SV6ujnXtrtov8l',
                'stripe_price_id' => 'price_1Ra6gvROhK379dzVRblYdMA8',
                'stripe_environment' => 'live',
                'stripe_metadata' => '[]',
                'created_at' => '2025-06-15 02:58:04',
                'updated_at' => '2025-06-15 02:58:04',
            ],
        ];

        foreach ($stripeProducts as $stripeProductData) {
            StripeProduct::updateOrCreate(
                ['id' => $stripeProductData['id']],
                $stripeProductData
            );
        }

        // Seed product type relationships
        $productTypeRelations = [
            ['product_id' => 4, 'product_type_id' => 1],
            ['product_id' => 5, 'product_type_id' => 1],
            ['product_id' => 6, 'product_type_id' => 1],
            ['product_id' => 1, 'product_type_id' => 1],
            ['product_id' => 2, 'product_type_id' => 1],
            ['product_id' => 3, 'product_type_id' => 1],
        ];

        foreach ($productTypeRelations as $relation) {
            $product = Product::find($relation['product_id']);
            $productType = ProductType::find($relation['product_type_id']);
            if ($product && $productType) {
                $product->productTypes()->syncWithoutDetaching([$productType->id]);
            }
        }

        Log::info('Stripe products and related data seeded successfully!');

        // Return "The Fellowship of the Ring" product for customer seeding
        return Product::find(6);
    }
} 