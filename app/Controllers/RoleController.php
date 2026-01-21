<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;

class RoleController extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Role',
            'items' => $this->roleModel->findAll()
        ];
        return view('admin/roles/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Role',
            'item' => null
        ];
        return view('admin/roles/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->roleModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $permissions = $this->request->getPost('permissions') ?? [];
        
        $this->roleModel->save([
            'role_name' => $this->request->getPost('role_name'),
            'description' => $this->request->getPost('description'),
            'permissions' => json_encode($permissions)
        ]);

        return redirect()->to('admin/roles')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->roleModel->find($id);
        if (!$item) return redirect()->to('admin/roles')->with('error', 'Role tidak ditemukan.');
        
        // Decode permissions
        $item['permissions'] = json_decode($item['permissions'] ?? '[]', true);

        $data = [
            'title' => 'Edit Role',
            'item' => $item
        ];
        return view('admin/roles/form', $data);
    }

    public function update($id)
    {
        // Add ID for unique validation
        $rules = $this->roleModel->getValidationRules();
        $rules['role_name'] = "required|min_length[3]|is_unique[master_roles.role_name,id,$id]";

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $permissions = $this->request->getPost('permissions') ?? [];

        $this->roleModel->save([
            'id' => $id,
            'role_name' => $this->request->getPost('role_name'),
            'description' => $this->request->getPost('description'),
            'permissions' => json_encode($permissions)
        ]);

        return redirect()->to('admin/roles')->with('success', 'Role berhasil diperbarui.');
    }

    public function delete($id)
    {
        // Check usage before delete? (Simple fk constraint will block if used)
        try {
            $this->roleModel->delete($id);
            return redirect()->to('admin/roles')->with('success', 'Role dihapus.');
        } catch (\Exception $e) {
            return redirect()->to('admin/roles')->with('error', 'Gagal menghapus role: Masih digunakan oleh User.');
        }
    }
}
