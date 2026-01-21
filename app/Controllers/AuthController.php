<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class AuthController extends BaseController
{
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('auth/login');
    }

    public function process_login()
    {
        $usersModel = new UsersModel();
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        // Join to get role name and permissions
        $user = $usersModel->select('users.*, master_roles.role_name, master_roles.permissions')
                           ->join('master_roles', 'master_roles.id = users.role_id', 'left')
                           ->where('username', $username)
                           ->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Set Session
                $sessionData = [
                    'user_id'       => $user['id'],
                    'username'      => $user['username'],
                    'nama_lengkap'  => $user['nama_lengkap'],
                    // Store role_name as 'role' for backward compatibility
                    'role'          => $user['role_name'] ?? 'Unknown', 
                    'role_id'       => $user['role_id'],
                    'permissions'   => json_decode($user['permissions'] ?? '[]', true), // Load Permissions
                    'id_kantor'     => $user['id_kantor'], // NULL for Super Admin
                    'is_logged_in'  => true
                ];
                session()->set($sessionData);

                // Redirect based on Role
                if ($user['role_name'] === 'Teknisi') {
                    return redirect()->to('/teknisi/dashboard');
                }

                return redirect()->to('/admin/dashboard');
            }
        }

        return redirect()->back()->with('error', 'Username atau Password salah.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
