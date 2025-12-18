<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\ContentBlockType;
use App\Policies\ContentBlockTypePolicy;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

beforeEach(function () {
    $this->policy = new ContentBlockTypePolicy();
});

test('user without current organisation cannot view any content block types', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user with current organisation can view any content block types', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    
    expect($this->policy->viewAny($user))->toBeTrue();
});

test('member can view content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentContentBlockType = ContentBlockType::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $contentBlockType))->toBeTrue();
    expect($this->policy->view($user, $differentContentBlockType))->toBeFalse();
});

test('admin can view content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentContentBlockType = ContentBlockType::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $contentBlockType))->toBeTrue();
    expect($this->policy->view($user, $differentContentBlockType))->toBeFalse();
});

test('owner can view content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentContentBlockType = ContentBlockType::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $contentBlockType))->toBeTrue();
    expect($this->policy->view($user, $differentContentBlockType))->toBeFalse();
});

test('owner can create content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    expect($this->policy->create($user, $organisation))->toBeTrue();
});

test('admin can create content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    
    expect($this->policy->create($user, $organisation))->toBeTrue();
});

test('member cannot create content block types without permissions', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    
    // Since we don't have permissions system set up yet, members should not be able to create
    expect($this->policy->create($user, $organisation))->toBeFalse();
});

test('user cannot create content block types in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    
    expect($this->policy->create($user, $differentOrg))->toBeFalse();
});

test('owner can update content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->update($user, $contentBlockType))->toBeTrue();
});

test('admin can update content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->update($user, $contentBlockType))->toBeTrue();
});

test('member cannot update content block types without permissions', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    // Since we don't have permissions system set up yet, members should not be able to update
    expect($this->policy->update($user, $contentBlockType))->toBeFalse();
});

test('user cannot update content block types in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->update($user, $contentBlockType))->toBeFalse();
});

test('owner can delete content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->delete($user, $contentBlockType))->toBeTrue();
});

test('admin can delete content block types in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->delete($user, $contentBlockType))->toBeTrue();
});

test('member cannot delete content block types without permissions', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $organisation->id]);
    
    // Since we don't have permissions system set up yet, members should not be able to delete
    expect($this->policy->delete($user, $contentBlockType))->toBeFalse();
});

test('user cannot delete content block types in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    $contentBlockType = ContentBlockType::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->delete($user, $contentBlockType))->toBeFalse();
});