<?php

namespace App\Controllers;

use App\Libraries\MastodonPoster;
use App\Models\DraftModel;
use App\Models\MediaModel;
use App\Models\StatusModel;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    /**
     * Display the home page
     *
     * Renders the home view with associated stylesheets and scripts.
     * Sets up the page title and passes data to the view layer.
     *
     * @return string The rendered home view
     */
    public function index(): string
    {
        $limit = 20;
        $query = trim((string) $this->request->getGet('q'));
        $timelineData = $this->getTimelineBatch(0, $limit, $query);
        $mastodonProfile = (string) config('Mastodon')->profile;
        $mastodonHandle =  (string) config('Mastodon')->account;
        $mastodonEnabled = (new MastodonPoster())->isEnabled();

        // Array of javascript files to include
        $data['js'] = ['home'];
        // Array of CSS files to include
        $data['css'] = ['home'];
        // Set the page title
        $data['title'] = 'Status Timeline';
        $data['statuses'] = $timelineData['statuses'];
        $data['hasMoreStatuses'] = $timelineData['hasMore'];
        $data['statusBatchSize'] = $limit;
        $data['searchQuery'] = $query;
        $data['mastodonHandle'] = $mastodonHandle;
        $data['mastodonProfile'] = $mastodonProfile;
        $data['mastodonEnabled'] = $mastodonEnabled;

        $data['draftCount'] = 0;

        if (session()->get('is_admin')) {
            $draftModel = new DraftModel();
            $data['draftCount'] = $draftModel->countAllResults();
        }

        return view('home', $data);
    }

    public function show(string $uuid): string
    {
        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();

        $status = $statusModel->where('uuid', $uuid)->first();

        if ($status === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Status not found.');
        }

        $mediaById = [];

        if (isset($status['media_ids']) && is_array($status['media_ids']) && $status['media_ids'] !== []) {
            $mediaRows = $mediaModel
                ->whereIn('id', array_values(array_unique(array_map('intval', $status['media_ids']))))
                ->findAll();

            foreach ($mediaRows as $mediaRow) {
                $mediaById[(int) $mediaRow['id']] = [
                    'id'          => (int) $mediaRow['id'],
                    'description' => (string) ($mediaRow['description'] ?? ''),
                    'url'         => '/media/' . ($mediaRow['file_name'] ?? ''),
                    'mimeType'    => (string) ($mediaRow['mime_type'] ?? ''),
                ];
            }
        }

        $status['media'] = [];

        if (isset($status['media_ids']) && is_array($status['media_ids'])) {
            foreach ($status['media_ids'] as $mediaId) {
                $mid = (int) $mediaId;

                if (isset($mediaById[$mid])) {
                    $status['media'][] = $mediaById[$mid];
                }
            }
        }

        $mastodonProfile = (string) config('Mastodon')->profile;
        $mastodonHandle  = (string) config('Mastodon')->account;

        $data['css']             = ['home'];
        $data['js']              = ['home'];
        $data['title']           = 'Status';
        $data['status']          = $status;
        $data['mastodonHandle']  = $mastodonHandle;
        $data['mastodonProfile'] = $mastodonProfile;

        return view('status', $data);
    }

    public function loadMoreStatuses(): ResponseInterface
    {
        $offset = max(0, (int) $this->request->getGet('offset'));
        $limit = (int) $this->request->getGet('limit');
        $query = trim((string) $this->request->getGet('q'));

        if ($limit <= 0) {
            $limit = 20;
        }

        if ($limit > 50) {
            $limit = 50;
        }

        $timelineData = $this->getTimelineBatch($offset, $limit, $query);

        $mastodonProfile = (string) config('Mastodon')->profile;
        $mastodonHandle = (string) config('Mastodon')->account;

        $html = view('home/partials/timeline_items', [
            'statuses' => $timelineData['statuses'],
            'mastodonHandle' => $mastodonHandle,
            'mastodonProfile' => $mastodonProfile,
        ]);

        return $this->response->setJSON([
            'statuses' => $timelineData['statuses'],
            'html' => $html,
            'nextOffset' => $offset + count($timelineData['statuses']),
            'hasMore' => $timelineData['hasMore'],
        ]);
    }

    /**
     * @return array{statuses: array<int, array<string, mixed>>, hasMore: bool}
     */
    private function getTimelineBatch(int $offset, int $limit, string $query = ''): array
    {
        $statusModel = new StatusModel();
        $mediaModel = new MediaModel();

        if ($query !== '') {
            $statusModel
                ->groupStart()
                ->like('content', $query)
                ->orLike('content_html', $query)
                ->groupEnd();
        }

        $rows = $statusModel
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit + 1, $offset);

        $hasMore = count($rows) > $limit;
        $statuses = array_slice($rows, 0, $limit);

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
                    'id' => (int) $mediaRow['id'],
                    'description' => (string) ($mediaRow['description'] ?? ''),
                    'url' => '/media/' . ($mediaRow['file_name'] ?? ''),
                    'mimeType' => (string) ($mediaRow['mime_type'] ?? ''),
                ];
            }
        }

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

        return [
            'statuses' => $statuses,
            'hasMore' => $hasMore,
        ];
    }
}
