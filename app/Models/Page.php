<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'website_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (!$page->slug) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if (!$page->slug || $page->isDirty('title')) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function contentBlocks()
    {
        return $this->belongsToMany(ContentBlock::class)
            ->withTimestamps()
            ->withPivot('order')
            ->orderBy('content_block_page.order');
    }

    public function attachContentBlock(ContentBlock $contentBlock, ?int $order = null)
    {
        if ($order === null) {
            // If no order specified, put it at the end
            $order = $this->contentBlocks()->max('content_block_page.order') + 1;
        }
        
        return $this->contentBlocks()->attach($contentBlock->id, ['order' => $order]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
} 