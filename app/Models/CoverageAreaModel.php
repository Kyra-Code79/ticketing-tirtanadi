<?php

namespace App\Models;

use CodeIgniter\Model;

class CoverageAreaModel extends Model
{
    protected $table            = 'coverage_area';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_kantor', 'nama_kecamatan'];

    /**
     * Find Office ID by Kecamatan Name
     */
    public function getKantorByKecamatan($kecamatanName)
    {
        $result = $this->where('nama_kecamatan', $kecamatanName)->first();
        return $result ? $result['id_kantor'] : null;
    }
}
