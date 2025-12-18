<?php

namespace App\Models;

use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrivateFile extends Model
{
    use HasFactory;

    const CONTENT_TYPE_EBOOK = 'ebook';
    const CONTENT_TYPE_AUDIOBOOK = 'audiobook';
    const CONTENT_TYPE_VIDEO = 'video';
    const CONTENT_TYPE_DOCUMENT = 'document';
    const CONTENT_TYPE_OTHER = 'other';

    const CONTENT_TYPES = [
        self::CONTENT_TYPE_EBOOK,
        self::CONTENT_TYPE_AUDIOBOOK,
        self::CONTENT_TYPE_VIDEO,
        self::CONTENT_TYPE_DOCUMENT,
        self::CONTENT_TYPE_OTHER,
    ];

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'content_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_private_file')
            ->withPivot(['sort_order', 'metadata'])
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    /**
     * Get a temporary URL for the file
     *
     * @param int|null $duration Duration in minutes
     * @return string|null
     */
    public function getTemporaryUrl(?int $duration = null): ?string
    {
        $fileUploadService = app(FileUploadService::class);
        return $fileUploadService->getTemporaryUrl($this->file_path, $duration);
    }

    /**
     * Delete the file from storage
     *
     * @return bool
     */
    public function deleteFile(): bool
    {
        $fileUploadService = app(FileUploadService::class);
        return $fileUploadService->delete($this->file_path);
    }

    /**
     * Scope to filter by content type
     */
    public function scopeByContentType($query, string $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Get human-readable file size
     *
     * @return string
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Check if the file is an ebook
     */
    public function isEbook(): bool
    {
        return $this->content_type === self::CONTENT_TYPE_EBOOK;
    }

    /**
     * Check if the file is an audiobook
     */
    public function isAudiobook(): bool
    {
        return $this->content_type === self::CONTENT_TYPE_AUDIOBOOK;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clean up the file when the model is deleted
        static::deleting(function ($privateFile) {
            $privateFile->deleteFile();
        });
    }
}
