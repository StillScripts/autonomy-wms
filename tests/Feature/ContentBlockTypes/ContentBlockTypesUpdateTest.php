<?php

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('content block type can be updated', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // First create a content block type
    $this->actingAs($user)->post('/content-block-types', [
        'name' => 'Original Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Original Field', 'type' => 'text'],
        ],
    ]);

    $contentBlockType = $organisation->contentBlockTypes()->latest()->first();
    $newName = 'Updated Block Type';

    $response = $this
        ->actingAs($user)
        ->from(route('content-block-types.edit', ['content_block_type' => $contentBlockType]))
        ->put(route('content-block-types.update', ['content_block_type' => $contentBlockType]), [
            'name' => $newName,
            'is_default' => true,
            'fields' => [
                ['label' => 'Updated Field', 'type' => 'textarea'],
            ],
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('content-block-types.show', [
            'content_block_type' => 'updated-block-type'
        ]));

    // Verify the content block type was updated correctly
    $this->assertDatabaseHas('content_block_types', [
        'id' => $contentBlockType->id,
        'name' => $newName,
        'slug' => 'updated-block-type',
        'organisation_id' => $organisation->id,
        'is_default' => true,
    ]);
});

test('cannot update content block type to duplicate slug in same organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // Create two different block types
    $this->actingAs($user)->post('/content-block-types', [
        'name' => 'First Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Field 1', 'type' => 'text'],
        ],
    ]);

    $this->actingAs($user)->post('/content-block-types', [
        'name' => 'Second Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Field 2', 'type' => 'text'],
        ],
    ]);

    $firstBlockType = $organisation->contentBlockTypes()->where('name', 'First Block Type')->first();
    $secondBlockType = $organisation->contentBlockTypes()->where('name', 'Second Block Type')->first();

    // Attempt to update second block type to have same name as first
    $response = $this
        ->actingAs($user)
        ->put(route('content-block-types.update', ['content_block_type' => $secondBlockType]), [
            'name' => 'First Block Type',
            'is_default' => false,
            'fields' => [
                ['label' => 'Field 2', 'type' => 'text'],
            ],
        ]);

    // Since the slug does change on update, this should fail
    $response->assertSessionHasErrors();
    

});
