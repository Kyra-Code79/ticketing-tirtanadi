<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\KantorModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $kantorModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UsersModel();
        $this->kantorModel = new KantorModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        // Join with Kantor and Roles
        $items = $this->userModel->select('users.*, master_kantor.nama_kantor, master_roles.role_name')
                                 ->join('master_kantor', 'master_kantor.id = users.id_kantor', 'left')
                                 ->join('master_roles', 'master_roles.id = users.role_id', 'left')
                                 ->findAll();

        $data = [
            'title' => 'Manajemen User (Admin)',
            'items' => $items
        ];
        return view('admin/users/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah User',
            'item' => null,
            'offices' => $this->kantorModel->findAll(),
            'roles' => $this->roleModel->findAll()
        ];
        return view('admin/users/form', $data);
    }

    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'nama_lengkap' => 'required',
            'role_id' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role_id' => $this->request->getPost('role_id'),
            'id_kantor' => $this->request->getPost('id_kantor') ?: null,
            // Hash Password
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ];

        if ($this->userModel->insert($data) === false) {
            // Check for DB errors
            $errors = $this->userModel->errors();
            if (empty($errors)) {
                // If model validation passed but DB failed (e.g. FK)
                $dbError = $this->userModel->db->error();
                $errors = ['database' => $dbError['message'] ?? 'Unknown database error'];
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }
        
        return redirect()->to('admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->userModel->find($id);
        if (!$item) return redirect()->to('admin/users')->with('error', 'User tidak ditemukan');

        $data = [
            'title' => 'Edit User',
            'item' => $item,
            'offices' => $this->kantorModel->findAll(),
            'roles' => $this->roleModel->findAll()
        ];
        return view('admin/users/form', $data);
    }

    public function update($id)
    {
        $rules = [
            'username' => "required|min_length[3]|is_unique[users.username,id,$id]",
            'nama_lengkap' => 'required',
            'role_id' => 'required'
        ];
        
        // Password optional on update
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role_id' => $this->request->getPost('role_id'),
            'id_kantor' => $this->request->getPost('id_kantor') ?: null,
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->save($data);
        return redirect()->to('admin/users')->with('success', 'Data user berhasil diperbarui.');
    }

    public function delete($id)
    {
        // Prevent deleting self?
        if (session()->get('id') == $id) {
            return redirect()->to('admin/users')->with('error', 'Anda tidak dapat menghapus akun sendiri saat login.');
        }

        $this->userModel->delete($id);
        return redirect()->to('admin/users')->with('success', 'User dihapus.');
    }
}
