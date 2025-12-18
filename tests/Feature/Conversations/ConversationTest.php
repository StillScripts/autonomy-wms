<?php

namespace Tests\Feature\Conversations;

use Tests\Traits\WithTestOrganisation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PageIdea;
use App\Services\AIService;
use Mockery;

uses(WithTestOrganisation::class);

test('user can create a conversation and generate page ideas', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // Mock the AIService to return predictable responses
    $this->mock(AIService::class, function ($mock) {
        $mock->shouldReceive('processResponse')
            ->andReturnUsing(function () {
                $pageIdea = PageIdea::factory()->create();
                $message = Message::factory()->assistant()->create([
                    'object_type' => PageIdea::class,
                    'object_id' => $pageIdea->id
                ]);
                return $message;
            });
    });

    // 1. Create a conversation
    $response = $this->actingAs($user)
        ->post('/conversations');

    $response->assertStatus(200);
    $conversation = Conversation::first();
    $this->assertNotNull($conversation);

    // 2. Send first message to generate initial PageIdea
    $response = $this->actingAs($user)
        ->post("/conversations/{$conversation->id}/messages", [
            'content' => 'Create a page about AI'
        ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'user_message',
        'ai_response'
    ]);

    // Verify first PageIdea was created
    $this->assertDatabaseHas('page_ideas', [
        'title' => $conversation->messages()->where('role', 'assistant')->first()->object->title
    ]);

    // 3. Send feedback message to generate updated PageIdea
    $response = $this->actingAs($user)
        ->post("/conversations/{$conversation->id}/messages", [
            'content' => 'Make it more technical'
        ]);

    $response->assertStatus(200);

    // Verify second PageIdea was created
    $this->assertDatabaseCount('page_ideas', 2);
    $this->assertDatabaseCount('messages', 4); // 2 user messages + 2 AI responses
});
