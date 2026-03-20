<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table            = 'statuses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'id',
        'uuid',
        'content',
        'content_html',
        'media_ids',
        'mastodon_id',
        'in_reply_to_id',
        'mastodon_url',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['encodeMediaIds'];
    protected $beforeUpdate = ['encodeMediaIds'];
    protected $afterFind    = ['decodeMediaIds'];

    protected function encodeMediaIds(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data']) || ! array_key_exists('media_ids', $data['data'])) {
            return $data;
        }

        $value = $data['data']['media_ids'];

        if (is_array($value)) {
            $data['data']['media_ids'] = json_encode(array_values(array_map('intval', $value)));

            return $data;
        }

        if ($value === null || $value === '') {
            $data['data']['media_ids'] = '[]';
        }

        return $data;
    }

    protected function decodeMediaIds(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        if (isset($data['data'][0]) && is_array($data['data'])) {
            foreach ($data['data'] as $index => $row) {
                if (is_array($row) && array_key_exists('media_ids', $row)) {
                    $data['data'][$index]['media_ids'] = $this->parseMediaIds($row['media_ids']);
                }
            }

            return $data;
        }

        if (is_array($data['data']) && array_key_exists('media_ids', $data['data'])) {
            $data['data']['media_ids'] = $this->parseMediaIds($data['data']['media_ids']);
        }

        return $data;
    }

    private function parseMediaIds($value): array
    {
        if (is_array($value)) {
            return array_values(array_map('intval', $value));
        }

        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_map('intval', $decoded));
        }

        if (ctype_digit($value)) {
            return [(int) $value];
        }

        $pieces = array_filter(array_map('trim', explode(',', $value)));

        return array_values(array_map('intval', $pieces));
    }
}
