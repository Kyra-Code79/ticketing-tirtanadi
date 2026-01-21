<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddTimestampsToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('created_at', 'users')) {
            $this->forge->addColumn('users', [
                'created_at' => [
                    'type'    => 'DATETIME',
                    'default' => new RawSql('CURRENT_TIMESTAMP'),
                    'after'   => 'id_kantor'
                ],
                'updated_at' => [
                    'type'    => 'DATETIME',
                    'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                    'after'   => 'created_at'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['created_at', 'updated_at']);
    }
}
