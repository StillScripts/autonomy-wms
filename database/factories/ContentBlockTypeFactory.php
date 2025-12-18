<?php

namespace Database\Factories;

use App\Models\ContentBlockType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class ContentBlockTypeFactory extends Factory
{
    protected $model = ContentBlockType::class;

    public function definition()
    {
        $name = $this->faker->word;
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'fields' => [
                ['label' => 'Title', 'type' => 'text'],
                ['label' => 'Description', 'type' => 'textarea'],
            ],
            'organisation_id' => null, // Default block type
            'is_default' => true,
        ];
    }

    public function forOrganisation($organisationId)
    {
        return $this->state(function (array $attributes) use ($organisationId) {
            return [
                'organisation_id' => $organisationId,
                'is_default' => false,
            ];
        });
    }
} 