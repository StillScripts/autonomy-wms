<?php

namespace Tests\Feature\Products;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Product;
use App\Models\StripeProduct;
use App\Models\Payment;
use App\Models\StripePayment;
use App\Services\StripeService;
use App\Enums\ThirdPartyProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Mockery;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('start test checkout creates payment and stripe payment', function () {
    $this->withoutVite();

    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // Configure Stripe for the organisation
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'test_public_key',
        'pk_test_' . str_repeat('1', 24)
    );
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'test_secret_key',
        'sk_test_' . str_repeat('1', 24)
    );
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

    $product = Product::factory()
        ->has(StripeProduct::factory()->test())
        ->create([
            'organisation_id' => $organisation->id,
            'name' => 'Test Product',
            'price' => 10.00,
            'currency' => 'usd',
        ]);

    // Mock the Stripe client and its responses
    $mockStripeClient = Mockery::mock(StripeClient::class);
    $mockCheckout = Mockery::mock();
    $mockSessions = Mockery::mock();
    $mockStripeClient->checkout = $mockCheckout;
    $mockCheckout->sessions = $mockSessions;

    // Create a proper mock of the Session class with all required properties
    $mockSession = new class extends Session {
        public $id = 'cs_test_123';
        public $url = 'https://checkout.stripe.com/test_123';
        public $metadata;
        public $payment_intent = 'pi_test_123';
        public $object = 'checkout.session';
        public $status = 'open';
        public $amount_total = 1000; // $10.00 in cents
        public $currency = 'usd';
        public $customer = 'cus_test_123';
        public $payment_status = 'unpaid';
        public $mode = 'payment';

        public function __construct()
        {
            $this->metadata = new Collection([
                'payment_id' => null,
                'product_id' => null,
                'test_payment' => true,
                'tested_by' => null,
            ]);
        }

        public static function getPermanentAttributes()
        {
            return new Collection([
                'id', 
                'object', 
                'metadata',
                'status',
                'amount_total',
                'currency',
                'customer',
                'payment_status',
                'mode'
            ]);
        }
    };

    // Mock the checkout session creation with proper response
    $mockSessions->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($params) {
            return isset($params['success_url']) 
                && isset($params['cancel_url'])
                && isset($params['mode'])
                && isset($params['line_items'])
                && $params['mode'] === 'payment';
        }))
        ->andReturn($mockSession);

    // Mock the StripeService to return our mocked client
    app()->bind(StripeService::class, function ($app, $params) use ($mockStripeClient) {
        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('getStripeClient')->andReturn($mockStripeClient);
        return $mock;
    });

    $response = $this
        ->actingAs($user)
        ->post(route('products.test-stripe-checkout', $product));

    $response->assertStatus(200);
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('products/index');
        $page->has('stripe_checkout_url');
        $page->where('stripe_checkout_url', 'https://checkout.stripe.com/test_123');
    });

    // Assert that the payment record was created
    $this->assertDatabaseHas('payments', [
        'organisation_id' => $organisation->id,
        'product_id' => $product->id,
        'provider_type' => 'stripe',
        'status' => Payment::STATUS_PENDING,
        'amount' => 10.00,
        'currency' => 'usd',
    ]);

    // Assert that the stripe payment record was created
    $this->assertDatabaseHas('stripe_payments', [
        'stripe_payment_intent_id' => 'pending_' . $mockSession->id,
        'stripe_environment' => 'test',
        'payment_id' => Payment::where('product_id', $product->id)->first()->id,
    ]);
});

test('start live checkout creates payment and stripe payment with json response', function () {
    $this->withoutVite();

    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    // Configure Stripe for the organisation
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'public_key',
        'pk_live_' . str_repeat('1', 24)
    );
    $organisation->setThirdPartyVariableValue(
        ThirdPartyProvider::STRIPE,
        'secret_key',
        'sk_live_' . str_repeat('1', 24)
    );
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

    $product = Product::factory()
        ->has(StripeProduct::factory()->live())
        ->create([
            'organisation_id' => $organisation->id,
            'name' => 'Live Product',
            'price' => 20.00,
            'currency' => 'usd',
        ]);

    // Mock the Stripe client and its responses
    $mockStripeClient = Mockery::mock(StripeClient::class);
    $mockCheckout = Mockery::mock();
    $mockSessions = Mockery::mock();
    $mockStripeClient->checkout = $mockCheckout;
    $mockCheckout->sessions = $mockSessions;

    // Create a proper mock of the Session class with all required properties
    $mockSession = new class extends Session {
        public $id = 'cs_live_123';
        public $url = 'https://checkout.stripe.com/live_123';
        public $metadata;
        public $payment_intent = 'pi_live_123';
        public $object = 'checkout.session';
        public $status = 'open';
        public $amount_total = 2000; // $20.00 in cents
        public $currency = 'usd';
        public $customer = 'cus_live_123';
        public $payment_status = 'unpaid';
        public $mode = 'payment';

        public function __construct()
        {
            $this->metadata = new Collection([
                'payment_id' => null,
                'product_id' => null,
                'test_payment' => false,
                'tested_by' => null,
            ]);
        }

        public static function getPermanentAttributes()
        {
            return new Collection([
                'id', 
                'object', 
                'metadata',
                'status',
                'amount_total',
                'currency',
                'customer',
                'payment_status',
                'mode'
            ]);
        }
    };

    // Mock the checkout session creation with proper response
    $mockSessions->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($params) {
            return isset($params['success_url']) 
                && isset($params['cancel_url'])
                && isset($params['mode'])
                && isset($params['line_items'])
                && $params['mode'] === 'payment';
        }))
        ->andReturn($mockSession);

    // Mock the StripeService to return our mocked client
    app()->bind(StripeService::class, function ($app, $params) use ($mockStripeClient) {
        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('getStripeClient')->andReturn($mockStripeClient);
        return $mock;
    });

    $response = $this
        ->postJson("/api/organisations/{$organisation->id}/products/{$product->id}/stripe-checkout");

    $response->assertStatus(200);
    $response->assertJson([
        'checkout_url' => 'https://checkout.stripe.com/live_123',
        'session_id' => 'cs_live_123',
    ]);
    $response->assertJsonStructure([
        'checkout_url',
        'payment_id',
        'session_id',
    ]);

    // Assert that the payment record was created
    $this->assertDatabaseHas('payments', [
        'organisation_id' => $organisation->id,
        'product_id' => $product->id,
        'provider_type' => 'stripe',
        'status' => Payment::STATUS_PENDING,
        'amount' => 20.00,
        'currency' => 'usd',
    ]);

    // Assert that the stripe payment record was created
    $this->assertDatabaseHas('stripe_payments', [
        'stripe_payment_intent_id' => 'pending_' . $mockSession->id,
        'stripe_environment' => 'live',
        'payment_id' => Payment::where('product_id', $product->id)->first()->id,
    ]);
});
