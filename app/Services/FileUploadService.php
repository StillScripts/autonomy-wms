<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    private $disk;
    private $defaultUrlDuration = 30; // minutes

    /**
     * Constructor
     *
     * @param string $disk The filesystem disk to use.
     */
    public function __construct(string $disk = 's3')
    {
        $this->disk = $disk;
    }

    /**
     * Upload a file to storage
     *
     * @param UploadedFile $file
     * @param string $path Directory path where the file should be stored
     * @return string The stored file path
     */
    public function upload(UploadedFile $file, string $path): string
    {
        Log::info('[FileUploadService] Starting file upload to S3', [
            'disk' => $this->disk,
            'path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_size_mb' => round($file->getSize() / (1024 * 1024), 2),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        try {
            $storedPath = $file->store($path, $this->disk);
            
            Log::info('[FileUploadService] File uploaded successfully to S3', [
                'stored_path' => $storedPath,
                'full_url' => Storage::disk($this->disk)->url($storedPath),
            ]);
            
            return $storedPath;
        } catch (\Exception $e) {
            Log::error('[FileUploadService] S3 upload failed', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'path' => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a temporary URL for a file
     *
     * @param string|null $path
     * @param int|null $duration Duration in minutes
     * @return string|null
     */
    public function getTemporaryUrl(?string $path, ?int $duration = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $duration = $duration ?? $this->defaultUrlDuration;

        try {
            return Storage::disk($this->disk)->temporaryUrl(
                $path,
                now()->addMinutes($duration)
            );
        } catch (\Exception $e) {
            Log::warning('[FileUploadService] Failed to generate temporary URL', [
                'path' => $path,
                'disk' => $this->disk,
                'error' => $e->getMessage(),
                'hint' => 'S3 may not be configured. Set AWS_* environment variables to enable file storage.',
            ]);
            return null;
        }
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk($this->disk)->delete($path);
    }
} 