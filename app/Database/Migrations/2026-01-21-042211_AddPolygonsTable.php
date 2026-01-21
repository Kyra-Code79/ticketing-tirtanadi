<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPolygonsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '#3388ff',
            ],
            'geometry' => [
                'type' => 'LONGTEXT', // Storing GeoJSON
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('polygons');
    }

    public function down()
    {
        $this->forge->dropTable('polygons');
    }
}
