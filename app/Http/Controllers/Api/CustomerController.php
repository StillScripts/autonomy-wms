<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use App\Models\Payment;
use App\Services\StripeService;
use App\Models\StripePayment;

class CustomerController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'customer' => $customer,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'customer' => $customer,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function products(Request $request)
    {
        $customer = $request->user();
        
        return response()->json([
            'products' => $customer->products()->get()
        ]);
    }

    public function purchase(Request $request, Product $product)
    {
        $request->validate([
            'redirect_url' => 'nullable|string|url',
        ]);

        $customer = $request->user();
        
        // Check if customer already owns the product
        if ($customer->products()->where('products.id', $product->id)->exists()) {
            return response()->json([
                'message' => 'You already own this product'
            ], 400);
        }

        // Get the organisation from the product
        $organisation = $product->organisation;

        try {
            $stripeService = app()->makeWith(StripeService::class, [
                'organisation' => $organisation,
                'environment' => 'live',
            ]);
            $stripe = $stripeService->getStripeClient();

            // Create the payment record
            $payment = Payment::create([
                'organisation_id' => $organisation->id,
                'product_id' => $product->id,
                'provider_type' => 'stripe',
                'status' => Payment::STATUS_PENDING,
                'amount' => $product->price,
                'currency' => $product->currency,
                'metadata' => [
                    'customer_id' => $customer->id,
                ],
            ]);

            // Prepare success and cancel URLs
            $redirectUrl = $request->input('redirect_url', url('/products'));
            $successUrl = $redirectUrl . (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'success=1';
            $cancelUrl = $redirectUrl . (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'canceled=1';

            // Create Stripe checkout session
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
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $customer->email, // Prefill customer email
                'metadata' => [
                    'payment_id' => $payment->id,
                    'product_id' => $product->id,
                    'customer_id' => $customer->id,
                ],
            ]);

            // Create the StripePayment record
            StripePayment::create([
                'payment_id' => $payment->id,
                'stripe_payment_intent_id' => 'pending_' . $session->id, // Temporary ID, will be replaced by webhook
                'stripe_environment' => 'live',
                'stripe_metadata' => array_merge($session->metadata->toArray(), [
                    'checkout_session_id' => $session->id,
                ]),
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'payment_id' => $payment->id,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
