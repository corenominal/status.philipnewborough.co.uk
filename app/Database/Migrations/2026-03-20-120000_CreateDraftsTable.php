<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateDraftsTable extends Migration
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
            'content' => [
                'type' => 'TEXT',
            ],
            'media_ids' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('uuid');

        $this->forge->createTable('drafts');
    }

    public function down()
    {
        $this->forge->dropTable('drafts');
    }
}
