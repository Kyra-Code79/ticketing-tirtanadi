<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // 1. SETTINGS
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'setting_key' => ['type' => 'VARCHAR', 'constraint' => 50],
            'setting_value' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('system_settings');

        // 2. MASTER KANTOR
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_kantor' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['Pemasaran', 'Produksi/IPAM', 'Pusat']],
            'alamat_kantor' => ['type' => 'TEXT', 'null' => true],
            'email_notifikasi' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('master_kantor');

        // 3. COVERAGE AREA
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kantor' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_kecamatan' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kantor', 'master_kantor', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('coverage_area');

        // 4. LAPORAN
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nomor_tiket' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_pelapor' => ['type' => 'VARCHAR', 'constraint' => 100],
            'no_hp' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_kecamatan' => ['type' => 'VARCHAR', 'constraint' => 100],
            'alamat_detail' => ['type' => 'TEXT'],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'id_kantor_tujuan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'isi_laporan' => ['type' => 'TEXT'],
            'foto_bukti' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Menunggu', 'Terverifikasi', 'Sedang Dikerjakan', 'Selesai', 'Ditolak'], 'default' => 'Menunggu'],
            'is_urgent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kantor_tujuan', 'master_kantor', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('laporan');

        // 5. USERS
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 50],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => 100],
            'role' => ['type' => 'ENUM', 'constraint' => ['Super Admin', 'Admin Cabang']],
            'id_kantor' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
        $this->forge->dropTable('laporan');
        $this->forge->dropTable('coverage_area');
        $this->forge->dropTable('master_kantor');
        $this->forge->dropTable('system_settings');
    }
}
