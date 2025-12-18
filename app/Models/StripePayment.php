<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'stripe_environment',
        'stripe_metadata',
    ];

    protected $casts = [
        'stripe_metadata' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isTestEnvironment(): bool
    {
        return $this->stripe_environment === 'test';
    }
}
