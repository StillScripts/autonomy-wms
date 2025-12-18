# Stripe Payment Flow - Visual Diagram

This document provides visual representations of the payment flow to complement the main documentation.

## High-Level Flow Diagram

```mermaid
sequenceDiagram
    participant C as Customer
    participant API as API Server
    participant DB as Database
    participant S as Stripe
    participant W as Webhook Handler

    C->>API: POST /api/v1/customers/products/{id}/purchase
    API->>DB: Create Payment (status: pending)
    API->>DB: Create StripePayment (pending_cs_xxx)
    API->>S: Create Checkout Session
    S-->>API: Return session URL
    API-->>C: Return checkout URL

    C->>S: Complete payment on Stripe
    S->>S: Process payment & create payment intent

    par Webhook Events
        S->>W: checkout.session.completed
        W->>DB: Find StripePayment by session ID
        W->>DB: Update Payment (status: completed)
        W->>DB: Update StripePayment (real payment_intent_id)
        W->>DB: Attach Product to Customer
    and
        S->>W: payment_intent.succeeded
        Note over W: Skipped (handled by checkout.session.completed)
    end

    C->>API: Access purchased product
    API->>DB: Verify customer owns product
    API-->>C: Grant access
```

## Database State Changes

### Initial State (After Purchase Request)

```
payments:
├── id: 1
├── status: 'pending'
├── product_id: 4
├── customer_id: 2 (in metadata)
└── ...

stripe_payments:
├── id: 1
├── payment_id: 1
├── stripe_payment_intent_id: 'pending_cs_test_123'
├── stripe_metadata: {
│   ├── checkout_session_id: 'cs_test_123'
│   └── ...
└── }

customer_product: (empty)
```

### Final State (After Webhook Processing)

```
payments:
├── id: 1
├── status: 'completed' ← UPDATED
├── product_id: 4
├── customer_id: 2 (in metadata)
└── ...

stripe_payments:
├── id: 1
├── payment_id: 1
├── stripe_payment_intent_id: 'pi_real_intent_123' ← UPDATED
├── stripe_customer_id: 'cus_stripe_123' ← UPDATED
├── stripe_payment_method_id: 'pm_card_123' ← UPDATED
├── stripe_metadata: {
│   ├── checkout_session_id: 'cs_test_123'
│   ├── payment_intent_id: 'pi_real_intent_123' ← ADDED
│   ├── customer_email: 'customer@example.com' ← ADDED
│   └── ...
└── }

customer_product: ← NEW RECORD
├── customer_id: 2
├── product_id: 4
├── payment_id: 1
└── purchased_at: '2025-01-XX XX:XX:XX'
```

## Webhook Event Processing Flow

```mermaid
flowchart TD
    A[Webhook Received] --> B{Verify Signature}
    B -->|Invalid| C[Return 400 Error]
    B -->|Valid| D{Event Type?}

    D -->|checkout.session.completed| E[Find StripePayment by session_id]
    D -->|payment_intent.succeeded| F[Find StripePayment by intent_id]
    D -->|checkout.session.expired| G[Mark Payment as Failed]
    D -->|Other| H[Log & Ignore]

    E --> I{StripePayment Found?}
    I -->|No| J[Try Fallback: Find by payment_id]
    I -->|Yes| K[Update Payment Status to Completed]
    J --> K

    K --> L[Retrieve Payment Intent from Stripe]
    L --> M{API Success?}
    M -->|No| N[Log Warning & Continue]
    M -->|Yes| O[Extract Payment Method Info]

    N --> P[Update StripePayment Record]
    O --> P
    P --> Q{Customer & Product in Metadata?}
    Q -->|Yes| R[Attach Product to Customer]
    Q -->|No| S[Skip Product Attachment]
    R --> T[Return 200 Success]
    S --> T

    F --> U{Found by Intent ID?}
    U -->|No| V[Try Find by Metadata]
    U -->|Yes| W[Update Payment Status]
    V --> W
    W --> T
```

## Error Handling Paths

```mermaid
flowchart TD
    A[Error Occurs] --> B{Error Type?}

    B -->|StripePayment Not Found| C[Log Warning & Return 200]
    B -->|Payment Intent API Error| D[Log Warning & Continue Without Details]
    B -->|Signature Verification Failed| E[Log Error & Return 400]
    B -->|Database Error| F[Log Error & Return 500]
    B -->|Unexpected Error| G[Log Full Stack Trace & Return 500]

    C --> H[Customer: No Product Delivered]
    D --> I[Customer: Product Delivered, Missing Payment Details]
    E --> J[Webhook Retry by Stripe]
    F --> K[Webhook Retry by Stripe]
    G --> L[Manual Investigation Required]

    style H fill:#ffcccc
    style I fill:#ffffcc
    style J fill:#ccffcc
    style K fill:#ccffcc
    style L fill:#ffcccc
```

## Key Integration Points

### 1. Customer Controller → Stripe API

```
CustomerController@purchase
    ↓
StripeService@getStripeClient()
    ↓
Stripe\Checkout\Session::create()
    ↓
Return checkout URL to customer
```

### 2. Webhook Controller → Service Layer

```
StripeWebhookController@handle
    ↓
Webhook signature verification
    ↓
Event type routing
    ↓
StripeService@handleWebhook()
    ↓
Specific event handlers
```

### 3. Database Consistency Flow

```
Payment Record Creation
    ↓ (immediate)
StripePayment Record Creation
    ↓ (via webhook)
Payment Status Update
    ↓ (via webhook)
StripePayment ID Update
    ↓ (via webhook)
Customer-Product Relationship
```

## Timing Considerations

### Typical Event Timeline

```
T+0s:    Customer initiates purchase
T+1s:    Database records created
T+2s:    Stripe checkout session created
T+3s:    Customer redirected to Stripe
T+30s:   Customer completes payment
T+31s:   Stripe processes payment
T+32s:   checkout.session.completed webhook sent
T+33s:   payment_intent.succeeded webhook sent
T+34s:   Our system processes webhooks
T+35s:   Customer has access to product
```

### Race Condition Mitigation

- **Multiple lookup strategies** for finding StripePayment records
- **Graceful error handling** for Stripe API failures
- **Idempotent product attachment** to prevent duplicates
- **Comprehensive logging** for debugging timing issues

## Monitoring Points

```mermaid
graph LR
    A[Customer Purchase] --> B[Payment Created]
    B --> C[Stripe Session Created]
    C --> D[Customer Redirected]
    D --> E[Payment Completed]
    E --> F[Webhook Received]
    F --> G[Payment Updated]
    G --> H[Product Attached]

    B -.-> B1[Monitor: Payment Creation Rate]
    C -.-> C1[Monitor: Stripe API Success Rate]
    F -.-> F1[Monitor: Webhook Processing Time]
    G -.-> G1[Monitor: Payment Completion Rate]
    H -.-> H1[Monitor: Product Attachment Success]

    style B1 fill:#e1f5fe
    style C1 fill:#e1f5fe
    style F1 fill:#e1f5fe
    style G1 fill:#e1f5fe
    style H1 fill:#e1f5fe
```

This visual documentation should be used alongside the main `stripe-payment-flow.md` file for a complete understanding of the payment system.
