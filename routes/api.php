<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\Api\WebsiteContentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Stripe webhooks must be accessible without authentication
Route::post('/webhook/stripe/{organisation}', [StripeWebhookController::class, 'handle']);

// Live checkout endpoint for external websites
Route::post('/organisations/{organisation}/products/{product}/stripe-checkout', [StripePaymentController::class, 'startLiveCheckout']);

// Test unauthenticated route
Route::get('/test', function () {
    \Log::info('DEBUG - Test route hit', [
        'time' => now()->toIso8601String()
    ]);
    
    return response()->json([
        'message' => 'Hello World!',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::prefix('v1')->group(function () {
    Route::get('websites/{website}/content', [WebsiteContentController::class, 'index']);
    Route::get('websites/{website}/pages/{pageSlug}/content', [WebsiteContentController::class, 'show']);
    Route::get('websites/{website}/global-content', [WebsiteContentController::class, 'globalContent']);
    
    // Products routes
    Route::get('organisations/{organisation}/products/by-type', [App\Http\Controllers\Api\ProductController::class, 'getProductsByType']);

    // Customer authentication routes
    Route::post('/customers/register', [CustomerController::class, 'register']);
    Route::post('/customers/login', [CustomerController::class, 'login']);
    
    // Protected customer routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/customers/logout', [CustomerController::class, 'logout']);
        Route::get('/customers/me', [CustomerController::class, 'me']);
        Route::get('/customers/products', [CustomerController::class, 'products']);
        Route::post('/customers/products/{product}/purchase', [CustomerController::class, 'purchase']);
        
        // Product access check
        Route::get('/products/{product}/access', [ProductController::class, 'checkAccess']);
    });
}); 