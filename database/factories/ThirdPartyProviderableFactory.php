<?php

namespace Database\Factories;

use App\Models\ThirdPartyProviderable;
use App\Models\ThirdPartyProvider;
use App\Models\Organisation;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThirdPartyProviderableFactory extends Factory
{
    protected $model = ThirdPartyProviderable::class;

    public function definition(): array
    {
        // Randomly choose Organisation or Website as providerable_type
        $providerableType = $this->faker->randomElement([
            Organisation::class,
            Website::class,
        ]);
        $providerableId = $providerableType::factory();

        return [
            'third_party_provider_id' => ThirdPartyProvider::factory(),
            'providerable_id' => $providerableId,
            'providerable_type' => $providerableType,
        ];
    }
} 