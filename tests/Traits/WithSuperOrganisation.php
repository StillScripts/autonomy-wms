<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Organisation;

trait WithSuperOrganisation
{
    protected function createUserWithSuperOrganisation(array $userAttributes = [], array $organisationAttributes = []): array
    {
        $user = User::factory()->create($userAttributes);
        $organisation = Organisation::factory()->superOrg()->create($organisationAttributes);
        
        $user->organisations()->attach($organisation, ['role' => 'owner']);
        $user->switchOrganisation($organisation);

        return [
            'user' => $user,
            'organisation' => $organisation,
        ];
    }
} 