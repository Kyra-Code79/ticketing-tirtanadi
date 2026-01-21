<?php

namespace App\Models;

use CodeIgniter\Model;

class OfficeTypeModel extends Model
{
    protected $table            = 'master_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_tipe', 'warna'];

    // Validation
    protected $validationRules = [
        'id'        => 'permit_empty|is_natural_no_zero', // Required for {id} placeholder
        'nama_tipe' => 'required|min_length[3]|is_unique[master_types.nama_tipe,id,{id}]',
        'warna'     => 'required|max_length[10]', 
    ];
}
