<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Settings
        $data_settings = [
            ['setting_key' => 'alert_radius_meter', 'setting_value' => '500', 'description' => 'Jarak radius untuk deteksi pengelompokan gangguan (meter)'],
            ['setting_key' => 'alert_threshold_count', 'setting_value' => '5', 'description' => 'Jumlah minimal laporan untuk trigger Zona Merah'],
            ['setting_key' => 'map_center_lat', 'setting_value' => '3.595196', 'description' => 'Titik tengah default peta (Medan)'],
            ['setting_key' => 'map_center_long', 'setting_value' => '98.672223', 'description' => 'Titik tengah default peta (Medan)'],
        ];
        $this->db->table('system_settings')->insertBatch($data_settings);

        // 2. Master Kantor
        $data_kantor = [
            ['id' => 1, 'nama_kantor' => 'Kantor Pusat Tirtanadi', 'tipe' => 'Pusat', 'email_notifikasi' => 'pusat@tirtanadi.co.id'],
            ['id' => 2, 'nama_kantor' => 'Cabang Medan Kota', 'tipe' => 'Pemasaran', 'email_notifikasi' => 'medankota@tirtanadi.co.id'],
            ['id' => 3, 'nama_kantor' => 'Cabang Padang Bulan', 'tipe' => 'Pemasaran', 'email_notifikasi' => 'padangbulan@tirtanadi.co.id'],
            ['id' => 4, 'nama_kantor' => 'IPAM Sunggal', 'tipe' => 'Produksi/IPAM', 'email_notifikasi' => 'ipam.sunggal@tirtanadi.co.id'],
        ];
        $this->db->table('master_kantor')->insertBatch($data_kantor);

        // 3. Coverage Area
        $data_coverage = [
            ['id_kantor' => 2, 'nama_kecamatan' => 'Medan Kota'],
            ['id_kantor' => 2, 'nama_kecamatan' => 'Medan Area'],
            ['id_kantor' => 3, 'nama_kecamatan' => 'Medan Baru'],
            ['id_kantor' => 3, 'nama_kecamatan' => 'Medan Selayang'],
            ['id_kantor' => 4, 'nama_kecamatan' => 'Medan Sunggal'],
        ];
        $this->db->table('coverage_area')->insertBatch($data_coverage);

        // 4. Users
        // Hash password 'admin123' - Hardcoded known working hash
        $password = '$2y$10$DIvf56qQv5AzDbz6jUi9f.skOvBB0z6tqw8vLjxsiy2OI0gROlcHS';
        $data_users = [
            ['username' => 'superadmin', 'password' => $password, 'nama_lengkap' => 'IT Manager', 'role' => 'Super Admin', 'id_kantor' => null],
            ['username' => 'admin_kota', 'password' => $password, 'nama_lengkap' => 'Staff Medan Kota', 'role' => 'Admin Cabang', 'id_kantor' => 2],
        ];
        $this->db->table('users')->insertBatch($data_users);
    }
}
