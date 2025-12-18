<?php

use App\Models\User;
use App\Models\Organisation;
use App\Models\Website;
use App\Models\Membership;

test('user can create websites in personal organisation', function () {
    // Create a user
    $user = User::factory()->create();

    // Create a personal organisation for the user
    $organisation = Organisation::factory()
        ->personal()
        ->create([
            'name' => $user->name . "'s Organisation"
        ]);

    // Attach user to organisation with owner role
    $organisation->users()->attach($user->id, [
        'role' => Membership::ROLE_OWNER
    ]);

    // Create multiple websites for the organisation
    $websites = Website::factory()->count(3)->create([
        'organisation_id' => $organisation->id,
    ]);

    // Assert the organisation has the correct number of websites
    expect($organisation->websites)->toHaveCount(3);

    // Assert each website belongs to the correct organisation
    $websites->each(function ($website) use ($organisation) {
        expect($website->organisation_id)->toBe($organisation->id);
        expect($organisation->websites->contains($website))->toBeTrue();
    });

    // Assert the user can access the websites through the organisation
    expect($user->organisations->contains($organisation))->toBeTrue();
});
