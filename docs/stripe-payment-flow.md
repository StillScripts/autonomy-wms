# Stripe Payment Flow Documentation

This document provides a comprehensive overview of the complete payment flow in the Autonomy Server, from customer purchase initiation to product delivery via Stripe webhooks.

## Overview

The payment system uses Stripe Checkout Sessions for secure payment processing with webhook-based completion handling. The flow ensures customers receive their purchased products reliably while maintaining data integrity throughout the process.

## Payment Flow Architecture

```
Customer Purchase Request
        ↓
Payment & StripePayment Records Created
        ↓
Stripe Checkout Session Created
        ↓
Customer Redirected to Stripe
        ↓
Customer Completes Payment
        ↓
Stripe Sends Webhooks
        ↓
System Processes Webhooks
        ↓
Payment Completed & Product Attached to Customer
```

## Detailed Flow Steps

### 1. Purchase Initiation

**Endpoint**: `POST /api/v1/customers/products/{product_id}/purchase`

**Controller**: `App\Http\Controllers\Api\CustomerController@purchase`

#### Process:

1. **Authentication Check**: Verify customer has valid token
2. **Ownership Check**: Ensure customer doesn't already own the product
3. **Payment Record Creation**:

    ```php
    $payment = Payment::create([
        'organisation_id' => $organisation->id,
        'product_id' => $product->id,
        'provider_type' => 'stripe',
        'status' => Payment::STATUS_PENDING,
        'amount' => $product->price,
        'currency' => $product->currency,
        'metadata' => ['customer_id' => $customer->id],
    ]);
    ```

4. **Stripe Checkout Session Creation**:

    ```php
    $session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['card'],
        'line_items' => [...],
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'customer_email' => $customer->email,
        'metadata' => [
            'payment_id' => $payment->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ],
    ]);
    ```

5. **StripePayment Record Creation**:
    ```php
    StripePayment::create([
        'payment_id' => $payment->id,
        'stripe_payment_intent_id' => 'pending_' . $session->id, // Temporary ID
        'stripe_environment' => 'live',
        'stripe_metadata' => [
            'checkout_session_id' => $session->id,
            // ... other metadata
        ],
    ]);
    ```

#### Key Design Decisions:

- **Temporary Payment Intent ID**: Uses `pending_` prefix to avoid conflicts with real Stripe payment intent IDs
- **Metadata Storage**: Checkout session ID stored in JSON metadata for webhook lookup
- **Environment Handling**: Supports both test and live Stripe environments

### 2. Customer Payment Process

**Flow**: Customer → Stripe Checkout → Payment Completion

1. Customer redirected to `$session->url`
2. Customer enters payment information on Stripe's secure checkout page
3. Stripe processes payment and creates payment intent
4. Stripe sends webhooks to our system

### 3. Webhook Processing

**Endpoint**: `POST /api/webhook/stripe/{organisation_id}`

**Controller**: `App\Http\Controllers\StripeWebhookController@handle`

#### Webhook Security:

1. **Environment Detection**: Automatically detects test vs live mode from webhook payload
2. **Secret Validation**: Verifies webhook signature using appropriate secret key
3. **Signature Verification**: Uses Stripe's `Webhook::constructEvent()` for security

#### Webhook Events Processed:

##### A. `checkout.session.completed` (Primary Flow)

**Handler**: `StripeService@handleCheckoutSessionCompleted`

**Process**:

1. **Record Lookup**:

    ```php
    // Find by checkout session ID in metadata
    $stripePayment = StripePayment::whereJsonContains(
        'stripe_metadata->checkout_session_id',
        $session['id']
    )->first();

    // Fallback: Find by payment_id in session metadata
    if (!$stripePayment && isset($session['metadata']['payment_id'])) {
        $payment = Payment::findOrFail($session['metadata']['payment_id']);
        $stripePayment = $payment->stripePayment;
    }
    ```

2. **Payment Completion**:

    ```php
    $payment->update(['status' => Payment::STATUS_COMPLETED]);
    ```

3. **Payment Intent Retrieval** (with error handling):

    ```php
    try {
        $paymentIntentDetails = $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
    } catch (\Exception $e) {
        // Log warning and continue without payment intent details
        \Log::warning('Could not retrieve payment intent details from Stripe', [...]);
    }
    ```

4. **StripePayment Record Update**:

    ```php
    $stripePayment->update([
        'stripe_payment_intent_id' => $paymentIntentId, // Real payment intent ID
        'stripe_customer_id' => $session['customer'],
        'stripe_payment_method_id' => $paymentIntentDetails->payment_method ?? null,
        'stripe_metadata' => array_merge($existingMetadata, [
            'checkout_session_id' => $session['id'],
            'customer_email' => $session['customer_email'],
            'payment_intent_id' => $paymentIntentId,
        ]),
    ]);
    ```

5. **Product Attachment to Customer**:

    ```php
    if (isset($session['metadata']['customer_id']) && isset($session['metadata']['product_id'])) {
        $customer = Customer::find($session['metadata']['customer_id']);
        $product = Product::find($session['metadata']['product_id']);

        if (!$customer->products()->where('products.id', $product->id)->exists()) {
            $customer->products()->attach($product->id, [
                'payment_id' => $payment->id,
                'purchased_at' => now(),
            ]);
        }
    }
    ```

