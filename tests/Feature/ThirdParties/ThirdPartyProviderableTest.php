<?php

namespace Tests\Feature\ThirdParties;
use Tests\Traits\WithTestOrganisation;
use App\Enums\ThirdPartyProvider;
use App\Models\ThirdPartyVariableValue;

uses(WithTestOrganisation::class);

test('third party providers can be added to an organisation', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $response = $this
        ->actingAs($user)
        ->put('/third-parties?provider=' . ThirdPartyProvider::STRIPE->value, [
            'variables' => [
                'test_public_key' => 'pk_test_123',
                'test_secret_key' => 'sk_test_123',
            ],
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'test_public_key',
        'value' => 'pk_test_123',
    ]);
});

test('store validates required fields', function () {
    ['user' => $user] = $this->createUserWithOrganisation();

    $response = $this->actingAs($user)
        ->post('/third-parties', []);

    $response->assertSessionHasErrors(['provider']);
});

test('update saves variable values for provider', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    // Create initial values
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'test_public_key',
        'pk_test_old'
    );

    $response = $this->actingAs($user)
        ->put('/third-parties?provider=' . ThirdPartyProvider::STRIPE->value, [
            'variables' => [
                'test_public_key' => 'pk_test_new',
                'test_secret_key' => 'sk_test_new',
            ],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'test_public_key',
        'value' => 'pk_test_new',
    ]);
    $this->assertDatabaseHas('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
        'variable_key' => 'test_secret_key',
        'value' => 'sk_test_new',
    ]);
});

test('destroy removes all variables for provider', function () {
    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();
    
    // Create some values
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'test_public_key',
        'pk_test_123'
    );
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'test_secret_key',
        'sk_test_123'
    );

    $response = $this->actingAs($user)
        ->delete('/third-parties?provider=' . ThirdPartyProvider::STRIPE->value);

    $response->assertRedirect();
    $this->assertDatabaseMissing('third_party_variable_values', [
        'providerable_id' => $organisation->id,
        'providerable_type' => get_class($organisation),
        'provider' => ThirdPartyProvider::STRIPE->value,
    ]);
});
