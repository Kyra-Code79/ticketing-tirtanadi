<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailAndDetailToLaporan extends Migration
{
    public function up()
    {
        $fieldsToAdd = [];
        $db = \Config\Database::connect();

        if (!$db->fieldExists('email', 'laporan')) {
            $fieldsToAdd['email'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'no_hp'
            ];
        }

        if (!$db->fieldExists('detail_aduan', 'laporan')) {
            $fieldsToAdd['detail_aduan'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'isi_laporan'
            ];
        }
        
        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('laporan', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('laporan', 'email');
        $this->forge->dropColumn('laporan', 'detail_aduan');
    }
}
