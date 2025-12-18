<?php

namespace App\Services;

use App\Enums\ThirdPartyProvider;
use App\Models\Organisation;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StripePayment;
use App\Models\StripeProduct;

use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $stripeClient = null;
    private Organisation $organisation;
    private string $environment;

    public function __construct(Organisation $organisation, string $environment = 'test')
    {
        $this->organisation = $organisation;
        $this->environment = $environment;
    }

    public function initializeStripe(): void
    {
        // Check if Stripe is configured for this organisation
        if (!$this->organisation->hasThirdPartyProvider(ThirdPartyProvider::STRIPE)) {
            throw new \Exception('Stripe not configured for this organisation');
        }

        // Get the appropriate secret key for this environment
        $secretKeyName = $this->environment === 'test' ? 'test_secret_key' : 'secret_key';
        $secretKey = $this->organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $secretKeyName);

        if (!$secretKey) {
            throw new \Exception("Stripe {$this->environment} secret key not found");
        }

        $this->stripeClient = new StripeClient($secretKey);
    }

    public function syncProducts(): array
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }

        $stats = ['created' => 0, 'updated' => 0, 'failed' => 0];

        try {
            DB::beginTransaction();

            $stripeProducts = $this->stripeClient->products->all(['active' => true]);
            
            foreach ($stripeProducts as $stripeProduct) {
                // Get the default price for the product
                $price = $this->stripeClient->prices->retrieve($stripeProduct->default_price);
                
                try {
                    $product = Product::updateOrCreate(
                        [
                            'organisation_id' => $this->organisation->id,
                            'provider_type' => 'stripe',
                            'provider_product_id' => $stripeProduct->id,
                        ],
                        [
                            'name' => $stripeProduct->name,
                            'description' => $stripeProduct->description,
                            'price' => $price->unit_amount / 100, // Convert from cents to dollars
                            'currency' => $price->currency,
                            'active' => $stripeProduct->active,
                            'metadata' => $stripeProduct->metadata,
                        ]
                    );

                    StripeProduct::updateOrCreate(
                        [
                            'product_id' => $product->id,
                        ],
                        [
                            'stripe_id' => $stripeProduct->id,
                            'stripe_price_id' => $price->id,
                            'stripe_environment' => $this->environment,
                            'stripe_metadata' => $stripeProduct->metadata,
                        ]
                    );

                    $stats[$product->wasRecentlyCreated ? 'created' : 'updated']++;
                } catch (\Exception $e) {
                    $stats['failed']++;
                    \Log::error('Failed to sync Stripe product: ' . $e->getMessage(), [
                        'stripe_product_id' => $stripeProduct->id,
                        'organisation_id' => $this->organisation->id,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }

    public function createPaymentIntent(Product $product, array $options = []): Payment
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'organisation_id' => $this->organisation->id,
                'product_id' => $product->id,
                'provider_type' => 'stripe',
                'status' => Payment::STATUS_PENDING,
                'amount' => $product->price,
                'currency' => $product->currency,
                'metadata' => $options['metadata'] ?? [],
            ]);

            $paymentIntent = $this->stripeClient->paymentIntents->create([
                'amount' => (int) ($product->price * 100), // Convert to cents
                'currency' => $product->currency,
                'metadata' => array_merge(
                    $options['metadata'] ?? [],
                    ['payment_id' => $payment->id]
                ),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            StripePayment::create([
                'payment_id' => $payment->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_environment' => $this->environment,
                'stripe_metadata' => $paymentIntent->metadata,
            ]);

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // NOTE the webhook needs to be set up automatically when they configure Stripe
    public function handlePaymentIntentWebhook(array $event): void
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }

        $paymentIntent = $event['data']['object'];
        $stripePayment = StripePayment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        // If not found by payment intent ID, this might be a payment intent from a checkout session
        // Look for records where the payment intent ID is stored in metadata after session completion
        if (!$stripePayment) {
            $stripePayment = StripePayment::whereJsonContains('stripe_metadata->payment_intent_id', $paymentIntent['id'])->first();
        }
        
        // Note: For checkout sessions, the checkout.session.completed webhook will handle updating
        // the payment intent ID, so we don't need to process payment intent events for those

        if (!$stripePayment) {
            \Log::warning('StripePayment not found for PaymentIntent', [
                'payment_intent_id' => $paymentIntent['id'],
                'checked_metadata' => true,
            ]);
            return; // Don't throw exception, just log and return
        }

        $payment = $stripePayment->payment;

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                ]);
                
                if (isset($paymentIntent['payment_method'])) {
                    $stripePayment->update([
                        'stripe_payment_method_id' => $paymentIntent['payment_method'],
                    ]);
                }
                break;

            case 'payment_intent.payment_failed':
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                ]);
                break;

            case 'payment_intent.refunded':
                $payment->update([
                    'status' => Payment::STATUS_REFUNDED,
                ]);
                break;
        }
    }

    // In app/Services/StripeService.php

    public function configureWebhook(string $environment = 'test'): void
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }

        // Use the correct webhook URL based on environment
        $baseWebhookUrl = "https://autonomy-server-main-wbzjim.laravel.cloud/api/webhook/stripe/{$this->organisation->id}";

        \Log::info('Configuring webhook with URL', [
            'url' => $baseWebhookUrl,
            'environment' => $environment,
            'app_environment' => app()->environment(),
        ]);

        // Get the appropriate secret key for this environment
        $secretKeyName = $environment === 'test' ? 'test_secret_key' : 'secret_key';
        $secretKey = $this->organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $secretKeyName);

        if (!$secretKey) {
            throw new \Exception("Stripe {$environment} secret key not found");
        }

        // Initialize Stripe client with the correct secret key
        $stripeClient = new StripeClient($secretKey);

        // Check if webhook is already configured for this environment
        $webhookSecretKey = $environment === 'test' ? 'test_webhook_secret' : 'webhook_secret';
        $existingWebhookSecret = $this->organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $webhookSecretKey);

        if ($existingWebhookSecret) {
            // Verify the webhook still exists in Stripe
            $webhookEndpointIdKey = $environment === 'test' ? 'test_webhook_endpoint_id' : 'webhook_endpoint_id';
            $webhookEndpointId = $this->organisation->getThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $webhookEndpointIdKey);
            
            if ($webhookEndpointId) {
                try {
                    $stripeClient->webhookEndpoints->retrieve($webhookEndpointId);
                    \Log::info("Existing webhook found and valid for {$environment} environment", [
                        'organisation_id' => $this->organisation->id,
                        'webhook_id' => $webhookEndpointId
                    ]);
                    return;
                } catch (\Exception $e) {
                    \Log::info("Existing webhook not found in Stripe for {$environment} environment, creating new one");
                }
            }
        }

        // Create the webhook endpoint in Stripe
        $webhook = $stripeClient->webhookEndpoints->create([
            'url' => $baseWebhookUrl,
            'enabled_events' => ['*'],
        ]);

        // Store the webhook endpoint ID and secret
        $webhookEndpointIdKey = $environment === 'test' ? 'test_webhook_endpoint_id' : 'webhook_endpoint_id';
        $webhookSecretKey = $environment === 'test' ? 'test_webhook_secret' : 'webhook_secret';

        $this->organisation->setThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $webhookEndpointIdKey, $webhook->id);
        
        if ($webhook->secret) {
            $this->organisation->setThirdPartyVariableValue(ThirdPartyProvider::STRIPE, $webhookSecretKey, $webhook->secret);
        }

        \Log::info("Successfully configured Stripe webhook for {$environment} environment", [
            'organisation_id' => $this->organisation->id,
            'webhook_id' => $webhook->id,
            'url' => $baseWebhookUrl
        ]);
    }

    public function handleWebhook(array $event): void
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }

        \Log::info('Handling Stripe webhook event', [
            'event_type' => $event['type'],
            //'event_id' => $event['id'],
            'environment' => $this->environment
        ]);

        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;
            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event['data']['object']);
                break;
            case 'payment_intent.succeeded':
            case 'payment_intent.payment_failed':
            case 'payment_intent.refunded':
                // Only handle payment intent events if they're not from a checkout session
                if (!isset($event['data']['object']['metadata']['checkout_session_id'])) {
                    $this->handlePaymentIntentWebhook($event);
                } else {
                    \Log::info('Skipping payment intent event as it belongs to a checkout session', [
                        'event_type' => $event['type'],
                        'event_id' => $event['id'],
                        'checkout_session_id' => $event['data']['object']['metadata']['checkout_session_id']
                    ]);
                }
                break;
            default:
                \Log::info('Unhandled webhook event type', [
                    'event_type' => $event['type'],
                    'event_id' => $event['id']
                ]);
                break;
        }
    }

    private function handleCheckoutSessionCompleted(array $session): void
    {
        \Log::info('Handling checkout session completed', [
            'session_id' => $session['id'],
            'metadata' => $session['metadata'] ?? [],
        ]);

        // First try to find the StripePayment by the checkout session ID stored in our metadata
        $stripePayment = StripePayment::whereJsonContains('stripe_metadata->checkout_session_id', $session['id'])->first();
        
        // If not found, fall back to finding by payment_id in session metadata
        if (!$stripePayment && isset($session['metadata']['payment_id'])) {
            $payment = Payment::findOrFail($session['metadata']['payment_id']);
            $stripePayment = $payment->stripePayment;
        }

        if (!$stripePayment) {
            throw new \Exception('No StripePayment record found for session: ' . $session['id']);
        }

        $payment = $stripePayment->payment;

        // Update the payment status
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
        ]);

        // Get the payment intent from the session
        $paymentIntentId = is_string($session['payment_intent']) ? $session['payment_intent'] : $session['payment_intent']['id'];
        
        // Try to retrieve the full payment intent, but handle errors gracefully
        $paymentIntentDetails = null;
        try {
            $paymentIntentDetails = $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
        } catch (\Exception $e) {
            \Log::warning('Could not retrieve payment intent details from Stripe', [
                'payment_intent_id' => $paymentIntentId,
                'session_id' => $session['id'],
                'error' => $e->getMessage()
            ]);
            // Continue without payment intent details
        }

        // Update the StripePayment record with the actual payment intent ID and additional information
        $updateData = [
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_customer_id' => $session['customer'] ?? null,
            'stripe_metadata' => array_merge($stripePayment->stripe_metadata ?? [], [
                'checkout_session_id' => $session['id'],
                'customer_email' => $session['customer_email'] ?? null,
                'payment_intent_id' => $paymentIntentId,
            ]),
        ];

        // Add payment method if we have payment intent details
        if ($paymentIntentDetails && isset($paymentIntentDetails->payment_method)) {
            $updateData['stripe_payment_method_id'] = $paymentIntentDetails->payment_method;
        }

        $stripePayment->update($updateData);

        // Attach the product to the customer if customer_id is in metadata
        if (isset($session['metadata']['customer_id']) && isset($session['metadata']['product_id'])) {
            $customer = \App\Models\Customer::find($session['metadata']['customer_id']);
            $product = \App\Models\Product::find($session['metadata']['product_id']);
            
            if ($customer && $product) {
                // Check if the relationship doesn't already exist
                if (!$customer->products()->where('products.id', $product->id)->exists()) {
                    $customer->products()->attach($product->id, [
                        'payment_id' => $payment->id,
                        'purchased_at' => now(),
                    ]);
                    
                    \Log::info('Successfully attached product to customer', [
                        'customer_id' => $customer->id,
                        'product_id' => $product->id,
                        'payment_id' => $payment->id,
                    ]);
                }
            }
        }

        \Log::info('Successfully updated payment and stripe payment records', [
            'payment_id' => $payment->id,
            'stripe_payment_id' => $stripePayment->id,
            'payment_intent_id' => $paymentIntentId,
        ]);
    }

    private function handleCheckoutSessionExpired(array $session): void
    {
        if (!isset($session['metadata']['payment_id'])) {
            throw new \Exception('No payment_id found in session metadata');
        }

        $payment = Payment::findOrFail($session['metadata']['payment_id']);
        
        // Update the payment status to failed
        $payment->update([
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    public function getStripeClient(): \Stripe\StripeClient
    {
        if (!$this->stripeClient) {
            $this->initializeStripe();
        }
        return $this->stripeClient;
    }
} 