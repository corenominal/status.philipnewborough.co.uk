<?php

/**
 * Status Importer CLI
 *
 * Usage:
 *   php spark status:import
 *   php spark status:import imports/statuses-2026-03-20-105027.json
 *   php spark status:import imports/statuses-2026-03-20-105027.json --limit 10
 *   php spark status:import imports/statuses-2026-03-20-105027.json --dry-run
 *   php spark status:import imports/statuses-2026-03-20-105027.json --replace
 *   php spark status:import imports/statuses-2026-03-20-105027.json --skip-download
 *
 * Flags:
 *   --limit N         Import the first N statuses only.
 *   --dry-run         Validate and process input without writing files/DB.
 *   --replace         Replace existing status rows that match by uuid.
 *   --skip-download   Skip image downloads; import metadata only.
 */

namespace App\Commands;

use App\Models\MediaModel;
use App\Models\StatusModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use RuntimeException;
use Throwable;

class ImportStatuses extends BaseCommand
{
    protected $group = 'Status';
    protected $name = 'status:import';
    protected $description = 'Import statuses and images from a JSON export file.';
    protected $usage = 'status:import [file] [--limit N] [--skip-download] [--dry-run] [--replace]';
    protected $arguments = [
        'file' => 'Path to import JSON file (default: imports/statuses-YYYY-MM-DD-HHMMSS.json).',
    ];
    protected $options = [
        '--limit'         => 'Only import the first N statuses from the file.',
        '--skip-download' => 'Do not download images. Media rows are still imported from source metadata.',
        '--dry-run'       => 'Validate/process input without writing to the database or filesystem.',
        '--replace'       => 'Replace existing status rows when UUID already exists.',
    ];

    private StatusModel $statusModel;
    private MediaModel $mediaModel;

    public function run(array $params)
    {
        $this->statusModel = model(StatusModel::class);
        $this->mediaModel  = model(MediaModel::class);

        $filePath = $this->resolveImportPath($params[0] ?? null);

        if (! is_file($filePath)) {
            CLI::error("Import file not found: {$filePath}");

            return EXIT_ERROR;
        }

        $json = file_get_contents($filePath);

        if ($json === false) {
            CLI::error("Unable to read import file: {$filePath}");

            return EXIT_ERROR;
        }

        $payload = json_decode($json, true);

        if (! is_array($payload) || ! isset($payload['statuses']) || ! is_array($payload['statuses'])) {
            CLI::error('Invalid import JSON. Expected a top-level "statuses" array.');

            return EXIT_ERROR;
        }

        $limit        = (int) (CLI::getOption('limit') ?? 0);
        $skipDownload = (bool) CLI::getOption('skip-download');
        $dryRun       = (bool) CLI::getOption('dry-run');
        $replace      = (bool) CLI::getOption('replace');
        $mediaDir     = ROOTPATH . 'public/media';

        if (! $dryRun && ! is_dir($mediaDir) && ! mkdir($mediaDir, 0775, true) && ! is_dir($mediaDir)) {
            CLI::error("Unable to create media directory: {$mediaDir}");

            return EXIT_ERROR;
        }

        $statuses = $payload['statuses'];

        if ($limit > 0) {
            $statuses = array_slice($statuses, 0, $limit);
        }

        $total = count($statuses);

        CLI::write("Starting import: {$total} statuses", 'yellow');
        CLI::write("Source file: {$filePath}");
        CLI::write('Options: ' . implode(', ', array_filter([
            $dryRun ? 'dry-run' : null,
            $skipDownload ? 'skip-download' : null,
            $replace ? 'replace' : null,
            $limit > 0 ? "limit={$limit}" : null,
        ])));
        CLI::newLine();

        $imported  = 0;
        $skipped   = 0;
        $failed    = 0;
        $mediaRows = 0;

        foreach ($statuses as $index => $status) {
            $position = $index + 1;

            if (! is_array($status)) {
                $failed++;
                CLI::error("[{$position}/{$total}] Invalid status payload (not an object). Skipping.");

                continue;
            }

            $uuid = trim((string) ($status['guid'] ?? ''));

            if ($uuid === '') {
                $failed++;
                CLI::error("[{$position}/{$total}] Missing status guid. Skipping.");

                continue;
            }

            CLI::write("[{$position}/{$total}] Processing status {$uuid}");

            try {
                $existingStatus = $this->statusModel->where('uuid', $uuid)->first();

                if ($existingStatus && ! $replace) {
                    $skipped++;
                    CLI::write('  - Status already exists, skipping (use --replace to overwrite).', 'yellow');

                    continue;
                }

                $mediaIds = $this->importMediaItems(
                    $status,
                    $skipDownload,
                    $dryRun,
                    $mediaDir,
                    $mediaRows
                );

                if (! $dryRun) {
                    if ($existingStatus && $mediaIds === []) {
                        $existingMediaIds = $existingStatus['media_ids'] ?? [];

                        if (is_array($existingMediaIds) && $existingMediaIds !== []) {
                            $mediaIds = $existingMediaIds;
                        }
                    }

                    if ($existingStatus) {
                        $this->statusModel->update((int) $existingStatus['id'], $this->buildStatusPayload($status, $mediaIds));
                    } else {
                        $this->statusModel->insert($this->buildStatusPayload($status, $mediaIds));
                    }

                    if ($this->statusModel->errors() !== []) {
                        throw new RuntimeException(implode('; ', $this->statusModel->errors()));
                    }
                }

                $imported++;
                CLI::write('  - Status imported.', 'green');
            } catch (Throwable $e) {
                $failed++;
                CLI::error("  - Failed: {$e->getMessage()}");
            }
        }

        CLI::newLine();
        CLI::write('Import complete', 'green');
        CLI::write("Imported: {$imported}");
        CLI::write("Skipped: {$skipped}");
        CLI::write("Failed: {$failed}");
        CLI::write("Media rows imported: {$mediaRows}");

        return $failed > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }

