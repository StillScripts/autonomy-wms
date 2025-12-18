<?php

namespace App\Services;

use App\Models\PrivateFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrivateFileService
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Upload a private file
     *
     * @param UploadedFile $file
     * @param array $data
     * @return PrivateFile
     */
    public function upload(UploadedFile $file, array $data): PrivateFile
    {
        Log::info('[PrivateFileService] Starting file upload', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'content_type' => $data['content_type'] ?? null,
        ]);

        return DB::transaction(function () use ($file, $data) {
            try {
                // Generate the storage path
                $storagePath = $this->generateStoragePath($data['content_type']);
                Log::info('[PrivateFileService] Generated storage path', ['path' => $storagePath]);
                
                // Upload the file
                Log::info('[PrivateFileService] Uploading file to FileUploadService');
                $filePath = $this->fileUploadService->upload($file, $storagePath);
                Log::info('[PrivateFileService] File uploaded successfully', ['file_path' => $filePath]);
                
                // Prepare data for database
                $privateFileData = [
                    'organisation_id' => $data['organisation_id'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content_type' => $data['content_type'],
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'metadata' => $data['metadata'] ?? [],
                    'active' => $data['active'] ?? true,
                ];
                
                Log::info('[PrivateFileService] Creating PrivateFile record', [
                    'organisation_id' => $privateFileData['organisation_id'],
                    'content_type' => $privateFileData['content_type'],
                    'file_size' => $privateFileData['file_size'],
                    'mime_type' => $privateFileData['mime_type'],
                ]);
                
                // Create the private file record
                $privateFile = PrivateFile::create($privateFileData);
                
                Log::info('[PrivateFileService] PrivateFile record created', [
                    'private_file_id' => $privateFile->id,
                    'file_path' => $privateFile->file_path,
                ]);
                
                return $privateFile;
            } catch (\Exception $e) {
                Log::error('[PrivateFileService] Upload failed', [
                    'error' => $e->getMessage(),
                    'file_name' => $file->getClientOriginalName(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Update a private file
     *
     * @param PrivateFile $privateFile
     * @param array $data
     * @param UploadedFile|null $newFile
     * @return PrivateFile
     */
    public function update(PrivateFile $privateFile, array $data, ?UploadedFile $newFile = null): PrivateFile
    {
        return DB::transaction(function () use ($privateFile, $data, $newFile) {
            $updateData = array_filter([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'content_type' => $data['content_type'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'active' => $data['active'] ?? null,
            ], fn($value) => $value !== null);

            // If a new file is provided, upload it and delete the old one
            if ($newFile) {
                $oldFilePath = $privateFile->file_path;
                
                // Generate new storage path
                $storagePath = $this->generateStoragePath($data['content_type'] ?? $privateFile->content_type);
                
                // Upload the new file
                $filePath = $this->fileUploadService->upload($newFile, $storagePath);
                
                // Update file-related fields
                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $newFile->getClientOriginalName();
                $updateData['mime_type'] = $newFile->getMimeType();
                $updateData['file_size'] = $newFile->getSize();
                
                // Delete the old file
                $this->fileUploadService->delete($oldFilePath);
            }

            $privateFile->update($updateData);
            
            return $privateFile->fresh();
        });
    }

    /**
     * Delete a private file
     *
     * @param PrivateFile $privateFile
     * @return bool
     */
    public function delete(PrivateFile $privateFile): bool
    {
        return DB::transaction(function () use ($privateFile) {
            // Delete the file from storage
            $this->fileUploadService->delete($privateFile->file_path);
            
            // Delete the database record
            return $privateFile->delete();
        });
    }

    /**
     * Generate storage path based on content type
     *
     * @param string $contentType
     * @return string
     */
    private function generateStoragePath(string $contentType): string
    {
        $year = date('Y');
        $month = date('m');
        
        return "private-files/{$contentType}/{$year}/{$month}";
    }

    /**
     * Attach private files to a product
     *
     * @param int $productId
     * @param array $privateFileIds
     * @param bool $detachExisting
     * @return void
     */
    public function attachToProduct(int $productId, array $privateFileIds, bool $detachExisting = false): void
    {
        $product = \App\Models\Product::findOrFail($productId);
        
        DB::transaction(function () use ($product, $privateFileIds, $detachExisting) {
            if ($detachExisting) {
                $product->privateFiles()->detach();
            }

            $attachData = [];
            foreach ($privateFileIds as $index => $fileId) {
                $attachData[$fileId] = [
                    'sort_order' => $index,
                    'metadata' => ['attached_at' => now()->toDateTimeString()],
                ];
            }

            $product->privateFiles()->attach($attachData);
        });
    }

    /**
     * Get temporary URLs for a product's private files
     *
     * @param int $productId
     * @param int|null $duration
     * @return array
     */
    public function getProductFileUrls(int $productId, ?int $duration = null): array
    {
        $product = \App\Models\Product::with('privateFiles')->findOrFail($productId);
        
        return $product->privateFiles->map(function ($file) use ($duration) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'content_type' => $file->content_type,
                'url' => $file->getTemporaryUrl($duration),
                'size' => $file->formatted_file_size,
                'metadata' => $file->metadata,
            ];
        })->toArray();
    }
} 