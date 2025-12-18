<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\ContentBlockType;
use App\Models\ContentBlock;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Tests\Traits\WithTestOrganisation;
use Illuminate\Support\Facades\Storage;

uses(WithTestOrganisation::class);

test('content block can be created by a valid user', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $contentBlockType = ContentBlockType::factory()->forOrganisation($organisation)->create();

    $data = [
        'content_block_type_id' => $contentBlockType->id,
        'content' => [
            'title' => 'Finance Faqs',
            'description' => 'Explore our finance faqs',
        ],
        'description' => 'This content is for a section on finance faqs',
        'organisation_id' => $organisation->id,
    ];

    $response = $this
        ->actingAs($user)
        ->from('/content-blocks/create')
        ->post('/content-blocks', $data);

    $contentBlock = ContentBlock::where([
        'content_block_type_id' => $contentBlockType->id,
        'organisation_id' => $organisation->id,
    ])->latest()->first();

    expect($contentBlock)->not->toBeNull();



    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('content-blocks.show', $contentBlock));

    $this->assertDatabaseHas('content_blocks', [
        'id' => $contentBlock->id,
        'content_block_type_id' => $contentBlockType->id,
        'organisation_id' => $organisation->id,
        'description' => 'This content is for a section on finance faqs',
    ]);

    $this->assertEquals([
        'title' => 'Finance Faqs',
        'description' => 'Explore our finance faqs',
    ], $contentBlock->content);
});

test('content block handles file uploads and generates S3 urls', function () {
    Storage::fake('s3');
    
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $contentBlockType = ContentBlockType::factory()->forOrganisation($organisation)->create([
        'fields' => [
            ['label' => 'Title', 'type' => 'text', 'slug' => 'title'],
            ['label' => 'Image', 'type' => 'file', 'slug' => 'image'],
        ],
    ]);
    
    $file = UploadedFile::fake()->image('test-image.jpg');
    
    $response = $this
        ->actingAs($user)
        ->from('/content-blocks/create')
        ->post('/content-blocks', [
            'content_block_type_id' => $contentBlockType->id,
            'content' => [
                'title' => 'Test Block',
                'image' => null,
            ],
        ], [
            'content.image' => $file
        ]);

    $response->assertSessionHasNoErrors();

    $contentBlock = ContentBlock::where([
        'content_block_type_id' => $contentBlockType->id,
        'organisation_id' => $organisation->id,
    ])->latest()->first();

    expect($contentBlock)->not->toBeNull();
    

    expect($contentBlock->content)
        ->toHaveKey('title')
        ->toHaveKey('image');

    Storage::disk('s3')->assertExists($contentBlock->content['image']);

}); 

