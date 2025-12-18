<?php

namespace Database\Factories;

use App\Models\PageIdea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageIdea>
 */
class PageIdeaFactory extends Factory
{
    protected $model = PageIdea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->paragraph(),
            'sections' => [
                [
                    'title' => $this->faker->sentence(),
                    'description' => $this->faker->paragraph(),
                    'justification' => $this->faker->paragraph(),
                ],
                [
                    'title' => $this->faker->sentence(),
                    'description' => $this->faker->paragraph(),
                    'justification' => $this->faker->paragraph(),
                ],
            ],
            'message' => $this->faker->paragraph(),
        ];
    }
}