##### B. `payment_intent.succeeded` (Secondary Flow)

**Handler**: `StripeService@handlePaymentIntentWebhook`

**Purpose**: Handles direct payment intent events (not from checkout sessions)

**Process**:

1. **Record Lookup**:

    ```php
    // Direct lookup by payment intent ID
    $stripePayment = StripePayment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

    // Fallback: Look in metadata for completed checkout sessions
    if (!$stripePayment) {
        $stripePayment = StripePayment::whereJsonContains(
            'stripe_metadata->payment_intent_id',
            $paymentIntent['id']
        )->first();
    }
    ```

2. **Status Updates**: Updates payment status and payment method information

##### C. `checkout.session.expired`

**Handler**: `StripeService@handleCheckoutSessionExpired`

**Process**: Marks payment as failed when checkout session expires

### 4. Error Handling & Recovery

#### Common Issues & Solutions:

1. **"No such payment_intent" Error**:

    - **Cause**: Trying to retrieve payment intent with session ID
    - **Solution**: Graceful error handling in `handleCheckoutSessionCompleted`

2. **StripePayment Not Found**:

    - **Cause**: Webhook events arriving before database records created
    - **Solution**: Multiple lookup strategies with fallbacks

3. **Duplicate Product Attachments**:
    - **Prevention**: Check existing relationship before attaching product

#### Logging Strategy:

```php
// Success logging
\Log::info('Successfully updated payment and stripe payment records', [
    'payment_id' => $payment->id,
    'stripe_payment_id' => $stripePayment->id,
    'payment_intent_id' => $paymentIntentId,
]);

// Warning logging
\Log::warning('StripePayment not found for PaymentIntent', [
    'payment_intent_id' => $paymentIntent['id'],
    'checked_metadata' => true,
]);

// Error logging with context
\Log::error('CONTROLLER ERROR - Unexpected error', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'organisation_id' => $organisation->id
]);
```

## Database Schema

### Payments Table

```sql
payments:
- id (primary key)
- organisation_id (foreign key)
- product_id (foreign key)
- provider_type ('stripe')
- status ('pending', 'completed', 'failed', 'refunded')
- amount (decimal)
- currency (string)
- metadata (json)
- timestamps
```

### Stripe Payments Table

```sql
stripe_payments:
- id (primary key)
- payment_id (foreign key)
- stripe_payment_intent_id (unique string)
- stripe_payment_method_id (nullable string)
- stripe_customer_id (nullable string)
- stripe_environment ('test', 'live')
- stripe_metadata (json)
- timestamps
```

### Customer Product Pivot Table

```sql
customer_product:
- customer_id (foreign key)
- product_id (foreign key)
- payment_id (foreign key)
- purchased_at (timestamp)
```

## Testing

### Key Test Scenarios:

1. **Successful Purchase Flow**: `CustomerPurchaseFlowTest@test_complete_purchase_flow_end_to_end`
2. **Webhook Processing**: `CustomerPurchaseFlowTest@test_stripe_webhook_completes_payment_and_attaches_product_to_customer`
3. **Duplicate Purchase Prevention**: `CustomerPurchaseFlowTest@test_customer_cannot_purchase_product_they_already_own`

### Mock Strategy:

- Mock Stripe client for checkout session creation
- Mock webhook payloads for testing webhook handlers
- Verify database state changes after each step

## Security Considerations

1. **Webhook Signature Verification**: All webhooks verified using Stripe signature
2. **Environment Isolation**: Test and live webhooks use separate secrets
3. **Customer Authentication**: All purchase endpoints require valid customer tokens
4. **Data Validation**: Payment amounts and currencies validated before processing

## Monitoring & Observability

### Key Metrics to Monitor:

- Payment completion rate
- Webhook processing success rate
- Customer product attachment success rate
- Payment intent retrieval failures

### Log Patterns to Watch:

- `"StripePayment not found for PaymentIntent"` (indicates lookup issues)
- `"Could not retrieve payment intent details from Stripe"` (API connectivity issues)
- `"Successfully attached product to customer"` (successful completions)

## Environment Configuration

### Required Stripe Variables per Organisation:

- `test_secret_key` / `secret_key`
- `test_public_key` / `public_key`
- `test_webhook_secret` / `webhook_secret`
- `test_webhook_endpoint_id` / `webhook_endpoint_id`

### Webhook URL Format:

```
https://your-domain.com/api/webhook/stripe/{organisation_id}
```

## Troubleshooting Guide

### Payment Not Completing:

1. Check webhook logs for processing errors
2. Verify webhook signature validation
3. Confirm payment_id in session metadata
4. Check if StripePayment record exists

### Customer Not Getting Product:

1. Verify `checkout.session.completed` webhook processed
2. Check if product attachment step completed
3. Confirm customer_id and product_id in session metadata
4. Verify customer-product relationship in database

### Database Inconsistencies:

1. Check if payment status matches Stripe status
2. Verify StripePayment record has correct payment_intent_id
3. Confirm metadata contains all required fields

## Future Improvements

1. **Idempotency Keys**: Add idempotency handling for webhook events
2. **Retry Logic**: Implement exponential backoff for failed Stripe API calls
3. **Dead Letter Queue**: Handle permanently failed webhook events
4. **Audit Trail**: Enhanced logging for payment state changes
5. **Real-time Notifications**: WebSocket updates for payment completion
