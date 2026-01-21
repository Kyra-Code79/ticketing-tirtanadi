<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermissionsToRoles extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        if (!$db->fieldExists('permissions', 'master_roles')) {
            $this->forge->addColumn('master_roles', [
                'permissions' => [
                    'type' => 'TEXT', // Auto-size for JSON
                    'null' => true,
                    'after' => 'description'
                ]
            ]);
        }

        // Seed Default Permissions for Super Admin
        $seeder = \Config\Database::connect();
        // Grant all permissions to Super Admin
        $allPermissions = json_encode([
            'manage_users', 
            'manage_roles', 
            'manage_offices', 
            'manage_types', 
            'manage_areas', 
            'view_all_reports'
        ]);
        
        $seeder->table('master_roles')
               ->where('role_name', 'Super Admin')
               ->update(['permissions' => $allPermissions]);
               
        // Grant limited permissions to Admin Cabang
        $branchPermissions = json_encode([
             // 'manage_users', // Maybe?
             // 'manage_offices', // Maybe?
        ]);
        // Actually, Admin Cabang usually just views reports. Let's leave empty or basic.
    }

    public function down()
    {
        $this->forge->dropColumn('master_roles', 'permissions');
    }
}
