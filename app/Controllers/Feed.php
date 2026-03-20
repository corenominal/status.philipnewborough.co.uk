<?php

namespace App\Controllers;

use App\Models\MediaModel;
use App\Models\StatusModel;
use CodeIgniter\HTTP\ResponseInterface;

class Feed extends BaseController
{
    public function rss(): ResponseInterface
    {
        $limit = 20;

        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();

        $statuses = $statusModel
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);

        // Collect all media IDs referenced by these statuses
        $mediaIds = [];

        foreach ($statuses as $status) {
            if (! isset($status['media_ids']) || ! is_array($status['media_ids'])) {
                continue;
            }

            foreach ($status['media_ids'] as $mediaId) {
                $id = (int) $mediaId;

                if ($id > 0) {
                    $mediaIds[] = $id;
                }
            }
        }

        $mediaById = [];

        if ($mediaIds !== []) {
            $mediaRows = $mediaModel->whereIn('id', array_values(array_unique($mediaIds)))->findAll();

            foreach ($mediaRows as $mediaRow) {
                $mediaById[(int) $mediaRow['id']] = [
                    'id'          => (int) $mediaRow['id'],
                    'description' => (string) ($mediaRow['description'] ?? ''),
                    'url'         => '/media/' . ($mediaRow['file_name'] ?? ''),
                    'mimeType'    => (string) ($mediaRow['mime_type'] ?? ''),
                    'width'       => (int) ($mediaRow['width'] ?? 0),
                    'height'      => (int) ($mediaRow['height'] ?? 0),
                    'filesize'    => (int) ($mediaRow['filesize'] ?? 0),
                ];
            }
        }

        // Attach media to each status
        foreach ($statuses as $index => $status) {
            $statuses[$index]['media'] = [];

            if (! isset($status['media_ids']) || ! is_array($status['media_ids'])) {
                continue;
            }

            foreach ($status['media_ids'] as $mediaId) {
                $id = (int) $mediaId;

                if (isset($mediaById[$id])) {
                    $statuses[$index]['media'][] = $mediaById[$id];
                }
            }
        }

        $siteUrl  = rtrim((string) config('App')->baseURL, '/');
        $siteName = (string) config('App')->siteName;

        $xml = view('feed/rss', [
            'statuses' => $statuses,
            'siteUrl'  => $siteUrl,
            'siteName' => $siteName,
            'feedUrl'  => $siteUrl . '/feed/rss',
        ]);

        return $this->response
            ->setContentType('application/rss+xml; charset=UTF-8')
            ->setBody($xml);
    }
}
