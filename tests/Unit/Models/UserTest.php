<?php

use App\Models\User;
use App\Models\Organisation;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('getOverlappingOrganisations returns shared organisations', function () {
    // Create two users
    ['user' => $user1, 'organisation' => $org1] = $this->createUserWithOrganisation();
    ['user' => $user2] = $this->createUserWithOrganisation();
    
    // Create a shared organisation
    $sharedOrg = Organisation::factory()->create();
    $user1->organisations()->attach($sharedOrg, ['role' => 'member']);
    $user2->organisations()->attach($sharedOrg, ['role' => 'member']);
    
    // Get overlapping organisations
    $overlappingOrgs = $user1->getOverlappingOrganisations($user2);
    
    // Should only contain the shared organisation
    expect($overlappingOrgs)->toHaveCount(1);
    expect($overlappingOrgs->first()->id)->toBe($sharedOrg->id);
});

test('getOverlappingOrganisations returns empty collection when no shared organisations', function () {
    // Create two users with different organisations
    ['user' => $user1] = $this->createUserWithOrganisation();
    ['user' => $user2] = $this->createUserWithOrganisation();
    
    // Get overlapping organisations
    $overlappingOrgs = $user1->getOverlappingOrganisations($user2);
    
    // Should be empty
    expect($overlappingOrgs)->toBeEmpty();
});

test('getOverlappingOrganisations includes all shared organisations', function () {
    // Create two users
    ['user' => $user1] = $this->createUserWithOrganisation();
    ['user' => $user2] = $this->createUserWithOrganisation();
    
    // Create multiple shared organisations
    $sharedOrgs = Organisation::factory()->count(3)->create();
    
    // Attach both users to all shared organisations
    foreach ($sharedOrgs as $org) {
        $user1->organisations()->attach($org, ['role' => 'member']);
        $user2->organisations()->attach($org, ['role' => 'member']);
    }
    
    // Get overlapping organisations
    $overlappingOrgs = $user1->getOverlappingOrganisations($user2);
    
    // Should contain all shared organisations
    expect($overlappingOrgs)->toHaveCount(3);
    expect($overlappingOrgs->pluck('id')->toArray())->toBe($sharedOrgs->pluck('id')->toArray());
});
