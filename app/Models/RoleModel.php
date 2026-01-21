<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'master_roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['role_name', 'description', 'permissions'];

    protected $validationRules = [
        'role_name' => 'required|min_length[3]|is_unique[master_roles.role_name,id,{id}]',
    ];
}
