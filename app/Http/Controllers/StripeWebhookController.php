<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Stripe\Webhook;
use App\Enums\ThirdPartyProvider;
// stripe listen --forward-to localhost:8000/api/webhook/stripe/3 --api-key sk_test_

class StripeWebhookController extends Controller
{
    public function handle(Request $request, Organisation $organisation)
    {
        \Log::info('CONTROLLER HIT - StripeWebhookController handle method', [
            'organisation_id' => $organisation->id,
            'timestamp' => now()->toIso8601String(),
            'headers' => $request->headers->all(),
            'content' => $request->getContent()
        ]);

        try {
            // Get the Stripe provider
            $provider = ThirdPartyProvider::STRIPE;
            
            // Check if Stripe is configured for this organisation
            if (!$organisation->hasThirdPartyProvider($provider)) {
                \Log::error('CONTROLLER ERROR - Stripe not configured for this organisation', [
                    'organisation_id' => $organisation->id
                ]);
                return response()->json(['error' => 'Stripe not configured for this organisation'], 400);
            }

            // Parse the payload to check if it's a test webhook
            $payload = $request->getContent();
            $payloadData = json_decode($payload, true);

            $isTest = false; // Default to false

            if (app()->environment('production')) {
                $webhookSecretKey = 'webhook_secret';
            } else {
                $isTest = isset($payloadData['livemode']) && $payloadData['livemode'] === false;
                $webhookSecretKey = $isTest ? 'test_webhook_secret' : 'webhook_secret';
            }

            $webhookSecretValue = $organisation->getThirdPartyVariableValue($provider, $webhookSecretKey);

            if (!$webhookSecretValue) {
                \Log::error('CONTROLLER ERROR - Webhook secret not found for organisation', [
                    'organisation_id' => $organisation->id,
                    'is_test' => $isTest,
                    'environment' => $isTest ? 'test' : 'production',
                    'webhook_secret_key' => $webhookSecretKey
                ]);
                return response()->json(['error' => 'Webhook secret not found'], 400);
            }

            \Log::info('CONTROLLER INFO - Found webhook secret', [
                'organisation_id' => $organisation->id,
                'is_test' => $isTest,
                'environment' => $isTest ? 'test' : 'production',
                'webhook_secret_key' => $webhookSecretKey
            ]);

            try {
                // Get the signature from the request
                $sigHeader = $request->header('Stripe-Signature');

                \Log::info('CONTROLLER INFO - Verifying webhook signature', [
                    'signature' => $sigHeader,
                    'webhook_secret_key' => $webhookSecretKey
                ]);

                // Verify the event
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecretValue
                );

                \Log::info('CONTROLLER INFO - Webhook signature verified', [
                    'event_type' => $event->type,
                    'event_id' => $event->id
                ]);

                // Initialize the Stripe service
                $stripeService = new StripeService($organisation, $isTest ? 'test' : 'production');

                // Convert the event to an array before passing it to the service
                $eventArray = [
                    'id' => $event->id,
                    'type' => $event->type,
                    'data' => [
                        'object' => $event->data->object->toArray()
                    ],
                    'livemode' => $event->livemode,
                    'pending_webhooks' => $event->pending_webhooks,
                    'request' => $event->request ? $event->request->toArray() : null
                ];

                // Handle the event
                $stripeService->handleWebhook($eventArray);

                \Log::info('CONTROLLER SUCCESS - Webhook handled successfully', [
                    'event_type' => $event->type,
                    'event_id' => $event->id
                ]);

                return response()->json(['status' => 'success']);
            } catch (\UnexpectedValueException $e) {
                \Log::error('CONTROLLER ERROR - Invalid payload', [
                    'error' => $e->getMessage(),
                    'organisation_id' => $organisation->id
                ]);
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                \Log::error('CONTROLLER ERROR - Invalid signature', [
                    'error' => $e->getMessage(),
                    'organisation_id' => $organisation->id
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('CONTROLLER ERROR - Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'organisation_id' => $organisation->id
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
} 