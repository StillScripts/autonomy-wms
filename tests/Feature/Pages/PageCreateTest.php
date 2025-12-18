<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use App\Models\Website;
use App\Models\Organisation;
use App\Models\ContentBlockType;
use App\Models\ContentBlock;
use Illuminate\Support\Str;

test('page can be created', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $website = Website::factory()->create([
        'organisation_id' => $organisation->id,
        'title' => 'Test Website',
        'domain' => 'test.com',
    ]);

    $title = 'About Us';
    $expectedSlug = Str::slug($title);

    $response = $this
        ->actingAs($user)
        ->from("/websites/{$website->id}/pages/create")
        ->post("/websites/{$website->id}/pages", [
            'title' => $title,
            'description' => 'Learn more about our company',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('websites.pages.edit', [$website, 'about-us']));

    $this->assertDatabaseHas('pages', [
        'website_id' => $website->id,
        'title' => $title,
        'slug' => $expectedSlug,
        'description' => 'Learn more about our company',
    ]);
});

test('page can be created with content blocks', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $website = Website::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    // Create a section heading block type
    $blockType = ContentBlockType::factory()->create([
        'organisation_id' => $organisation->id,
        'name' => 'Section Heading',
        'slug' => 'section-heading',
        'fields' => [
            ['label' => 'Heading', 'type' => 'text'],
            ['label' => 'Subheading', 'type' => 'textarea'],
        ],
    ]);
    $contentBlock = ContentBlock::factory()->create([
        'content_block_type_id' => $blockType->id,
        'content' => [
            'heading' => 'Welcome to Our Site',
            'subheading' => 'Discover amazing content',
        ],
    ]);

    $response = $this
        ->actingAs($user)
        ->from("/websites/{$website->id}/pages/create")
        ->post("/websites/{$website->id}/pages", [
            'title' => 'Home Page',
            'description' => 'Welcome to our website',
            'contentBlocks' => [
                [
                    'content_block_type_id' => $blockType->id,
                    'content_block_id' => $contentBlock->id,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $page = $website->pages()->where('title', 'Home Page')->first();
    
    $this->assertNotNull($page);
    $this->assertDatabaseHas('content_block_page', [
        'page_id' => $page->id,
        'content_block_id' => $contentBlock->id,
    ]);
});

test('cannot create page with duplicate slug in same website', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $website = Website::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    $this->actingAs($user)->post("/websites/{$website->id}/pages", [
        'title' => 'About Us',
        'description' => 'First about page',
    ]);

    $response = $this->actingAs($user)->post("/websites/{$website->id}/pages", [
        'title' => 'About Us',
        'description' => 'Second about page',
    ]);

    $response->assertSessionHasErrors(['error']);
    
    $this->assertEquals(1, $website->pages()
        ->where('slug', Str::slug('About Us'))
        ->count());
});

test('can create same page slug in different websites', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $website1 = Website::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    $website2 = Website::factory()->create([
        'organisation_id' => $organisation->id,
    ]);

    // Create page in first website
    $response1 = $this->actingAs($user)->post("/websites/{$website1->id}/pages", [
        'title' => 'About Us',
        'description' => 'About page for first website',
    ]);
    $response1->assertSessionHasNoErrors();

    // Create same titled page in second website
    $response2 = $this->actingAs($user)->post("/websites/{$website2->id}/pages", [
        'title' => 'About Us',
        'description' => 'About page for second website',
    ]);
    $response2->assertSessionHasNoErrors();

    // Assert both pages exist with same slug in different websites
    $slug = Str::slug('About Us');
    $this->assertEquals(1, $website1->pages()->where('slug', $slug)->count());
    $this->assertEquals(1, $website2->pages()->where('slug', $slug)->count());
});
