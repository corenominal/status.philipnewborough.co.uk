<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateMediaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => '',
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => '',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => '',
            ],
            'file_ext' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => '',
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => '',
            ],
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'height' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'filesize' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => '',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('media');
    }

    public function down()
    {
        $this->forge->dropTable('media');
    }
}