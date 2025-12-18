<?php

namespace Tests\Feature\Websites;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('website can be created', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $title = 'My Test Website';
    $domain = 'test-website.com';

    $response = $this
        ->actingAs($user)
        ->from('/websites/create')
        ->post('/websites', [
            'title' => $title,
            'domain' => $domain,
            'description' => 'A test website description',
        ]);

    $response
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('websites', [
        'title' => $title,
        'domain' => $domain,
        'organisation_id' => $organisation->id,
        'status' => 'active',
    ]);
});

test('cannot create website with duplicate domain', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    // Create first website
    $this->actingAs($user)->post('/websites', [
        'title' => 'First Website',
        'domain' => 'test.com',
        'description' => 'First website description',
    ]);

    // Attempt to create second website with same domain
    $response = $this->actingAs($user)->post('/websites', [
        'title' => 'Second Website',
        'domain' => 'test.com',
        'description' => 'Second website description',
    ]);

    $response->assertSessionHasErrors(['domain']);
    
    // Assert only one website exists with this domain
    $this->assertEquals(1, $organisation->websites()
        ->where('domain', 'test.com')
        ->count());
});

test('can create website with same domain in different organisations', function () {
    $user = User::factory()->create();
    $organisation1 = Organisation::factory()->create();
    $organisation2 = Organisation::factory()->create();
    
    // Attach user to both organisations as owner
    $user->organisations()->attach([
        $organisation1->id => ['role' => 'owner'],
        $organisation2->id => ['role' => 'owner']
    ]);

    // Create website in first organisation
    $user->switchOrganisation($organisation1);
    $response1 = $this->actingAs($user)->post('/websites', [
        'title' => 'First Website',
        'domain' => 'test1.com',
        'description' => 'First website description',
    ]);
    $response1->assertSessionHasNoErrors();

    // Create website in second organisation
    $user->switchOrganisation($organisation2);
    $response2 = $this->actingAs($user)->post('/websites', [
        'title' => 'Second Website',
        'domain' => 'test2.com',
        'description' => 'Second website description',
    ]);
    $response2->assertSessionHasNoErrors();

    // Assert both websites exist in their respective orgs
    $this->assertEquals(1, $organisation1->websites()->where('domain', 'test1.com')->count());
    $this->assertEquals(1, $organisation2->websites()->where('domain', 'test2.com')->count());
});

test('website can be created with logo', function () {
    Storage::fake('s3');

    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();
    
    $user->organisations()->attach($organisation, ['role' => 'owner']);
    $user->switchOrganisation($organisation);

    $title = 'Website with Logo';
    $domain = 'logo-website.com';
    $fakeLogo = UploadedFile::fake()->image('logo.jpg');

    $response = $this
        ->actingAs($user)
        ->from('/websites/create')
        ->post('/websites', [
            'title' => $title,
            'domain' => $domain,
            'description' => 'A website with a logo',
            'logo' => $fakeLogo,
        ]);

    $response
        ->assertSessionHasNoErrors();

    $website = $organisation->websites()->where('domain', $domain)->first();
    $this->assertNotNull($website->logo);
    // Check that the path starts with the expected directory
    $this->assertStringStartsWith('website-logos/' . $organisation->id, $website->logo);
    // Check the file exists on the fake disk
    Storage::disk('s3')->assertExists($website->logo);
    // Check that the stored path ends with the original extension
    $this->assertStringEndsWith('.' . $fakeLogo->getClientOriginalExtension(), $website->logo);
});
