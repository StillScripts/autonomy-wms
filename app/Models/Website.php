<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\FileUploadService;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'title',
        'domain',
        'description',
        'status',
        'logo',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo)) {
            return null;
        }

        return app(FileUploadService::class)->getTemporaryUrl($this->logo);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function globalContentBlocks()
    {
        return $this->hasMany(GlobalContentBlock::class);
    }

    public function thirdPartyProviders()
    {
        return $this->morphToMany(ThirdPartyProvider::class, 'providerable', 'third_party_providerables');
    }
}
