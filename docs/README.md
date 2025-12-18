# Autonomy Server Documentation

This directory contains comprehensive documentation for the Autonomy Server application.

## Documentation Index

### Payment System

- **[Stripe Payment Flow](stripe-payment-flow.md)** - Complete technical documentation of the payment process from customer purchase to product delivery
- **[Payment Flow Diagrams](stripe-payment-flow-diagram.md)** - Visual diagrams and flowcharts showing the payment system architecture

### Authentication & Customer Management

- **[Customer Authentication System](customer-authentication-system.md)** - Detailed guide for customer authentication implementation
- **[Customer Auth Plan](customer-auth-plan.md)** - Planning document for customer authentication features

### Technical Guides

- **[MP3 Upload Debugging Guide](mp3-upload-debugging-guide.md)** - Troubleshooting guide for media file uploads

### API Documentation

- **[Purchase Endpoint](api/purchase-endpoint.md)** - API documentation for purchase-related endpoints

## Quick References

### Payment Flow (TL;DR)

1. Customer calls `POST /api/v1/customers/products/{id}/purchase`
2. System creates Payment and StripePayment records
3. Stripe checkout session created and customer redirected
4. Customer completes payment on Stripe
5. Stripe sends `checkout.session.completed` webhook
6. System processes webhook, updates payment status, and attaches product to customer

### Key Files for Payment System

- **Controllers**: `app/Http/Controllers/Api/CustomerController.php`, `app/Http/Controllers/StripeWebhookController.php`
- **Services**: `app/Services/StripeService.php`
- **Models**: `app/Models/Payment.php`, `app/Models/StripePayment.php`, `app/Models/Customer.php`
- **Tests**: `tests/Feature/Customers/CustomerPurchaseFlowTest.php`

### Common Troubleshooting

#### Payments Not Completing

1. Check webhook endpoint configuration
2. Verify webhook signature validation
3. Ensure correct Stripe environment (test/live)
4. Review webhook processing logs

#### Customers Not Getting Products

1. Verify `checkout.session.completed` webhook processed successfully
2. Check if customer-product relationship created in database
3. Confirm metadata contains correct customer_id and product_id

## Development Setup

### Required Environment Variables

Each organisation needs these Stripe variables configured:

- `test_secret_key` / `secret_key`
- `test_public_key` / `public_key`
- `test_webhook_secret` / `webhook_secret`
- `test_webhook_endpoint_id` / `webhook_endpoint_id`

### Testing

Run the complete payment flow tests:

```bash
php artisan test --filter=CustomerPurchaseFlowTest
php artisan test --filter=StripePaymentTest
```

### Webhook Testing

Use Stripe CLI for local webhook testing:

```bash
stripe listen --forward-to localhost:8000/api/webhook/stripe/{organisation_id}
```

## Architecture Overview

The payment system follows a webhook-driven architecture where:

- **Immediate Response**: Customer gets immediate feedback and checkout URL
- **Asynchronous Processing**: Payment completion handled via webhooks
- **Reliability**: Multiple lookup strategies and error handling ensure robust processing
- **Security**: Webhook signature verification and environment isolation

For detailed technical information, see the specific documentation files linked above.
