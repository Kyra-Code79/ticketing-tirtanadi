<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropRoleEnumFromUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Ensure data is migrated before dropping (Double Check)
        if ($db->fieldExists('role', 'users') && $db->fieldExists('role_id', 'users')) {
            // Get all users
            $users = $db->table('users')->get()->getResultArray();
            foreach ($users as $user) {
                if (empty($user['role_id']) && !empty($user['role'])) {
                    // Find role_id from master_roles
                    $roleDef = $db->table('master_roles')->where('role_name', $user['role'])->get()->getRowArray();
                    if ($roleDef) {
                         $db->table('users')->where('id', $user['id'])->update(['role_id' => $roleDef['id']]);
                    }
                }
            }
            
            // Now Drop the column
            $this->forge->dropColumn('users', 'role');
        }
    }

    public function down()
    {
        // Restore Enum Column (Empty)
        $this->forge->addColumn('users', [
            'role' => ['type' => 'ENUM', 'constraint' => ['Super Admin', 'Admin Cabang'], 'null' => true]
        ]);
    }
}
