<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalContentBlock extends Model
{
    protected $fillable = [
        'website_id',
        'content_block_id'
    ];

    /**
     * Get the website that owns the global content block.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the content block that is global.
     */
    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class);
    }

    /**
     * Scope a query to only include global content blocks for a specific website.
     */
    public function scopeForWebsite(Builder $query, Website $website): Builder
    {
        return $query->where('website_id', $website->id);
    }
}
