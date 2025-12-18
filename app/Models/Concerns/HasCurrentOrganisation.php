<?php

namespace App\Models\Concerns;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasCurrentOrganisation
{
    public function currentOrganisation(): ?Organisation
    {
        if (! $this->id) {
            return null;
        }

        $currentOrganisationId = session('current_organisation_id');

        if (! $currentOrganisationId) {
            return $this->organisations()
                ->where('personal_organisation', true)
                ->first();
        }

        return $this->organisations()
            ->where('organisations.id', $currentOrganisationId)
            ->first();
    }

    public function switchOrganisation(Organisation $organisation): void
    {
        if (! $this->organisations()->where('organisations.id', $organisation->id)->exists()) {
            throw new \InvalidArgumentException('User does not belong to this organization.');
        }

        session(['current_organisation_id' => $organisation->id]);
    }
} 