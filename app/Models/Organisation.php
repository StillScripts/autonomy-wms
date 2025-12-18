<?php

namespace App\Models;

use App\Enums\ThirdPartyProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'personal_organisation',
        'is_super_org',
    ];

    protected $casts = [
        'personal_organisation' => 'boolean',
        'is_super_org' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->using(Membership::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class);
    }

    public function contentBlockTypes()
    {
        return $this->hasMany(ContentBlockType::class);
    }

    public function isSuperOrg(): bool
    {
        return $this->is_super_org;
    }

    public function thirdPartyVariableValues(): MorphMany
    {
        return $this->morphMany(ThirdPartyVariableValue::class, 'providerable');
    }

    public function getThirdPartyVariableValue(ThirdPartyProvider $provider, string $variableKey): ?string
    {
        return $this->thirdPartyVariableValues()
            ->where('provider', $provider->value)
            ->where('variable_key', $variableKey)
            ->value('value');
    }

    public function setThirdPartyVariableValue(ThirdPartyProvider $provider, string $variableKey, string $value): void
    {
        $this->thirdPartyVariableValues()->updateOrCreate(
            [
                'provider' => $provider->value,
                'variable_key' => $variableKey,
            ],
            ['value' => $value]
        );
    }

    public function hasThirdPartyProvider(ThirdPartyProvider $provider): bool
    {
        return $this->thirdPartyVariableValues()
            ->where('provider', $provider->value)
            ->exists();
    }

    public function getEnabledThirdPartyProviders(): array
    {
        return $this->thirdPartyVariableValues()
            ->distinct('provider')
            ->pluck('provider')
            ->toArray();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function privateFiles()
    {
        return $this->hasMany(PrivateFile::class);
    }
} 