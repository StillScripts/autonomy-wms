<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Membership;

trait WithTestOrganisation
{
    protected function createUserWithOrganisation(array $userAttributes = [], array $organisationAttributes = [], string $role = 'owner'): array
    {
        $user = User::factory()->create($userAttributes);
        $organisation = Organisation::factory()->create($organisationAttributes);
        
        $user->organisations()->attach($organisation, ['role' => $role]);
        $user->switchOrganisation($organisation);

        $membership = Membership::where('user_id', $user->id)
            ->where('organisation_id', $organisation->id)
            ->first();

        return [
            'user' => $user,
            'organisation' => $organisation,
            'membership' => $membership,
        ];
    }
} 