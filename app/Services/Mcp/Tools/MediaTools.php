<?php

namespace App\Services\Mcp\Tools;

use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Upload media from a URL
     *
     * @param array $args Arguments with 'url' and optional 'name', 'collection'
     * @return array
     */
    public function uploadFromUrl(array $args): array
    {
        $validator = Validator::make($args, [
            'url' => 'required|url',
            'name' => 'nullable|string|max:255',
            'collection' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $validated = $validator->validated();
            $url = $validated['url'];
            $collection = $validated['collection'] ?? 'default';

            // Download the file
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to download file from URL',
                    'error' => 'HTTP ' . $response->status(),
                ];
            }

            // Get content type and determine extension
            $contentType = $response->header('Content-Type');
            $extension = $this->getExtensionFromMimeType($contentType);

            if (!$extension) {
                // Try to get extension from URL
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            }

            if (!$this->isAllowedExtension($extension)) {
                return [
                    'success' => false,
                    'message' => 'File type not allowed',
                    'error' => "Extension: {$extension}",
                ];
            }

            // Generate filename
            $originalName = $validated['name'] ?? pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
            $originalName = Str::slug($originalName) ?: 'media';
            $filename = $originalName . '.' . $extension;

            // Store temporarily
            $tempPath = 'temp/' . Str::uuid() . '.' . $extension;
            Storage::disk('local')->put($tempPath, $response->body());
            $fullTempPath = Storage::disk('local')->path($tempPath);

            // Create Media record using Spatie Media Library
            // We'll create a "virtual" model to attach media to
            $media = Media::create([
                'model_type' => 'App\\Models\\Company', // Use Company as a generic holder
                'model_id' => 1,
                'collection_name' => $collection,
                'name' => $originalName,
                'file_name' => $filename,
                'mime_type' => $contentType,
                'disk' => 'local',
                'size' => strlen($response->body()),
                'manipulations' => [],
                'custom_properties' => [
                    'source_url' => $url,
                    'uploaded_via' => 'mcp',
                ],
                'generated_conversions' => [],
                'responsive_images' => [],
                'uuid' => Str::uuid(),
            ]);

            // Move file to media library location
            $mediaPath = 'media/' . $media->id . '/' . $filename;
            Storage::disk('local')->move($tempPath, $mediaPath);

            // Update media record with correct path
            $media->update([
                'disk' => 'local',
                'conversions_disk' => 'local',
            ]);

            $this->auditLog->log(
                'upload_from_url',
                'media',
                $media->id,
                [
                    'url' => $url,
                    'filename' => $filename,
                    'collection' => $collection,
                ]
            );

            return [
                'success' => true,
                'message' => 'Media uploaded successfully',
                'media_id' => $media->id,
                'filename' => $filename,
                'mime_type' => $contentType,
                'size' => $media->size,
                'path' => $mediaPath,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to upload media',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a media file
     *
     * @param array $args Arguments with 'id'
     * @return array
     */
    public function delete(array $args): array
    {
        $validator = Validator::make($args, [
            'id' => 'required|integer|exists:media,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $media = Media::findOrFail($args['id']);
            $mediaId = $media->id;
            $filename = $media->file_name;

            // Delete file from storage
            $mediaPath = 'media/' . $media->id;
            if (Storage::disk('local')->exists($mediaPath)) {
                Storage::disk('local')->deleteDirectory($mediaPath);
            }

            $media->delete();

            $this->auditLog->log(
                'delete',
                'media',
                $mediaId,
                ['filename' => $filename]
            );

            return [
                'success' => true,
                'message' => 'Media deleted successfully',
                'id' => $mediaId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete media',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMimeType(?string $mimeType): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
        ];

        return $map[$mimeType] ?? null;
    }

    /**
     * Check if extension is allowed
     */
    private function isAllowedExtension(?string $extension): bool
    {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'];
        return in_array(strtolower($extension ?? ''), $allowed);
    }
}
