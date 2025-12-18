<?php

namespace App\Models;

use App\Enums\ThirdPartyProvider;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyVariableValue extends Model
{
    protected $fillable = [
        'providerable_id',
        'providerable_type',
        'provider',
        'variable_key',
        'value',
    ];

    protected $casts = [
        'provider' => ThirdPartyProvider::class,
    ];

    public function providerable()
    {
        return $this->morphTo();
    }

    public function getVariableInfo(): array
    {
        return $this->provider->getVariables()[$this->variable_key] ?? [];
    }
}
