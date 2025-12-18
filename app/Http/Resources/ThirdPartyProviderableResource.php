<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyProviderableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'provider' => [
                'value' => $this->provider->value,
                'display_name' => $this->provider->getDisplayName(),
                'variables' => $this->provider->getVariables(),
            ],
            'currentValues' => $this->when(isset($this->currentValues), $this->currentValues),
        ];
    }
}
