<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThirdPartyProviderable extends Model
{
    use HasFactory;

    protected $fillable = [
        'third_party_provider_id',
        'providerable_id',
        'providerable_type',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyProvider::class, 'third_party_provider_id');
    }

    public function providerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function variableValues(): HasMany
    {
        return $this->hasMany(ThirdPartyVariableValue::class, 'providerable_id')
            ->where('providerable_type', ThirdPartyProviderable::class);
    }
} 