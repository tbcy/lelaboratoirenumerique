<?php

namespace App\Services\Mcp\Resources;

use App\Services\Mcp\PaginationService;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    /**
     * List all media files
     *
     * @param array $args Optional filters
     * @return array
     */
    public function list(array $args = []): array
    {
        $query = Media::query()->orderBy('created_at', 'desc');

        // Filter by collection
        if (!empty($args['collection'])) {
            $query->where('collection_name', $args['collection']);
        }

        // Filter by mime type
        if (!empty($args['mime_type'])) {
            $query->where('mime_type', 'like', $args['mime_type'] . '%');
        }

        // Search by name
        if (!empty($args['search'])) {
            $query->where(function ($q) use ($args) {
                $q->where('name', 'like', '%' . $args['search'] . '%')
                    ->orWhere('file_name', 'like', '%' . $args['search'] . '%');
            });
        }

        $media = $this->pagination->paginate($query, $args);

        return [
            'success' => true,
            'data' => $media->map(fn ($item) => $this->formatMedia($item))->toArray(),
            'pagination' => $this->pagination->getMeta($media),
        ];
    }

    /**
     * Get a single media file
     *
     * @param array $args Arguments with 'id'
     * @return array
     */
    public function get(array $args): array
    {
        if (empty($args['id'])) {
            return [
                'success' => false,
                'message' => 'Media ID is required',
            ];
        }

        $media = Media::find($args['id']);

        if (!$media) {
            return [
                'success' => false,
                'message' => 'Media not found',
            ];
        }

        return [
            'success' => true,
            'data' => $this->formatMedia($media, true),
        ];
    }

    /**
     * Format media for API response
     */
    private function formatMedia(Media $media, bool $detailed = false): array
    {
        $data = [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'size_human' => $this->formatBytes($media->size),
            'collection' => $media->collection_name,
            'created_at' => $media->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['custom_properties'] = $media->custom_properties;
            $data['path'] = 'media/' . $media->id . '/' . $media->file_name;
            $data['exists'] = Storage::disk('local')->exists('media/' . $media->id . '/' . $media->file_name);
        }

        return $data;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
