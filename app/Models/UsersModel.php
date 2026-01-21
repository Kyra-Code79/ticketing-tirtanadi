<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'password', 'nama_lengkap', 'role_id', 'id_kantor', 'last_login'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'username'     => 'required|alpha_numeric|min_length[3]|is_unique[users.username,id,{id}]',
        'nama_lengkap' => 'required',
        'role_id'      => 'required|is_natural_no_zero',
        'password'     => 'permit_empty|min_length[6]', // Optional on edit
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
}
