<?php

namespace Tests\Feature\Products;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Product;
use App\Models\StripeProduct;
use App\Models\Payment;
use App\Models\StripePayment;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Mockery;
use Tests\Traits\WithTestOrganisation;

uses(WithTestOrganisation::class);

test('user can view products index', function () {
    $this->withoutVite();

    ['user' => $user, 'organisation' => $organisation] = $this->createUserWithOrganisation();

    $products = Product::factory()
        ->count(3)
        ->has(StripeProduct::factory()->test())
        ->create([
            'organisation_id' => $organisation->id,
        ]);

    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response->assertStatus(200);
    $response->assertInertia(function (AssertableInertia $page) {
        return $page
            ->component('products/index')
            ->has('products.data', 3);
    });
});

test('unauthorized user cannot view products', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response->assertStatus(403);
});

test('unauthorized user cannot sync products', function () {
    $user = User::factory()->create();
    
    // Create user without any organisation access
    $response = $this
        ->actingAs($user)
        ->post(route('products.sync'));

    $response->assertStatus(403);
});