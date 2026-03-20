<?php

namespace App\Models;

use CodeIgniter\Model;

class ExampleModel extends Model
{
    protected $table            = 'example';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'role',
        'status',
        'joined',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
