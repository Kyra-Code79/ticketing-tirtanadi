<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfficeTypes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Create master_types table
        if (!$db->tableExists('master_types')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'nama_tipe' => ['type' => 'VARCHAR', 'constraint' => 100],
                'warna' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#6c757d'], // Hex Code
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('master_types', true);
        }

        // 2. Add type_id to master_kantor
        if (!$db->fieldExists('type_id', 'master_kantor')) {
            $this->forge->addColumn('master_kantor', [
                'type_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'nama_kantor'
                ]
            ]);
            $this->forge->addForeignKey('type_id', 'master_types', 'id', 'SET NULL', 'CASCADE');
        }

        // 3. Seed Default Data & Migrate Existing
        $seeder = \Config\Database::connect();
        
        // Check if types already seeded (simple check)
        $count = $seeder->table('master_types')->countAllResults();
        
        if ($count == 0) {
            // Define defaults and colors
            $defaults = [
                ['nama_tipe' => 'Pusat', 'warna' => '#dc3545'], // Red
                ['nama_tipe' => 'Pemasaran', 'warna' => '#0d6efd'], // Blue
                ['nama_tipe' => 'Produksi/IPAM', 'warna' => '#198754'], // Green
            ];

            foreach ($defaults as $def) {
                // Insert ignore or check first
                $seeder->table('master_types')->insert($def);
                $newId = $seeder->insertID();
                
                // Update existing records matching this name
                // Note: Old 'tipe' column values were exactly these strings.
                // Assuming 'tipe' column still exists or existed.
                if ($db->fieldExists('tipe', 'master_kantor')) {
                     $seeder->table('master_kantor')
                       ->where('tipe', $def['nama_tipe'])
                       ->update(['type_id' => $newId]);
                }
            }
        }
        
        // 4. Drop old 'tipe' column?
        if ($db->fieldExists('tipe', 'master_kantor')) {
             $this->forge->dropColumn('master_kantor', 'tipe');
        }
    }

    public function down()
    {
        $this->forge->addColumn('master_kantor', [
            'tipe' => ['type' => 'ENUM', 'constraint' => ['Pemasaran', 'Produksi/IPAM', 'Pusat'], 'null' => true]
        ]);
        $this->forge->dropForeignKey('master_kantor', 'master_kantor_type_id_foreign');
        $this->forge->dropColumn('master_kantor', 'type_id');
        $this->forge->dropTable('master_types');
    }
}
