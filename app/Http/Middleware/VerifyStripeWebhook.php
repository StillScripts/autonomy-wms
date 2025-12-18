<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyStripeWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('MIDDLEWARE HIT - VerifyStripeWebhook middleware hit', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'content' => $request->getContent(),
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String()
        ]);

        if (!$request->hasHeader('Stripe-Signature')) {
            \Log::error('MIDDLEWARE ERROR - Missing Stripe signature header', [
                'headers' => $request->headers->all(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            // Add the test flag based on the event data
            $payload = json_decode($request->getContent(), true);
            $isTest = isset($payload['livemode']) && !$payload['livemode'];
            
            \Log::info('MIDDLEWARE INFO - Stripe webhook payload received', [
                'event_type' => $payload['type'] ?? 'unknown',
                'is_test_mode' => $isTest,
                'event_id' => $payload['id'] ?? 'unknown',
                'payload' => $payload
            ]);

            $request->merge([
                'isTest' => $isTest
            ]);

            return $next($request);
        } catch (\Exception $e) {
            \Log::error('MIDDLEWARE ERROR - Exception processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'headers' => $request->headers->all(),
                'content' => $request->getContent()
            ]);
            throw $e;
        }
    }
} 