<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\ContentBlockType;
use App\Models\Organisation;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_default_content_block_type()
    {
        $type = ContentBlockType::factory()->create();

        $this->assertDatabaseHas('content_block_types', [
            'id' => $type->id,
            'is_default' => true,
            'organisation_id' => null,
        ]);
    }

    public function test_can_create_organisation_specific_content_block_type()
    {
        $organisation = Organisation::factory()->create();
        $type = ContentBlockType::factory()
            ->forOrganisation($organisation->id)
            ->create();

        $this->assertDatabaseHas('content_block_types', [
            'id' => $type->id,
            'is_default' => false,
            'organisation_id' => $organisation->id,
        ]);
    }

    public function test_can_create_content_block_with_default_type()
    {
        $type = ContentBlockType::factory()->create();
        $page = Page::factory()->create();
        
        $block = ContentBlock::factory()->create([
            'content_block_type_id' => $type->id,
            'content' => [
                'heading' => 'Welcome to Our Site',
                'subheading' => 'Discover amazing content',
            ],
        ]);

        $this->assertDatabaseHas('content_blocks', [
            'id' => $block->id,
            'content_block_type_id' => $type->id,
            'content' => json_encode([
                'heading' => 'Welcome to Our Site',
                'subheading' => 'Discover amazing content',
            ])
        ]);

        $this->assertTrue($block->validateContent());
    }

    public function test_can_create_content_block_with_custom_content()
    {
        $type = ContentBlockType::factory()->create();
        $page = Page::factory()->create();
        
        $customContent = [
            'title' => 'Custom Title',
            'description' => 'Custom Description'
        ];

        $block = ContentBlock::factory()
            ->withCustomContent($customContent)
            ->create([
                'content_block_type_id' => $type->id,
            ]);

        $this->assertEquals($customContent, $block->content);
        $this->assertTrue($block->validateContent());
    }
} 