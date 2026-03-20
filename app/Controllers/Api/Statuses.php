<?php

namespace App\Controllers\Api;

use App\Libraries\Markdown;
use App\Models\MediaModel;
use App\Models\StatusModel;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;

class Statuses extends BaseController
{
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

        $mediaIds    = $this->parseMediaIds($this->request->getPost('media_ids'));
        $contentHtml = $this->toHtml($content);
        $uuid        = Uuid::uuid4()->toString();

        $statusModel = new StatusModel();
        $statusModel->insert([
            'uuid'         => $uuid,
            'content'      => $content,
            'content_html' => $contentHtml,
            'media_ids'    => $mediaIds,
        ]);

        $status = $statusModel->find($statusModel->getInsertID());

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
