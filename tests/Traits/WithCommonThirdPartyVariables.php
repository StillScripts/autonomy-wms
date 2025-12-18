<?php

namespace Tests\Traits;

use App\Models\ThirdPartyProvider;
use App\Models\ThirdPartyVariable;

trait WithCommonThirdPartyVariables
{
    /**
     * Create the standard Stripe variables for a given provider
     *
     * @param ThirdPartyProvider $provider
     * @return void
     */
    protected function createStripeVariables(ThirdPartyProvider $provider): void
    {
        $variables = [
            [
                'name' => 'STRIPE_TEST_PUBLIC_KEY',
                'description' => 'Stripe test environment publishable key (starts with pk_test_)',
                'is_secret' => false,
            ],
            [
                'name' => 'STRIPE_TEST_SECRET_KEY',
                'description' => 'Stripe test environment secret key (starts with sk_test_)',
                'is_secret' => true,
            ],
            [
                'name' => 'STRIPE_TEST_WEBHOOK_SECRET',
                'description' => 'Stripe test environment webhook signing secret (starts with whsec_)',
                'is_secret' => true,
            ],
            [
                'name' => 'STRIPE_TEST_WEBHOOK_ENDPOINT_ID',
                'description' => 'Stripe test environment webhook endpoint ID (starts with we_)',
                'is_secret' => false,
            ],
            [
                'name' => 'STRIPE_PUBLIC_KEY',
                'description' => 'Stripe live environment publishable key (starts with pk_live_)',
                'is_secret' => false,
            ],
            [
                'name' => 'STRIPE_SECRET_KEY',
                'description' => 'Stripe live environment secret key (starts with sk_live_)',
                'is_secret' => true,
            ],
            [
                'name' => 'STRIPE_WEBHOOK_SECRET',
                'description' => 'Stripe live environment webhook signing secret (starts with whsec_)',
                'is_secret' => true,
            ],
            [
                'name' => 'STRIPE_WEBHOOK_ENDPOINT_ID',
                'description' => 'Stripe live environment webhook endpoint ID (starts with we_)',
                'is_secret' => false,
            ],
        ];

        foreach ($variables as $variable) {
            ThirdPartyVariable::create(array_merge($variable, [
                'third_party_provider_id' => $provider->id,
            ]));
        }
    }
}

trait WithSampleStripeVariableValues
{
    /**
     * Add sample values for all Stripe variables for a given organisation's providerable
     *
     * @param \App\Models\ThirdPartyProviderable $providerable
     * @return void
     */
    protected function addSampleStripeVariableValues($providerable): void
    {
        $provider = $providerable->provider;
        $variables = $provider->variables;
        foreach ($variables as $variable) {
            $value = match ($variable->name) {
                'STRIPE_TEST_PUBLIC_KEY' => 'pk_test_sample',
                'STRIPE_TEST_SECRET_KEY' => 'sk_test_sample',
                'STRIPE_TEST_WEBHOOK_SECRET' => 'whsec_test_sample',
                'STRIPE_TEST_WEBHOOK_ENDPOINT_ID' => 'we_test_sample',
                'STRIPE_PUBLIC_KEY' => 'pk_live_sample',
                'STRIPE_SECRET_KEY' => 'sk_live_sample',
                'STRIPE_WEBHOOK_SECRET' => 'whsec_live_sample',
                'STRIPE_WEBHOOK_ENDPOINT_ID' => 'we_live_sample',
                default => 'sample_value',
            };
            $providerable->variableValues()->updateOrCreate(
                ['third_party_variable_id' => $variable->id],
                ['value' => $value]
            );
        }
    }
} 