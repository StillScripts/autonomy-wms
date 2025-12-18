<?php

namespace Database\Factories;

use App\Models\ThirdPartyVariableValue;
use App\Models\ThirdPartyProviderable;
use App\Models\ThirdPartyVariable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThirdPartyVariableValue>
 */
class ThirdPartyVariableValueFactory extends Factory
{
    protected $model = ThirdPartyVariableValue::class;

    public function definition(): array
    {
        return [
            'third_party_providerable_id' => ThirdPartyProviderable::factory(),
            'third_party_variable_id' => ThirdPartyVariable::factory(),
            'value' => $this->faker->regexify('[A-Za-z0-9_]{32}'),
        ];
    }
} 