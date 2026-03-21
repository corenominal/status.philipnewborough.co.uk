<?php

namespace App\Controllers\Admin;

use App\Models\StatusModel;

class Export extends BaseController
{
    public function index(): string
    {
        $statusModel  = new StatusModel();
        $totalStatuses = $statusModel->countAllResults();

        return view('admin/export', [
            'title'         => 'Export Data',
            'js'            => ['admin/export'],
            'css'           => ['admin/export'],
            'totalStatuses' => $totalStatuses,
        ]);
    }

    public function download(string $format): \CodeIgniter\HTTP\ResponseInterface
    {
        $allowed = ['json', 'sql', 'ai'];

        if (! in_array($format, $allowed, true)) {
            return $this->response->setStatusCode(404);
        }

        $statusModel = new StatusModel();
        $statuses    = $statusModel->orderBy('created_at', 'ASC')->findAll();

        return match ($format) {
            'json' => $this->downloadJson($statuses),
            'sql'  => $this->downloadSql($statuses),
            'ai'   => $this->downloadAi($statuses),
        };
    }

    // -------------------------------------------------------------------------

    private function downloadJson(array $statuses): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload  = json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'statuses-' . date('Y-m-d') . '.json';

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($payload))
            ->setBody($payload);
    }

    private function downloadSql(array $statuses): \CodeIgniter\HTTP\ResponseInterface
    {
        $lines   = [];
        $lines[] = '-- Status export generated ' . date('Y-m-d H:i:s');
        $lines[] = '-- Total records: ' . count($statuses);
        $lines[] = '';
        $lines[] = 'CREATE TABLE IF NOT EXISTS `statuses` (';
        $lines[] = '  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,';
        $lines[] = '  `uuid` varchar(36) NOT NULL,';
        $lines[] = '  `content` text NOT NULL,';
        $lines[] = '  `content_html` text NOT NULL,';
        $lines[] = '  `media_ids` json DEFAULT NULL,';
        $lines[] = '  `mastodon_id` varchar(32) DEFAULT NULL,';
        $lines[] = '  `in_reply_to_id` varchar(32) DEFAULT NULL,';
        $lines[] = '  `mastodon_url` varchar(512) DEFAULT NULL,';
        $lines[] = '  `created_at` datetime DEFAULT NULL,';
        $lines[] = '  `updated_at` datetime DEFAULT NULL,';
        $lines[] = '  `deleted_at` datetime DEFAULT NULL,';
        $lines[] = '  PRIMARY KEY (`id`)';
        $lines[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
        $lines[] = '';

        if (! empty($statuses)) {
            $lines[] = 'INSERT INTO `statuses` (`id`, `uuid`, `content`, `content_html`, `media_ids`, `mastodon_id`, `in_reply_to_id`, `mastodon_url`, `created_at`, `updated_at`, `deleted_at`) VALUES';

            $rows = [];

            foreach ($statuses as $s) {
                $mediaIds = is_array($s['media_ids']) ? json_encode($s['media_ids']) : ($s['media_ids'] ?? '[]');

                $rows[] = sprintf(
                    "  (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                    $this->sqlVal($s['id']),
                    $this->sqlVal($s['uuid']),
                    $this->sqlVal($s['content']),
                    $this->sqlVal($s['content_html']),
                    $this->sqlVal($mediaIds),
                    $this->sqlVal($s['mastodon_id']),
                    $this->sqlVal($s['in_reply_to_id']),
                    $this->sqlVal($s['mastodon_url']),
                    $this->sqlVal($s['created_at']),
                    $this->sqlVal($s['updated_at']),
                    $this->sqlVal($s['deleted_at'])
                );
            }

            $lines[] = implode(",\n", $rows) . ';';
        }

        $payload  = implode("\n", $lines);
        $filename = 'statuses-' . date('Y-m-d') . '.sql';

        return $this->response
            ->setHeader('Content-Type', 'application/sql; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($payload))
            ->setBody($payload);
    }

    private function downloadAi(array $statuses): \CodeIgniter\HTTP\ResponseInterface
    {
        $prompt = 'Analyze the following messages for my personal writing style. Look at sentence structure, '
            . 'level of formality, use of emojis, punctuation habits, and common vocabulary. '
            . 'Create a concise \'Style Guide\' I can use for future prompts.';

        $lines   = [];
        $lines[] = 'PROMPT';
        $lines[] = '======';
        $lines[] = $prompt;
        $lines[] = '';
        $lines[] = 'MESSAGES';
        $lines[] = '========';

        foreach ($statuses as $s) {
            $text = trim(strip_tags($s['content_html'] ?: $s['content']));

            if ($text !== '') {
                $lines[] = '---';
                $lines[] = $text;
            }
        }

        $payload  = implode("\n", $lines);
        $filename = 'statuses-ai-' . date('Y-m-d') . '.txt';

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($payload))
            ->setBody($payload);
    }

    // -------------------------------------------------------------------------

    private function sqlVal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return "'" . addslashes((string) $value) . "'";
    }
}
