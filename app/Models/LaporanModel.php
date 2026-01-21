<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    protected $table            = 'laporan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'nomor_tiket',
        'nama_pelapor',
        'no_hp',
        'nama_kecamatan',
        'alamat_detail',
        'latitude',
        'longitude',
        'id_kantor_tujuan',
        'id_teknisi',
        'isi_laporan',
        'foto_bukti',
        'status',
        'is_urgent',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active reports for GIS calculation (Status: Menunggu)
     */
    public function getActiveReports()
    {
        return $this->where('status', 'Menunggu')->findAll();
    }
}
