<?php

namespace App\Controllers;

use App\Models\MediaModel;
use App\Models\StatusModel;

class Gallery extends BaseController
{
    /**
     * Display the media gallery page.
     *
     * Fetches all image media attached to published statuses, randomises the
     * order for each page load, and passes them to the gallery view.
     *
     * @return string The rendered gallery view
     */
    public function index(): string
    {
        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();

        // Fetch all non-deleted statuses that have at least one media attachment.
        $statuses = $statusModel
            ->select('uuid, media_ids')
            ->where('deleted_at IS NULL')
            ->where('media_ids IS NOT NULL')
            ->where('media_ids !=', 'null')
            ->where('media_ids !=', '[]')
            ->findAll();

        // Build a media_id → status_uuid lookup map.
        $mediaStatusMap = [];

        foreach ($statuses as $status) {
            $mediaIds = $status['media_ids'];

            if (! is_array($mediaIds) || $mediaIds === []) {
                continue;
            }

            foreach ($mediaIds as $mediaId) {
                $mediaStatusMap[(int) $mediaId] = $status['uuid'];
            }
        }

        $mediaItems = [];

        if (! empty($mediaStatusMap)) {
            $mediaRows = $mediaModel
                ->whereIn('id', array_keys($mediaStatusMap))
                ->like('mime_type', 'image/', 'after')
                ->findAll();

            foreach ($mediaRows as $row) {
                $statusUuid = $mediaStatusMap[(int) $row['id']] ?? null;

                if ($statusUuid === null) {
                    continue;
                }

                $mediaItems[] = [
                    'url'         => '/media/' . $row['file_name'],
                    'description' => (string) ($row['description'] ?? ''),
                    'width'       => max(1, (int) $row['width']),
                    'height'      => max(1, (int) $row['height']),
                    'status_uuid' => $statusUuid,
                ];
            }

            shuffle($mediaItems);
        }

        return view('gallery', [
            'js'         => ['gallery', 'shared/network-animation'],
            'css'        => ['gallery'],
            'title'      => 'Media Gallery',
            'mediaItems' => $mediaItems,
        ]);
    }
}
