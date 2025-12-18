<?php

use App\Models\User;
use App\Models\Organisation;
use App\Policies\UserPolicy;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

beforeEach(function () {
    $this->policy = new UserPolicy();
});

test('user without current organisation cannot view any users', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();

    expect($this->policy->viewAny($user, $organisation))->toBeFalse();
});

test('user with current organisation but no role cannot view any users', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $user->organisations()->updateExistingPivot($organisation->id, ['role' => null]);
    
    expect($this->policy->viewAny($user, $organisation))->toBeFalse();
});

test('member can view any users in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');    
    $organisation2 = Organisation::factory()->create();
    
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('admin can view any users in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $organisation2 = Organisation::factory()->create();
    
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('owner can view any users in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $organisation2 = Organisation::factory()->create();
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('member can only view a user in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $sameOrgUser = User::factory()->create();
    $sameOrgUser->organisations()->attach($organisation, ['role' => 'member']);
    ['user' => $differentOrgUser] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgUser))->toBeTrue();
    expect($this->policy->view($user, $differentOrgUser))->toBeFalse();
});

test('admin can only view a user in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $sameOrgUser = User::factory()->create();
    $sameOrgUser->organisations()->attach($organisation, ['role' => 'member']);
    ['user' => $differentOrgUser] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgUser))->toBeTrue();
    expect($this->policy->view($user, $differentOrgUser))->toBeFalse();
});

test('owner can only view a user in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $sameOrgUser = User::factory()->create();
    $sameOrgUser->organisations()->attach($organisation, ['role' => 'member']);
    ['user' => $differentOrgUser] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgUser))->toBeTrue();
    expect($this->policy->view($user, $differentOrgUser))->toBeFalse();
});

test('only owners and admins can create users', function () {
    ['user' => $member, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    ['user' => $admin, 'organisation' => $organisation2] = $this->createUserWithOrganisation([], [], 'admin');
    ['user' => $owner, 'organisation' => $organisation3] = $this->createUserWithOrganisation();
    
    expect($this->policy->create($member, $organisation))->toBeFalse();
    expect($this->policy->create($admin, $organisation2))->toBeTrue();
    expect($this->policy->create($owner, $organisation3))->toBeTrue();
});

test('user can update themselves', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $otherUser = User::factory()->create();
    
    expect($this->policy->update($user, $user))->toBeTrue();
    expect($this->policy->update($user, $otherUser))->toBeFalse();
});

test('user can delete themselves', function () {
    ['user' => $user] = $this->createUserWithOrganisation();
    $otherUser = User::factory()->create();
    
    expect($this->policy->delete($user, $user))->toBeTrue();
    expect($this->policy->delete($user, $otherUser))->toBeFalse();
});
