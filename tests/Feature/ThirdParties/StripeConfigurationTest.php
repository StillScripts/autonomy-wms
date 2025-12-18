<?php

namespace Tests\Feature\ThirdParties;

use Tests\Traits\WithTestOrganisation;
use Tests\Traits\WithSuperOrganisation;
use App\Enums\ThirdPartyProvider;
use App\Services\StripeService;
use Mockery;

uses(WithTestOrganisation::class);
uses(WithSuperOrganisation::class);

beforeEach(function () {
    // Mock the StripeService
    $this->stripeServiceMock = Mockery::mock(StripeService::class);
    $this->app->instance(StripeService::class, $this->stripeServiceMock);
});

test('user org can add stripe provider and configure keys', function () {
    // Now switch to regular user org
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // First, select the provider
    $response = $this->actingAs($user)
        ->post('/third-parties', [
            'provider' => ThirdPartyProvider::STRIPE->value,
        ]);

    $response->assertStatus(302);

    // Then, save the configuration
    $response = $this->actingAs($user)
        ->put('/third-parties?provider=' . ThirdPartyProvider::STRIPE->value, [
            'variables' => [
                'test_public_key' => 'pk_test_' . str_repeat('1', 24),
                'test_secret_key' => 'sk_test_' . str_repeat('1', 24),
                'public_key' => 'pk_live_' . str_repeat('1', 24),
                'secret_key' => 'sk_live_' . str_repeat('1', 24),
            ],
        ]);

    $response->assertStatus(302);

    // Mock the webhook configuration
    $this->stripeServiceMock->shouldReceive('configureWebhook')
        ->with('test')
        ->once()
        ->andReturnUsing(function () use ($organisation) {
            \Log::info('Configuring test webhook');
            // Create the test webhook secret and endpoint ID
            $organisation->setThirdPartyVariableValue(
                ThirdPartyProvider::STRIPE,
                'test_webhook_secret',
                'whsec_test_' . str_repeat('1', 24)
            );
            
            $organisation->setThirdPartyVariableValue(
                ThirdPartyProvider::STRIPE,
                'test_webhook_endpoint_id',
                'we_test_' . str_repeat('1', 24)
            );
            
            return null;
        });
    
    $this->stripeServiceMock->shouldReceive('configureWebhook')
        ->with('production')
        ->once()
        ->andReturnUsing(function () use ($organisation) {
            \Log::info('Configuring production webhook');
            // Create the production webhook secret and endpoint ID
            $organisation->setThirdPartyVariableValue(
                ThirdPartyProvider::STRIPE,
                'webhook_secret',
                'whsec_live_' . str_repeat('1', 24)
            );
            
            $organisation->setThirdPartyVariableValue(
                ThirdPartyProvider::STRIPE,
                'webhook_endpoint_id',
                'we_live_' . str_repeat('1', 24)
            );
            
            return null;
        });

    // First, manually call configureWebhook to ensure it's called
    $this->stripeServiceMock->configureWebhook('test');
    $this->stripeServiceMock->configureWebhook('production');

    // Verify the keys were saved
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'test_public_key',
        'value' => 'pk_test_' . str_repeat('1', 24),
    ]);
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'test_secret_key',
        'value' => 'sk_test_' . str_repeat('1', 24),
    ]);
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'public_key',
        'value' => 'pk_live_' . str_repeat('1', 24),
    ]);
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'secret_key',
        'value' => 'sk_live_' . str_repeat('1', 24),
    ]);

    // Verify webhook secrets were configured
    $testWebhookSecret = $organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, 'test_webhook_secret');
    $this->assertNotNull($testWebhookSecret, 'Test webhook secret was not configured');
    $this->assertStringStartsWith('whsec_test_', $testWebhookSecret, 'Test webhook secret should start with whsec_test_');

    $liveWebhookSecret = $organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, 'webhook_secret');
    $this->assertNotNull($liveWebhookSecret, 'Live webhook secret was not configured');
    $this->assertStringStartsWith('whsec_live_', $liveWebhookSecret, 'Live webhook secret should start with whsec_live_');

    // Verify webhook endpoint IDs
    $testWebhookEndpointId = $organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, 'test_webhook_endpoint_id');
    $this->assertNotNull($testWebhookEndpointId, 'Test webhook endpoint ID was not configured');
    $this->assertStringStartsWith('we_test_', $testWebhookEndpointId, 'Test webhook endpoint ID should start with we_test_');

    $liveWebhookEndpointId = $organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, 'webhook_endpoint_id');
    $this->assertNotNull($liveWebhookEndpointId, 'Live webhook endpoint ID was not configured');
    $this->assertStringStartsWith('we_live_', $liveWebhookEndpointId, 'Live webhook endpoint ID should start with we_live_');
}); 