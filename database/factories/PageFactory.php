<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition()
    {
        $title = $this->faker->sentence(4);
        
        return [
            'website_id' => Website::factory(),
            'title' => $title,
            'description' => $this->faker->paragraph(),
            'slug' => Str::slug($title),
        ];
    }
} 