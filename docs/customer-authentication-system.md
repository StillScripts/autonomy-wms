# Customer Authentication System Documentation

## Overview

The customer authentication system allows external websites to authenticate their customers and provide access to purchased products. This system uses Laravel Sanctum for token-based authentication and supports cross-origin requests.

## Database Structure

### Customers Table

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name');
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```

### Customer-Product Pivot Table

```php
Schema::create('customer_product', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('payment_id')->constrained()->onDelete('cascade');
    $table->timestamp('purchased_at');
    $table->timestamps();
});
```

## API Endpoints

### Authentication Endpoints

#### Register Customer

```http
POST /api/v1/customers/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

Response:

```json
{
    "customer": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abcdef..."
}
```

#### Login Customer

```http
POST /api/v1/customers/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response:

```json
{
    "customer": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abcdef..."
}
```

#### Logout Customer

```http
POST /api/v1/customers/logout
Authorization: Bearer 1|abcdef...
```

Response:

```json
{
    "message": "Logged out successfully"
}
```

#### Get Current Customer

```http
GET /api/v1/customers/me
Authorization: Bearer 1|abcdef...
```

Response:

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
}
```

#### Get Customer's Products

```http
GET /api/v1/customers/products
Authorization: Bearer 1|abcdef...
```

Response:

```json
{
    "products": [
        {
            "id": 1,
            "name": "Product Name",
            "payment": {
                "id": 1,
                "status": "completed",
                "amount": "99.99",
                "currency": "USD"
            }
        }
    ]
}
```

#### Check Product Access

```http
GET /api/v1/products/{product}/access
Authorization: Bearer 1|abcdef...
```

Response:

```json
{
    "has_access": true,
    "product": {
        "id": 1,
        "name": "Product Name"
    }
}
```

## Implementation Details

### Customer Model

The Customer model extends Authenticatable and uses Sanctum's HasApiTokens trait:

```php
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasApiTokens;
    // ...
}
```

### CORS Configuration

Update `config/cors.php` to allow requests from customer websites:

```php
return [
    'paths' => ['api/*'],
    'allowed_origins' => [
        'https://customer-website-1.com',
        'https://customer-website-2.com',
    ],
    'supports_credentials' => true,
];
```

### Frontend Integration

#### Next.js Implementation

1. Store the token in a secure HTTP-only cookie
2. Include the token in API requests:

```typescript
const api = axios.create({
    baseURL: 'https://api.example.com',
    withCredentials: true,
    headers: {
        Authorization: `Bearer ${token}`,
    },
});
```

3. Handle authentication state:

```typescript
const [customer, setCustomer] = useState(null);

const login = async (email, password) => {
    const response = await api.post('/customers/login', {
        email,
        password,
    });
    setCustomer(response.data.customer);
};
```

## Security Considerations

1. **Token Storage**: Always store tokens in HTTP-only cookies
2. **CORS**: Configure allowed origins carefully
3. **Password Hashing**: Passwords are automatically hashed using Laravel's Hash facade
4. **Rate Limiting**: Consider implementing rate limiting on authentication endpoints
5. **Token Expiration**: Configure token expiration in `config/sanctum.php`

## Testing

### API Tests

```php
public function test_customer_can_login()
{
    $customer = Customer::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->postJson('/api/v1/customers/login', [
        'email' => $customer->email,
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'customer',
            'token'
        ]);
}
```

## Maintenance

1. **Token Cleanup**: Implement a command to clean up expired tokens
2. **Customer Data**: Consider implementing data retention policies
3. **Logging**: Monitor authentication attempts and failures
4. **Updates**: Keep Laravel and Sanctum updated to latest versions

## Troubleshooting

Common issues and solutions:

1. **CORS Errors**: Check allowed origins in `config/cors.php`
2. **Token Issues**: Verify token format and expiration
3. **Authentication Failures**: Check password hashing and validation
4. **Product Access**: Verify customer-product relationships and payment status

## Future Improvements

1. Implement refresh tokens
2. Add two-factor authentication
3. Implement social login
4. Add email verification
5. Implement password reset functionality
