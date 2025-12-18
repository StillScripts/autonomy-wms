<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StripePayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?Product $fellowshipProduct = null): void
    {
        // Create or find the customer based on local data
        $customer = Customer::firstOrCreate(
            ['email' => 'blur@example.com'],
            [
                'name' => 'Daniel Still',
                'password' => Hash::make('password123'),
            ]
        );

        // If no product provided, get it from the database
        if (!$fellowshipProduct) {
            $fellowshipProduct = Product::where('name', 'The Fellowship of the Ring')
                ->where('price', 12.99)
                ->first();
        }

        if ($fellowshipProduct) {
            // Create completed payment for "The Fellowship of the Ring"
            $payment = Payment::firstOrCreate([
                'product_id' => $fellowshipProduct->id,
                'status' => Payment::STATUS_COMPLETED,
                'metadata->customer_id' => $customer->id,
            ], [
                'organisation_id' => $fellowshipProduct->organisation_id,
                'provider_type' => 'stripe',
                'amount' => $fellowshipProduct->price,
                'currency' => $fellowshipProduct->currency,
                'metadata' => [
                    'customer_id' => $customer->id,
                ],
            ]);

            // Create StripePayment for the payment
            StripePayment::firstOrCreate([
                'payment_id' => $payment->id,
            ], [
                'stripe_payment_intent_id' => 'cs_test_' . bin2hex(random_bytes(25)),
                'stripe_payment_method_id' => null,
                'stripe_customer_id' => null,
                'stripe_environment' => 'test',
                'stripe_metadata' => [
                    'checkout_session_id' => 'cs_test_' . bin2hex(random_bytes(25)),
                ],
            ]);

            // Attach product to customer (product they own)
            if (!$customer->products()->where('products.id', $fellowshipProduct->id)->exists()) {
                $customer->products()->attach($fellowshipProduct->id, [
                    'payment_id' => $payment->id,
                    'purchased_at' => now(),
                ]);
            }
        }

        if ($this->command) {
            $this->command->info('Customer seeded successfully!');
            $this->command->info('Customer Login:');
            $this->command->info('Email: blur@example.com');
            $this->command->info('Password: password123');
        }
    }
} 