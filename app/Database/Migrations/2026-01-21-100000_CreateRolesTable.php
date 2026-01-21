<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Create master_roles table
        if (!$db->tableExists('master_roles')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'role_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'description' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('master_roles', true);
        }

        // 2. Add role_id to users
        if (!$db->fieldExists('role_id', 'users')) {
            $this->forge->addColumn('users', [
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'role'
                ]
            ]);
            $this->forge->addForeignKey('role_id', 'master_roles', 'id', 'RESTRICT', 'CASCADE');
        }

        // 3. Seed Default Roles
        $seeder = \Config\Database::connect();
        $defaults = [
            ['role_name' => 'Super Admin', 'description' => 'Full Access'],
            ['role_name' => 'Admin Cabang', 'description' => 'Limited to Office'],
            ['role_name' => 'Teknisi', 'description' => 'Field Technician'],
        ];

        foreach ($defaults as $def) {
            // Check existence
            $exists = $seeder->table('master_roles')->where('role_name', $def['role_name'])->countAllResults();
            if ($exists == 0) {
                $seeder->table('master_roles')->insert($def);
            }
        }

        // 4. Migrate Existing Data (Enum -> ID)
        if ($db->fieldExists('role', 'users')) {
            $roles = $seeder->table('master_roles')->get()->getResultArray();
            foreach ($roles as $role) {
                // Update users where enum matches role_name
                $seeder->table('users')
                       ->where('role', $role['role_name'])
                       ->update(['role_id' => $role['id']]);
            }
        }

        // 5. Drop old role column
        // Optional: Keep for backup or drop now. Let's drop to force usage of role_id.
        // if ($db->fieldExists('role', 'users')) {
        //    $this->forge->dropColumn('users', 'role');
        // }
    }

    public function down()
    {
        // Revert is complex due to data loss possibility.
        // We will just drop the FK and column.
        $this->forge->dropForeignKey('users', 'users_role_id_foreign');
        $this->forge->dropColumn('users', 'role_id');
        $this->forge->dropTable('master_roles');
    }
}
