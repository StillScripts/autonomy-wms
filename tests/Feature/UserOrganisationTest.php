<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\Membership;

test('user can be created with a personal organisation', function () {
    $user = User::factory()->create();
    $organisation = Organisation::factory()
        ->personal()
        ->create([
            'name' => $user->name . "'s Organisation"
        ]);
    
    $user->organisations()->attach($organisation, [
        'role' => Membership::ROLE_OWNER
    ]);

    $userOrg = $user->organisations()->first();

    expect($userOrg->name)->toBe($user->name . "'s Organisation");
    expect($userOrg->personal_organisation)->toBeTrue();
    expect($userOrg->pivot->role)->toBe(Membership::ROLE_OWNER);
});

test('user can belong to multiple organisations but have one personal organisation', function () {
    $user = User::factory()->create();
    
    // Create personal organisation
    $personalOrg = Organisation::factory()
        ->personal()
        ->create([
            'name' => $user->name . "'s Organisation"
        ]);

    $user->organisations()->attach($personalOrg, [
        'role' => Membership::ROLE_OWNER
    ]);
    
    // Create and attach to another organisation
    $anotherOrg = Organisation::factory()
        ->create();

    $user->organisations()->attach($anotherOrg, [
        'role' => Membership::ROLE_MEMBER
    ]);

    expect($user->organisations)->toHaveCount(2);

    $personalOrgMembership = $user->organisations()
        ->where('personal_organisation', true)
        ->first();

    expect($personalOrgMembership->pivot->role)->toBe(Membership::ROLE_OWNER);

    $regularOrgMembership = $user->organisations()
        ->where('personal_organisation', false)
        ->first();

    expect($regularOrgMembership->pivot->role)->toBe(Membership::ROLE_MEMBER);
});

test('organisation can have multiple users', function () {
    $organisation = Organisation::factory()->create();
    
    $users = User::factory()
        ->count(3)
        ->create();

    $users->each(function ($user) use ($organisation) {
        $organisation->users()->attach($user, [
            'role' => Membership::ROLE_MEMBER
        ]);
    });

    expect($organisation->users)->toHaveCount(3);

    $organisation->users->each(function ($user) {
        expect($user->pivot->role)->toBe(Membership::ROLE_MEMBER);
    });
}); 