# Purchase Endpoint Documentation

## Overview

The purchase endpoint allows authenticated customers to initiate a purchase for a specific product. The endpoint returns a Stripe checkout URL that the customer can use to complete their payment.

## Endpoint Details

- **URL**: `/api/v1/customers/products/{product_id}/purchase`
- **Method**: POST
- **Authentication**: Required (Bearer Token)
- **Content-Type**: application/json

## Request

### Headers

```
Authorization: Bearer {your_token}
Accept: application/json
Content-Type: application/json
```

### URL Parameters

- `product_id`: The ID of the product to purchase

## Response

### Success Response (200 OK)

```json
{
    "checkout_url": "https://checkout.stripe.com/...",
    "payment_id": 3,
    "session_id": "cs_test_..."
}
```

### Error Responses

- `401 Unauthorized`: Invalid or missing authentication token
- `404 Not Found`: Product not found
- `422 Unprocessable Entity`: Validation errors

## cURL Examples

### Basic Usage

```bash
curl -X POST 'http://localhost:8000/api/v1/customers/products/4/purchase' \
-H 'Authorization: Bearer {your_token}' \
-H 'Accept: application/json' \
-H 'Content-Type: application/json'
```

### With Verbose Output

```bash
curl -X POST 'http://localhost:8000/api/v1/customers/products/4/purchase' \
-H 'Authorization: Bearer {your_token}' \
-H 'Accept: application/json' \
-H 'Content-Type: application/json' \
-v
```

## Notes

1. Replace `{your_token}` with your actual authentication token
2. Replace `4` with the actual product ID you want to purchase
3. The checkout URL in the response should be opened in a browser to complete the payment
4. The session ID can be used to track the payment status