    private function importMediaItems(
        array $status,
        bool $skipDownload,
        bool $dryRun,
        string $mediaDir,
        int &$mediaRows
    ): array {
        $mediaIds = [];
        $images   = is_array($status['images'] ?? null) ? $status['images'] : [];

        foreach ($images as $image) {
            if (! is_array($image)) {
                continue;
            }

            $sourceUrl = trim((string) ($image['url'] ?? ''));

            if ($sourceUrl === '') {
                continue;
            }

            $fileExt = $this->resolveFileExtension($image, $sourceUrl);
            $uuid    = $this->createUuidV4();
            $file    = "{$uuid}.{$fileExt}";
            $path    = rtrim($mediaDir, '/') . '/' . $file;

            $mimeType = trim((string) ($image['mime_type'] ?? ''));
            $width    = (int) ($image['width'] ?? 0);
            $height   = (int) ($image['height'] ?? 0);
            $filesize = trim((string) ($image['filesize'] ?? ''));

            if (! $skipDownload && ! $dryRun) {
                $downloaded = $this->downloadImage($sourceUrl, $path);

                if (! $downloaded) {
                    CLI::write("  - Failed to download image: {$sourceUrl}", 'red');

                    continue;
                }

                if ($mimeType === '') {
                    $detectedMime = mime_content_type($path);

                    if (is_string($detectedMime)) {
                        $mimeType = $detectedMime;
                    }
                }

                if ($width === 0 || $height === 0) {
                    $imageInfo = @getimagesize($path);

                    if (is_array($imageInfo)) {
                        $width  = (int) ($imageInfo[0] ?? 0);
                        $height = (int) ($imageInfo[1] ?? 0);
                    }
                }

                $sizeBytes = @filesize($path);

                if ($sizeBytes !== false) {
                    $filesize = (string) $sizeBytes;
                }
            }

            $mediaPayload = [
                'uuid'        => $uuid,
                'file_name'   => $file,
                'description' => trim((string) ($image['description'] ?? '')),
                'file_ext'    => $fileExt,
                'mime_type'   => $mimeType,
                'width'       => $width,
                'height'      => $height,
                'filesize'    => $filesize,
                'created_at'  => $status['created_at'] ?? null,
                'updated_at'  => $status['updated_at'] ?? null,
            ];

            if ($dryRun) {
                $mediaIds[] = 0;
                $mediaRows++;

                continue;
            }

            $this->mediaModel->insert($mediaPayload);

            if ($this->mediaModel->errors() !== []) {
                throw new RuntimeException('Media insert failed: ' . implode('; ', $this->mediaModel->errors()));
            }

            $mediaIds[] = (int) $this->mediaModel->getInsertID();
            $mediaRows++;
        }

        return $mediaIds;
    }

    private function buildStatusPayload(array $status, array $mediaIds): array
    {
        return [
            'uuid'          => trim((string) ($status['guid'] ?? '')),
            'content'       => (string) ($status['content'] ?? ''),
            'content_html'  => (string) ($status['content_html'] ?? ''),
            'media_ids'     => $mediaIds,
            'mastodon_id'   => (string) ($status['mastodon_id'] ?? ''),
            'in_reply_to_id'=> (string) ($status['in_reply_to_id'] ?? ''),
            'mastodon_url'  => (string) ($status['mastodon_url'] ?? ''),
            'created_at'    => $status['created_at'] ?? null,
            'updated_at'    => $status['updated_at'] ?? null,
            'deleted_at'    => $status['deleted_at'] ?? null,
        ];
    }

    private function downloadImage(string $url, string $destination): bool
    {
        try {
            $requestUrl = $this->normalizeUrl($url);

            $http = Services::curlrequest([
                'timeout' => 30,
            ]);

            $response = $http->request('GET', $requestUrl, [
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                return false;
            }

            $body = $response->getBody();

            if (! is_string($body) || $body === '') {
                return false;
            }

            return file_put_contents($destination, $body) !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function normalizeUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return str_replace(' ', '%20', $url);
        }

        $scheme = $parts['scheme'] . '://';
        $auth   = '';

        if (isset($parts['user'])) {
            $auth = $parts['user'];

            if (isset($parts['pass'])) {
                $auth .= ':' . $parts['pass'];
            }

            $auth .= '@';
        }

        $host = $parts['host'];
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';

        if ($path !== '') {
            $segments = explode('/', $path);
            $segments = array_map(
                static fn (string $segment): string => rawurlencode(rawurldecode($segment)),
                $segments
            );
            $path = implode('/', $segments);

            if (str_starts_with($parts['path'], '/') && ! str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
        }

        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $auth . $host . $port . $path . $query . $fragment;
    }

    private function resolveImportPath(?string $path): string
    {
        if ($path === null || trim($path) === '') {
            $latest = glob(ROOTPATH . 'imports/statuses-*.json');

            if ($latest !== false && $latest !== []) {
                rsort($latest);

                return (string) $latest[0];
            }

            return ROOTPATH . 'imports/statuses.json';
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return ROOTPATH . ltrim($path, '/');
    }

    private function resolveFileExtension(array $image, string $sourceUrl): string
    {
        $ext = strtolower(trim((string) ($image['file_ext'] ?? '')));

        if ($ext === '') {
            $path = parse_url($sourceUrl, PHP_URL_PATH);
            $ext  = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));
        }

        $ext = preg_replace('/[^a-z0-9]/', '', $ext ?? '') ?? '';

        return $ext !== '' ? $ext : 'jpg';
    }

    private function createUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}