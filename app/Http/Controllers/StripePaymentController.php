<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\Payment;
use App\Models\StripePayment;

class StripePaymentController extends Controller
{
    use AuthorizesRequests;

    public function startTestCheckout(Product $product)
    {
        return $this->startCheckout($product, 'test');
    }

    public function startLiveCheckout(Organisation $organisation, Product $product)
    {
        return $this->startCheckout($product, 'live', $organisation);
    }

    private function startCheckout(Product $product, string $environment, ?Organisation $organisation = null)
    {
        $organisation = $organisation ?? auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            throw new \Exception('Organisation not found');
        }

        if (auth()->check()) {
            $this->authorize('view', $organisation);
        }

        try {
            $stripeService = app()->makeWith(StripeService::class, [
                'organisation' => $organisation,
                'environment' => $environment,
            ]);
            $stripe = $stripeService->getStripeClient();

            // Create the payment record first
            $payment = Payment::create([
                'organisation_id' => $organisation->id,
                'product_id' => $product->id,
                'provider_type' => 'stripe',
                'status' => Payment::STATUS_PENDING,
                'amount' => $product->price,
                'currency' => $product->currency,
                'metadata' => [
                    'test_payment' => $environment === 'test',
                    'tested_by' => auth()->id(),
                ],
            ]);

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $product->currency,
                        'product_data' => [
                            'name' => $product->name,
                        ],
                        'unit_amount' => (int)($product->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/products?success=1'),
                'cancel_url' => url('/products?canceled=1'),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'product_id' => $product->id,
                    'test_payment' => $environment === 'test',
                    'tested_by' => auth()->id(),
                ],
            ]);

            // Create the StripePayment record with a temporary ID that will be replaced by webhook
            StripePayment::create([
                'payment_id' => $payment->id,
                'stripe_payment_intent_id' => 'pending_' . $session->id, // Temporary ID, will be replaced by webhook
                'stripe_environment' => $environment,
                'stripe_metadata' => array_merge($session->metadata->toArray(), [
                    'checkout_session_id' => $session->id,
                ]),
            ]);

            // For live payments, return JSON response
            if ($environment === 'live') {
                return response()->json([
                    'checkout_url' => $session->url,
                    'payment_id' => $payment->id,
                    'session_id' => $session->id,
                ]);
            }

            // For test payments, return Inertia response
            return Inertia::render('products/index', [
                'products' => $organisation->products()->with('stripeProduct')->latest()->paginate(10),
                'stripe_checkout_url' => $session->url,
            ]);
        } catch (\Exception $e) {
            if ($environment === 'live') {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }

            return Inertia::render('products/index', [
                'products' => $organisation->products()->with('stripeProduct')->latest()->paginate(10),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
