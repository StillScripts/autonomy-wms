<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Audiobook',
                'description' => 'Digital audio content',
            ],
            [
                'name' => 'E-Book',
                'description' => 'Digital book content',
            ],
            [
                'name' => 'Bundle',
                'description' => 'Combined product offering',
            ],
            [
                'name' => 'Video',
                'description' => 'Digital video content',
            ],
            [
                'name' => 'Document',
                'description' => 'Digital document content',
            ],
        ];

        foreach ($types as $type) {
            ProductType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
