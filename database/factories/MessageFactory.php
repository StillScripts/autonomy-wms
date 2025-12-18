<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'content' => $this->faker->paragraph(),
            'role' => $this->faker->randomElement(['user', 'assistant']),
        ];
    }

    public function user(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }

    public function assistant(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
        ]);
    }
}
