<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContentBlockTypeController;
use App\Http\Controllers\ContentBlockController;
use App\Http\Controllers\ThirdPartyProviderController;
use App\Http\Controllers\ThirdPartyVariableController;
use App\Http\Controllers\ThirdPartyProviderableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Middleware\EnsureSuperOrgAdmin;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\GlobalContentBlockController;
use App\Http\Controllers\PageIdeaController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('websites/{website}/pages/{page}/json', [PageController::class, 'showJson'])->name('websites.pages.json');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('websites', WebsiteController::class);

    Route::resource('websites.pages', PageController::class);

    // Global content blocks management for websites
    Route::get('websites/{website}/global-content-blocks/edit', [GlobalContentBlockController::class, 'edit'])->name('websites.global-content-blocks.edit');
    Route::put('websites/{website}/global-content-blocks', [GlobalContentBlockController::class, 'update'])->name('websites.global-content-blocks.update');
    Route::delete('websites/{website}/global-content-blocks/{globalContentBlock}', [GlobalContentBlockController::class, 'destroy'])->name('websites.global-content-blocks.destroy');

    Route::resource('content-block-types', ContentBlockTypeController::class);

    Route::resource('content-blocks', ContentBlockController::class);

    Route::resource('private-files', PrivateFileController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Page Ideas
    Route::get('page-ideas', [PageIdeaController::class, 'index'])->name('page-ideas.index');
    Route::get('page-ideas/create', [PageIdeaController::class, 'create'])->name('page-ideas.create');
    Route::post('page-ideas/generate', [PageIdeaController::class, 'generate'])->name('page-ideas.generate');
    Route::get('page-ideas/{pageIdea}/edit', [PageIdeaController::class, 'edit'])->name('page-ideas.edit');
    Route::get('page-ideas/{pageIdea}', [PageIdeaController::class, 'show'])->name('page-ideas.show');
    Route::get('conversations/{conversation}/page-ideas/versions', [PageIdeaController::class, 'versions'])->name('page-ideas.versions');
    Route::get('page-ideas/test-connection', [PageIdeaController::class, 'testConnection'])->name('page-ideas.test-connection');

    // website/organisation third party integrations
    Route::controller(ThirdPartyProviderableController::class)->group(function () {
    Route::get('third-parties', 'index')->name('third-parties.index');
    Route::get('third-parties/create', 'create')->name('third-parties.create');
    Route::post('third-parties', 'store')->name('third-parties.store');
    Route::get('third-parties/edit', 'edit')->name('third-parties.edit');
    Route::put('third-parties', 'update')->name('third-parties.update');
    Route::delete('third-parties', 'destroy')->name('third-parties.destroy');
});

    // Conversations
    Route::resource('conversations', ConversationController::class);
    Route::post('conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.messages.store');
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'getMessages'])->name('conversations.messages.index');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/sync', [ProductController::class, 'sync'])->name('products.sync');
    Route::post('/products/{product}/test-stripe-checkout', [StripePaymentController::class, 'startTestCheckout'])->name('products.test-stripe-checkout');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

    // Public product JSON endpoints
    Route::get('/api/products', [ProductController::class, 'getAllProducts'])->name('api.products.index');
    Route::get('/api/products/{product}', [ProductController::class, 'getProduct'])->name('api.products.show');
});

Route::middleware(['auth', EnsureSuperOrgAdmin::class])->prefix('system-settings')->name('system.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('system/index');
    })->name('index');

    // system-level management of third parties
    // Route::resource('third-party-providers', ThirdPartyProviderController::class)
    //     ->parameters(['third-party-providers' => 'thirdPartyProvider']);
    // Route::resource('third-party-variables', ThirdPartyVariableController::class)
    //     ->parameters(['third-party-variables' => 'thirdPartyVariable']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
