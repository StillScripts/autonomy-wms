<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\Membership;
use App\Policies\MembershipPolicy;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

beforeEach(function () {
    $this->policy = new MembershipPolicy();
});

test('user without current organisation cannot view any memberships', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()->create();

    expect($this->policy->viewAny($user, $organisation))->toBeFalse();
});

test('user with current organisation but no role cannot view any memberships', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $user->organisations()->updateExistingPivot($organisation->id, ['role' => null]);
    
    expect($this->policy->viewAny($user, $organisation))->toBeFalse();
});

test('member can view any memberships in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');    
    $organisation2 = Organisation::factory()->create();
    
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('admin can view any memberships in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $organisation2 = Organisation::factory()->create();
    
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('owner can view any memberships in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $organisation2 = Organisation::factory()->create();
    
    expect($this->policy->viewAny($user, $organisation))->toBeTrue();
    expect($this->policy->viewAny($user, $organisation2))->toBeFalse();
});

test('member can only view a membership in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    
    $sameOrgUser = User::factory()->create();
    $sameOrgMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $sameOrgUser->id,
        'role' => 'member'
    ]);

    ['user' => $differentOrgUser, 'organisation' => $differentOrg, 'membership' => $differentOrgMembership] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgMembership))->toBeTrue();
    expect($this->policy->view($user, $differentOrgMembership))->toBeFalse();
});

test('admin can only view a membership in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    $sameOrgUser = User::factory()->create();
    $sameOrgMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $sameOrgUser->id,
        'role' => 'member'
    ]);
    
    ['user' => $differentOrgUser, 'organisation' => $differentOrg, 'membership' => $differentOrgMembership] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgMembership))->toBeTrue();
    expect($this->policy->view($user, $differentOrgMembership))->toBeFalse();
});

test('owner can only view a membership in their organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $sameOrgUser = User::factory()->create();
    $sameOrgMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $sameOrgUser->id,
        'role' => 'member'
    ]);
    
    ['user' => $differentOrgUser, 'organisation' => $differentOrg, 'membership' => $differentOrgMembership] = $this->createUserWithOrganisation();
    
    expect($this->policy->view($user, $sameOrgMembership))->toBeTrue();
    expect($this->policy->view($user, $differentOrgMembership))->toBeFalse();
});

test('only owners and admins can create memberships in their organisation', function () {
    ['user' => $member, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    ['user' => $admin, 'organisation' => $organisation2] = $this->createUserWithOrganisation([], [], 'admin');
    ['user' => $owner, 'organisation' => $organisation3] = $this->createUserWithOrganisation();
    
    expect($this->policy->create($member, $organisation))->toBeFalse();
    expect($this->policy->create($admin, $organisation2))->toBeTrue();
    expect($this->policy->create($owner, $organisation3))->toBeTrue();
});

test('owner can update any membership in their organisation', function () {
    ['user' => $owner, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $targetUser = User::factory()->create();
    $membership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $targetUser->id,
        'role' => 'admin'
    ]);
    
    expect($this->policy->update($owner, $membership))->toBeTrue();
});

test('admin can only update member memberships', function () {
    ['user' => $admin, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    
    $memberUser = User::factory()->create();
    $ownerUser = User::factory()->create();
    
    $memberMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $memberUser->id,
        'role' => 'member'
    ]);
    
    $ownerMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $ownerUser->id,
        'role' => 'owner'
    ]);
    
    expect($this->policy->update($admin, $memberMembership))->toBeTrue();
    expect($this->policy->update($admin, $ownerMembership))->toBeFalse();
});

test('member cannot update any membership', function () {
    ['user' => $member, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $targetUser = User::factory()->create();
    $membership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $targetUser->id,
        'role' => 'member'
    ]);
    
    expect($this->policy->update($member, $membership))->toBeFalse();
});

test('owner can delete any membership in their organisation', function () {
    ['user' => $owner, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    $targetUser = User::factory()->create();
    $membership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $targetUser->id,
        'role' => 'admin'
    ]);
    
    expect($this->policy->delete($owner, $membership))->toBeTrue();
});

test('admin can only delete member memberships', function () {
    ['user' => $admin, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'admin');
    
    $memberUser = User::factory()->create();
    $ownerUser = User::factory()->create();
    
    $memberMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $memberUser->id,
        'role' => 'member'
    ]);
    
    $ownerMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $ownerUser->id,
        'role' => 'owner'
    ]);
    
    expect($this->policy->delete($admin, $memberMembership))->toBeTrue();
    expect($this->policy->delete($admin, $ownerMembership))->toBeFalse();
});

test('member cannot delete any membership', function () {
    ['user' => $member, 'organisation' => $organisation] = $this->createUserWithOrganisation([], [], 'member');
    $targetUser = User::factory()->create();
    $membership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $targetUser->id,
        'role' => 'member'
    ]);
    
    expect($this->policy->delete($member, $membership))->toBeFalse();
});

test('only owners and admins can assign roles to members', function () {
    ['user' => $owner, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $targetMember = User::factory()->create();
    
    $adminMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $admin->id,
        'role' => 'admin'
    ]);
    
    $memberMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $member->id,
        'role' => 'member'
    ]);
    
    $targetMemberMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $targetMember->id,
        'role' => 'member'
    ]);
    
    expect($this->policy->assignRole($owner, $targetMemberMembership))->toBeTrue();
    expect($this->policy->assignRole($admin, $targetMemberMembership))->toBeTrue();
    expect($this->policy->assignRole($member, $targetMemberMembership))->toBeFalse();
});

test('cannot assign roles to non-members', function () {
    ['user' => $owner, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    $admin = User::factory()->create();
    $adminMembership = Membership::factory()->create([
        'organisation_id' => $organisation->id,
        'user_id' => $admin->id,
        'role' => 'admin'
    ]);
    
    expect($this->policy->assignRole($owner, $adminMembership))->toBeFalse();
}); 