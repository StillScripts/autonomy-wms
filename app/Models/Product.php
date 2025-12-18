<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'price',
        'currency',
        'active',
        'provider_type',
        'provider_product_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function stripeProduct(): HasOne
    {
        return $this->hasOne(StripeProduct::class);
    }

    public function privateFiles(): BelongsToMany
    {
        return $this->belongsToMany(PrivateFile::class, 'product_private_file')
            ->withPivot(['sort_order', 'metadata'])
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class)
            ->withTimestamps();
    }

    public function getProviderProduct(): ?Model
    {
        return match($this->provider_type) {
            'stripe' => $this->stripeProduct,
            default => null,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider_type', $provider);
    }
}
