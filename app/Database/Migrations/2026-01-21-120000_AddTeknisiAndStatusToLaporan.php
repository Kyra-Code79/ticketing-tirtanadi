<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeknisiAndStatusToLaporan extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Add id_teknisi column
        if (!$db->fieldExists('id_teknisi', 'laporan')) {
            $this->forge->addColumn('laporan', [
                'id_teknisi' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    // 'unsigned' => true, // Hypothethically Users.id is SIGNED
                    'null' => true,
                    'after' => 'id_kantor_tujuan'
                ]
            ]);
            // Add FK
            $this->forge->addForeignKey('id_teknisi', 'users', 'id', 'SET NULL', 'CASCADE', 'fk_laporan_teknisi');
            $this->forge->processIndexes('laporan'); 
        }

        // 2. Update Status Enum
        // Existing: ['Menunggu', 'Proses', 'Selesai', 'Ditolak']
        // New: ['Menunggu', 'Terverifikasi', 'Sedang Dikerjakan', 'Proses', 'Selesai', 'Ditolak']
        
        // CodeIgniter forge modifyColumn doesn't always handle ENUM expansion well across all drivers, 
        // but for MySQL it works by redefining the column.
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Menunggu', 'Terverifikasi', 'Sedang Dikerjakan', 'Proses', 'Selesai', 'Ditolak'],
                'default' => 'Menunggu'
            ]
        ];
        $this->forge->modifyColumn('laporan', $fields);
    }

    public function down()
    {
        // Revert enum? It's risky if data exists. keep it.
        // Drop FK and Column
        $this->forge->dropForeignKey('laporan', 'fk_laporan_teknisi');
        $this->forge->dropColumn('laporan', 'id_teknisi');
    }
}
