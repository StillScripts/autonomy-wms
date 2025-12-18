<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class CustomerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_can_register()
    {
        $response = $this->postJson('/api/v1/customers/register', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'customer' => [
                    'id',
                    'name',
                    'email',
                ],
                'token'
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
    }

    public function test_customer_cannot_register_with_existing_email()
    {
        Customer::create([
            'name' => 'Existing Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/customers/register', [
            'name' => 'New Customer',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_customer_can_login()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/customers/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'customer' => [
                    'id',
                    'name',
                    'email',
                ],
                'token'
            ]);
    }

    public function test_customer_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/customers/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_customer_can_logout()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/customers/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $customer->id,
            'tokenable_type' => Customer::class,
        ]);
    }

    public function test_customer_can_view_their_profile()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/customers/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $customer->id,
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ]);
    }

    public function test_customer_can_view_their_products()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $organisation = Organisation::create([
            'name' => 'Test Organisation',
            'is_super_org' => false,
            'personal_organisation' => false,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'currency' => 'USD',
            'active' => true,
            'provider_type' => 'stripe',
            'provider_product_id' => 'prod_test123',
            'organisation_id' => $organisation->id,
            'metadata' => [],
        ]);

        $payment = Payment::create([
            'status' => 'completed',
            'amount' => 99.99,
            'currency' => 'USD',
            'organisation_id' => $organisation->id,
            'product_id' => $product->id,
            'provider_type' => 'stripe',
            'metadata' => [],
        ]);

        $customer->products()->attach($product->id, [
            'payment_id' => $payment->id,
            'purchased_at' => now(),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/customers/products');

        $response->assertStatus(200);
    }

    public function test_customer_can_check_product_access()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $organisation = Organisation::create([
            'name' => 'Test Organisation',
            'is_super_org' => false,
            'personal_organisation' => false,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'currency' => 'USD',
            'active' => true,
            'provider_type' => 'stripe',
            'provider_product_id' => 'prod_test123',
            'organisation_id' => $organisation->id,
            'metadata' => [],
        ]);

        $payment = Payment::create([
            'status' => 'completed',
            'amount' => 99.99,
            'currency' => 'USD',
            'organisation_id' => $organisation->id,
            'product_id' => $product->id,
            'provider_type' => 'stripe',
            'metadata' => [],
        ]);

        $customer->products()->attach($product->id, [
            'payment_id' => $payment->id,
            'purchased_at' => now(),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/products/{$product->id}/access");

        $response->assertStatus(200)
            ->assertJson([
                'has_access' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                ],
            ]);
    }

    public function test_customer_cannot_access_unpurchased_product()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $organisation = Organisation::create([
            'name' => 'Test Organisation',
            'is_super_org' => false,
            'personal_organisation' => false,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'currency' => 'USD',
            'active' => true,
            'provider_type' => 'stripe',
            'provider_product_id' => 'prod_test123',
            'organisation_id' => $organisation->id,
            'metadata' => [],
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/products/{$product->id}/access");

        $response->assertStatus(200)
            ->assertJson([
                'has_access' => false,
                'product' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                ],
            ]);
    }
} 