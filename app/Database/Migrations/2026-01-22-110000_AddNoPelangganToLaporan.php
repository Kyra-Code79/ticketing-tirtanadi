<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoPelangganToLaporan extends Migration
{
    public function up()
    {
        // Check if column exists first to avoid errors
        $db = \Config\Database::connect();
        if (!$db->fieldExists('no_pelanggan', 'laporan')) {
            $this->forge->addColumn('laporan', [
                'no_pelanggan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'nama_pelapor'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('laporan', 'no_pelanggan');
    }
}
