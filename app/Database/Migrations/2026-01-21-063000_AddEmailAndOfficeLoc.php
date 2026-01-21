<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailAndOfficeLoc extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add Email to Laporan table
        if (!$db->fieldExists('email', 'laporan')) {
            $this->forge->addColumn('laporan', [
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'no_hp'
                ]
            ]);
        }

        // Add Lat/Long to Master Kantor table
        if (!$db->fieldExists('latitude', 'master_kantor')) {
            $this->forge->addColumn('master_kantor', [
                'latitude' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,8',
                    'null' => true,
                    'after' => 'alamat_kantor'
                ],
                'longitude' => [
                    'type' => 'DECIMAL',
                    'constraint' => '11,8',
                    'null' => true,
                    'after' => 'latitude'
                ]
            ]);
        }
        
        // Add detail_aduan
        if (!$db->fieldExists('detail_aduan', 'laporan')) {
             $this->forge->addColumn('laporan', [
                'detail_aduan' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'isi_laporan'
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('laporan', 'email');
        $this->forge->dropColumn('laporan', 'detail_aduan');
        $this->forge->dropColumn('master_kantor', 'latitude');
        $this->forge->dropColumn('master_kantor', 'longitude');
    }
}
