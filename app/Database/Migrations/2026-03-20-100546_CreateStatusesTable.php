<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateStatusesTable extends Migration
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
            'content_html' => [
                'type' => 'TEXT',
            ],
            'media_ids' => [
                'type' => 'TEXT',
            ],
            'mastodon_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => '',
            ],
            'in_reply_to_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => '',
            ],
            'mastodon_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        
        // Recommended: Index for uuid and mastodon_id for faster API lookups
        $this->forge->addKey('uuid');
        $this->forge->addKey('mastodon_id');

        $this->forge->createTable('statuses');
    }

    public function down()
    {
        $this->forge->dropTable('statuses');
    }
}