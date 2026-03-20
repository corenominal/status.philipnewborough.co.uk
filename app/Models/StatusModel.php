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
}
