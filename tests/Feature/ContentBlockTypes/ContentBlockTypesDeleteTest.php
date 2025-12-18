<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\ContentBlockType;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('owners and admins can delete content block types', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    $contentBlockType = ContentBlockType::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('content-block-types.destroy', $contentBlockType));
    
    $response->assertRedirect(route('content-block-types.index'))
        ->assertSessionHas('success', 'Content block type deleted successfully.');

    $this->assertDatabaseMissing('content_block_types', [
        'id' => $contentBlockType->id
    ]);
});

test('members cannot delete content block types', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    $organisation->users()->updateExistingPivot($user->id, ['role' => 'member']);
    
    $contentBlockType = ContentBlockType::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('content-block-types.destroy', $contentBlockType));
    
    $response->assertForbidden();

    $this->assertDatabaseHas('content_block_types', [
        'id' => $contentBlockType->id
    ]);
}); 