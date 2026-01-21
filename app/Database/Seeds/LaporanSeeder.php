<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LaporanSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        
        // Base coordinate (Medan)
        $baseLat = 3.595196;
        $baseLng = 98.672223;

        $data = [];

        // 1. Generate 5 Clustered Reports (Close to each other) - "Red Zone" candidates
        for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'nomor_tiket' => 'TKT-' . date('ymd') . rand(1000, 9999),
                'nama_pelapor' => $faker->name,
                'no_hp' => $faker->phoneNumber,
                'nama_kecamatan' => 'Medan Kota',
                'alamat_detail' => $faker->address,
                'latitude' => $baseLat + ($faker->randomFloat(6, -0.002, 0.002)), // ~200m radius
                'longitude' => $baseLng + ($faker->randomFloat(6, -0.002, 0.002)),
                'id_kantor_tujuan' => 2, // Medan Kota
                'isi_laporan' => 'Pipa bocor di area padat penduduk ' . $i,
                'foto_bukti' => null,
                'status' => $faker->randomElement(['Menunggu', 'Sedang Dikerjakan']),
                'is_urgent' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // 2. Generate 5 Scattered Reports (Far away)
        for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'nomor_tiket' => 'TKT-' . date('ymd') . rand(1000, 9999),
                'nama_pelapor' => $faker->name,
                'no_hp' => $faker->phoneNumber,
                'nama_kecamatan' => 'Medan Sunggal',
                'alamat_detail' => $faker->address,
                'latitude' => $baseLat + ($faker->randomFloat(6, -0.05, 0.05)), // ~5km radius
                'longitude' => $baseLng + ($faker->randomFloat(6, -0.05, 0.05)),
                'id_kantor_tujuan' => 4, // IPAM Sunggal
                'isi_laporan' => 'Air kotor dan berbau ' . $i,
                'foto_bukti' => null,
                'status' => $faker->randomElement(['Selesai', 'Terverifikasi', 'Ditolak']),
                'is_urgent' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('laporan')->insertBatch($data);
    }
}
