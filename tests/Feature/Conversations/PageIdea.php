<?php

namespace Tests\Feature\Conversations;

use App\Models\User;
use Illuminate\Support\Str;

test('conversation can be created', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
        'organisation_id' => $organisation->id,
        'title' => 'New Conversation'
    ]);

    $response = $this->postJson('/api/conversations', [
        'title' => 'New Conversation'
    ]);

    $response->assertStatus(200);
});