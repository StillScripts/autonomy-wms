<?php

namespace Database\Factories;

use App\Models\ContentBlock;
use App\Models\ContentBlockType;
use App\Models\Organisation;
use App\Models\Page;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentBlockFactory extends Factory
{
    protected $model = ContentBlock::class;

    public function definition()
    {
        return [
            'content_block_type_id' => ContentBlockType::factory(),
            'content' => [
                'title' => $this->faker->sentence,
                'description' => $this->faker->paragraph,
            ],
            'description' => $this->faker->paragraph,
            'organisation_id' => Organisation::factory(),
            'website_id' => Website::factory(),
        ];
    }

    public function withCustomContent(array $content)
    {
        return $this->state(function (array $attributes) use ($content) {
            return [
                'content' => $content,
            ];
        });
    }

    public function organizationWide()
    {
        return $this->state(function (array $attributes) {
            return [
                'website_id' => null,
            ];
        });
    }

    public function forOrganisation(Organisation $organisation)
    {
        return $this->state(function (array $attributes) use ($organisation) {
            return [
                'organisation_id' => $organisation->id,
            ];
        });
    }

    public function forWebsite(Website $website)
    {
        return $this->state(function (array $attributes) use ($website) {
            return [
                'website_id' => $website->id,
                'organisation_id' => $website->organisation_id,
            ];
        });
    }
} 