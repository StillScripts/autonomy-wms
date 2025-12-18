<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_block_type_id',
        'content',
        'description',
        'organisation_id',
        'website_id',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    protected $appends = ['content_with_urls'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contentBlock) {
            if (!$contentBlock->content || !$contentBlock->blockType) {
                return;
            }

            $content = $contentBlock->content;
            $request = request();

            foreach ($contentBlock->blockType->fields as $field) {
                if ($field['type'] === 'file' || $field['type'] === 'image') {
                    $fieldSlug = $field['slug'];
                    
                    // Handle file upload if a new file is provided
                    $file = $request->file("content.{$fieldSlug}");
                    if ($file instanceof UploadedFile) {
                        $path = app(FileUploadService::class)->upload(
                            $file,
                            "content-blocks/{$contentBlock->content_block_type_id}"
                        );
                        $content[$fieldSlug] = $path;
                    }
                }
            }

            $contentBlock->content = $content;
        });
    }

    public function getContentWithUrlsAttribute()
    {
        if (empty($this->content)) {
            return [];
        }

        $content = $this->content;
        $blockType = $this->blockType;

        if (!$blockType) {
            return $content;
        }

        $fileService = app(FileUploadService::class);

        // Process each field in the content
        foreach ($blockType->fields as $field) {
            $fieldSlug = $field['slug'];
            
            // If this is a file field and we have a value, generate a signed URL
            if (($field['type'] === 'file' || $field['type'] === 'image') && !empty($content[$fieldSlug])) {
                // Generate URL with 24 hour duration for images
                $content[$fieldSlug . '_url'] = $fileService->getTemporaryUrl($content[$fieldSlug], 1440);
            }
        }

        return $content;
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class)
            ->withTimestamps()
            ->orderBy('content_block_page.created_at');
    }

    public function blockType()
    {
        return $this->belongsTo(ContentBlockType::class, 'content_block_type_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function validateContent()
    {
        if (!$this->blockType) {
            return false;
        }

        $fields = $this->blockType->fields;
        $content = $this->content;

        // Validate that all required fields are present
        foreach ($fields as $field) {
            if (($field['required'] ?? false) && !isset($content[$field['name']])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope a query to only include content blocks for a specific organization.
     */
    public function scopeForOrganisation(Builder $query, Organisation $organisation): Builder
    {
        return $query->where('organisation_id', $organisation->id);
    }

    /**
     * Scope a query to only include content blocks for a specific website.
     */
    public function scopeForWebsite(Builder $query, Website $website): Builder
    {
        return $query->where('website_id', $website->id);
    }

    /**
     * Scope a query to only include organisation-wide content blocks.
     */
    public function scopeOrganisationWide(Builder $query): Builder
    {
        return $query->whereNull('website_id');
    }

    /**
     * Scope a query to only include website-specific content blocks.
     */
    public function scopeWebsiteSpecific(Builder $query): Builder
    {
        return $query->whereNotNull('website_id');
    }
} 