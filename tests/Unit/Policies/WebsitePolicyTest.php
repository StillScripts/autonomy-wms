<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\Website;
use App\Policies\WebsitePolicy;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

beforeEach(function () {
    $this->policy = new WebsitePolicy();
});

test('user without current organisation cannot view any websites', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user with current organisation can view any websites', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    
    expect($this->policy->viewAny($user))->toBeTrue();
});

test('member can view websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentWebsite = Website::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $website))->toBeTrue();
    expect($this->policy->view($user, $differentWebsite))->toBeFalse();
});

test('admin can view websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentWebsite = Website::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $website))->toBeTrue();
    expect($this->policy->view($user, $differentWebsite))->toBeFalse();
});

test('owner can view websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    $differentOrg = Organisation::factory()->create();
    $differentWebsite = Website::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->view($user, $website))->toBeTrue();
    expect($this->policy->view($user, $differentWebsite))->toBeFalse();
});

test('owner can create websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    expect($this->policy->create($user, $organisation))->toBeTrue();
});

test('admin can create websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    
    expect($this->policy->create($user, $organisation))->toBeTrue();
});

test('member can create websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    
    expect($this->policy->create($user, $organisation))->toBeTrue();
});

test('user cannot create websites in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    
    expect($this->policy->create($user, $differentOrg))->toBeFalse();
});

test('owner can update websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->update($user, $website))->toBeTrue();
});

test('admin can update websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->update($user, $website))->toBeTrue();
});

test('member can update websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->update($user, $website))->toBeTrue();
});

test('user cannot update websites in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    $website = Website::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->update($user, $website))->toBeFalse();
});

test('owner can delete websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->delete($user, $website))->toBeTrue();
});

test('admin can delete websites in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->delete($user, $website))->toBeTrue();
});

test('member cannot delete websites', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->delete($user, $website))->toBeFalse();
});

test('user cannot delete websites in different organisation', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $differentOrg = Organisation::factory()->create();
    $website = Website::factory()->create(['organisation_id' => $differentOrg->id]);
    
    expect($this->policy->delete($user, $website))->toBeFalse();
});

test('guests can view websites via api', function () {
    $guest = User::factory()->create();
    $organisation = Organisation::factory()->create();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->viewViaApi($guest, $website))->toBeTrue();
});

test('users cannot restore websites', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->restore($user, $website))->toBeFalse();
});

test('users cannot force delete websites', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $website = Website::factory()->create(['organisation_id' => $organisation->id]);
    
    expect($this->policy->forceDelete($user, $website))->toBeFalse();
});