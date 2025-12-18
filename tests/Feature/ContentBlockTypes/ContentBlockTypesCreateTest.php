<?php

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('content block type can be created by a valid user', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $name = 'FAQ Block Type';
    $expectedSlug = Str::slug($name);

    $response = $this
        ->actingAs($user)
        ->from('/content-block-types/create')
        ->post('/content-block-types', [
            'name' => $name,
            'is_default' => false,
            'fields' => [
                ['label' => 'Question', 'type' => 'text'],
                ['label' => 'Answer', 'type' => 'textarea'],
            ],
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('content-block-types.show', [
            'content_block_type' => $organisation->contentBlockTypes()->latest()->first()
        ]));

    $this->assertDatabaseHas('content_block_types', [
        'name' => $name,
        'slug' => $expectedSlug,
        'organisation_id' => $organisation->id,
    ]);
});

test('content block type cannot be created if user is not logged in', function () {
    $response = $this->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Question', 'type' => 'text'],
        ],
    ]);

    $response->assertRedirect(route('login'));
});

test('content block type cannot be created if user is just a member', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $organisation->users()->updateExistingPivot($user->id, ['role' => 'member']);
    
    $response = $this->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [ 
            ['label' => 'Question', 'type' => 'text'],
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseMissing('content_block_types', [
        'name' => 'Invalid Block Type',
        'slug' => Str::slug('Invalid Block Type'),
        'organisation_id' => $organisation->id,
    ]);
});


test('cannot create duplicate content block type slugs in same organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // Create first block type
    $this->actingAs($user)->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Question', 'type' => 'text'],
        ],
    ]);

    // Attempt to create second block type with same name
    $response = $this->actingAs($user)->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Different Question', 'type' => 'text'],
        ],
    ]);

    $response->assertSessionHasErrors(['error']);
    
    // Assert only one block type exists with this slug
    $this->assertEquals(1, $organisation->contentBlockTypes()
        ->where('slug', Str::slug('FAQ Block Type'))
        ->count());
});

test('can create same content block type slug in different organisations', function () {
    ['user' => $user, 'organisation' => $organisation1] = $this->createUserWithOrganisation();

    $organisation2 = Organisation::factory()->create();
    $user->organisations()->attach([
        $organisation2->id => ['role' => 'owner']
    ]);

    // Create block type in first organisation
    $user->switchOrganisation($organisation1);
    $response1 = $this->actingAs($user)->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Question', 'type' => 'text'],
        ],
    ]);
    $response1->assertSessionHasNoErrors();

    // Create same block type in second organisation
    $user->switchOrganisation($organisation2);
    $response2 = $this->actingAs($user)->post('/content-block-types', [
        'name' => 'FAQ Block Type',
        'is_default' => false,
        'fields' => [
            ['label' => 'Question', 'type' => 'text'],
        ],
    ]);
    $response2->assertSessionHasNoErrors();

    // Assert both block types exist with same slug in different orgs
    $slug = Str::slug('FAQ Block Type');
    $this->assertEquals(1, $organisation1->contentBlockTypes()->where('slug', $slug)->count());
    $this->assertEquals(1, $organisation2->contentBlockTypes()->where('slug', $slug)->count());
});

test('cannot create content block type with invalid field type', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $response = $this->actingAs($user)->post('/content-block-types', [
        'name' => 'Invalid Block Type', 
        'fields' => [
            ['label' => 'Invalid Field', 'type' => 'invalid']
        ]
    ]);

    $response->assertRedirect();

    $this->assertDatabaseMissing('content_block_types', [
        'name' => 'Invalid Block Type',
        'slug' => Str::slug('Invalid Block Type'),
        'organisation_id' => $organisation->id,
    ]);
});
