<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stripe_id',
        'stripe_price_id',
        'stripe_environment',
        'stripe_metadata',
    ];

    protected $casts = [
        'stripe_metadata' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isTestEnvironment(): bool
    {
        return $this->stripe_environment === 'test';
    }
}
