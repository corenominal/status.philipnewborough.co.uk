<?php

namespace App\Controllers\Api;

use App\Libraries\Markdown;
use App\Libraries\MastodonPoster;
use App\Models\MediaModel;
use App\Models\StatusModel;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;

class Statuses extends BaseController
{
    /**
     * GET /api/statuses/latest
     * Fetch the most recent published statuses.
     * Accepts optional query params: limit (1–100, default 20), offset (default 0).
     */
    public function latest(): ResponseInterface
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 20);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();

        $total    = $statusModel->countAllResults(false);
        $statuses = $statusModel
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);

        $result = [];

        foreach ($statuses as $status) {
            $media    = $this->hydrateMedia($this->parseMediaIds($status['media_ids'] ?? null), $mediaModel);
            $result[] = [
                'uuid'         => $status['uuid'],
                'content'      => $status['content'],
                'content_html' => $status['content_html'],
                'media'        => $media,
                'mastodon_url' => $status['mastodon_url'],
                'created_at'   => $status['created_at'],
                'updated_at'   => $status['updated_at'],
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $result,
            'meta'   => [
                'total'  => $total,
                'limit'  => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * GET /api/statuses/:id
     * Fetch a single status by ID (admin only).
     */
    public function get(int $id): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();
        $status      = $statusModel->find($id);

        if (! $status) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Status not found.']);
        }

        $status['media'] = $this->hydrateMedia($this->parseMediaIds($status['media_ids'] ?? null), $mediaModel);

        return $this->response->setJSON(['status' => 'success', 'data' => $status]);
    }

    /**
     * POST /api/statuses
     * Create a new status (admin only).
     */
    public function create(): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $content = trim((string) ($this->request->getPost('content') ?? ''));

        if ($content === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Content is required.']);
        }

        $mediaIds       = $this->parseMediaIds($this->request->getPost('media_ids'));
        $postToMastodon = $this->request->getPost('post_to_mastodon') === '1';
        $contentHtml    = $this->toHtml($content);
        $uuid           = Uuid::uuid4()->toString();

        $statusModel = new StatusModel();
        $statusModel->insert([
            'uuid'         => $uuid,
            'content'      => $content,
            'content_html' => $contentHtml,
            'media_ids'    => $mediaIds,
        ]);

        $insertId = $statusModel->getInsertID();
        $status   = $statusModel->find($insertId);

        if ($postToMastodon) {
            $mastodon = new MastodonPoster();

            if ($mastodon->isEnabled()) {
                try {
                    $mediaItems = [];

                    if (! empty($mediaIds)) {
                        $mediaModel = new MediaModel();
                        $mediaItems = $mediaModel->whereIn('id', $mediaIds)->findAll();
                    }

                    $mastodonData = $mastodon->post($content, $mediaItems);
                    $statusModel->update($insertId, [
                        'mastodon_id'  => $mastodonData['mastodon_id'],
                        'mastodon_url' => $mastodonData['mastodon_url'],
                    ]);
                    $status = $statusModel->find($insertId);
                } catch (\Throwable $e) {
                    log_message('error', 'MastodonPoster::post failed: ' . $e->getMessage());
                }
            }
        }

        return $this->response->setStatusCode(201)->setJSON(['status' => 'success', 'data' => $status]);
    }

    /**
     * PATCH /api/statuses/:id
     * Update an existing status (admin only).
     */
    public function update(int $id): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $statusModel = new StatusModel();
        $status      = $statusModel->find($id);

        if (! $status) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Status not found.']);
        }

        $body    = $this->request->getJSON(true) ?: [];
        $post    = $this->request->getPost() ?: [];
        $input   = array_merge($post, $body);

        $update = [];

        if (array_key_exists('content', $input)) {
            $content = trim((string) $input['content']);

            if ($content === '') {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Content cannot be empty.']);
            }

            $update['content']      = $content;
            $update['content_html'] = $this->toHtml($content);
        }

        if (array_key_exists('media_ids', $input)) {
            $update['media_ids'] = $this->parseMediaIds($input['media_ids']);
        }

        if (! empty($update)) {
            $statusModel->update($id, $update);
        }

        // Sync to Mastodon if content or media changed and the status has a mastodon_id.
        $mastodonSyncNeeded = ! empty($status['mastodon_id'])
            && (isset($update['content']) || isset($update['media_ids']));

        if ($mastodonSyncNeeded) {
            $mastodon = new MastodonPoster();

            if ($mastodon->isEnabled()) {
                try {
                    $finalContent  = $update['content'] ?? $status['content'];
                    $finalMediaIds = $update['media_ids'] ?? $this->parseMediaIds($status['media_ids'] ?? null);
                    $mediaItems    = [];

                    if (! empty($finalMediaIds)) {
                        $mediaModel = new MediaModel();
                        $mediaItems = $mediaModel->whereIn('id', $finalMediaIds)->findAll();
                    }

                    $mastodon->update((string) $status['mastodon_id'], $finalContent, $mediaItems);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to update Mastodon status {id}: {message}', [
                        'id'      => $status['mastodon_id'],
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $statusModel->find($id)]);
    }

    /**
     * DELETE /api/statuses/:id
     * Soft-delete a status and permanently remove any attached media (admin only).
     */
    public function delete(int $id): ResponseInterface
    {
        if (empty($GLOBALS['is_admin'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden.']);
        }

        $statusModel = new StatusModel();
        $status      = $statusModel->find($id);

        if (! $status) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Status not found.']);
        }

        // Remove attached media records and their files from disk.
        $mediaIds = $this->parseMediaIds($status['media_ids'] ?? null);

        if (! empty($mediaIds)) {
            $mediaModel = new MediaModel();
            $mediaRows  = $mediaModel->whereIn('id', $mediaIds)->findAll();

            foreach ($mediaRows as $row) {
                $filePath = FCPATH . 'media/' . basename((string) $row['file_name']);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $mediaModel->whereIn('id', $mediaIds)->delete();
        }

        // Delete from Mastodon if the status was posted there.
        if (! empty($status['mastodon_id'])) {
            $mastodon = new MastodonPoster();

            if ($mastodon->isEnabled()) {
                try {
                    $mastodon->delete((string) $status['mastodon_id']);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to delete Mastodon status {id}: {message}', [
                        'id'      => $status['mastodon_id'],
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        $statusModel->delete($id);

        return $this->response->setJSON(['status' => 'success']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function toHtml(string $content): string
    {
        $content = nl2br($content);
        try {
            $markdown = new Markdown();
            $markdown->setMarkdown($content);
            $result = $markdown->convert();

            if (isset($result['html']) && $result['html'] !== '') {
                return $result['html'];
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Markdown conversion failed: ' . $e->getMessage());
        }

        // Fallback: wrap paragraphs manually
        $escaped    = esc($content);
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $escaped)));

        if (empty($paragraphs)) {
            return '<p>' . nl2br($escaped) . '</p>';
        }

        return implode('', array_map(fn ($p) => '<p>' . nl2br($p) . '</p>', $paragraphs));
    }

    private function parseMediaIds(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = $raw !== null ? [$raw] : [];
        }

        return array_values(array_filter(array_map('intval', $raw), fn ($id) => $id > 0));
    }

    private function hydrateMedia(array $mediaIds, MediaModel $mediaModel): array
    {
        if (empty($mediaIds)) {
            return [];
        }

        $ids   = array_values(array_filter(array_map('intval', $mediaIds), fn ($id) => $id > 0));
        $rows  = $mediaModel->whereIn('id', $ids)->findAll();
        $byId  = [];

        foreach ($rows as $row) {
            $byId[(int) $row['id']] = [
                'id'          => (int) $row['id'],
                'description' => (string) ($row['description'] ?? ''),
                'url'         => '/media/' . ($row['file_name'] ?? ''),
                'mime_type'   => (string) ($row['mime_type'] ?? ''),
            ];
        }

        $media = [];

        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $media[] = $byId[$id];
            }
        }

        return $media;
    }
}
