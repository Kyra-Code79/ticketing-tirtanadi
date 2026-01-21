<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OfficeTypeModel;

class TypeController extends BaseController
{
    protected $typeModel;

    public function __construct()
    {
        $this->typeModel = new OfficeTypeModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Tipe Kantor',
            'items' => $this->typeModel->findAll()
        ];
        return view('admin/types/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Tipe Kantor',
            'item' => null
        ];
        return view('admin/types/form', $data);
    }

    public function store()
    {
        if (!$this->typeModel->save($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->typeModel->errors());
        }
        return redirect()->to('admin/types')->with('success', 'Tipe baru berhasil disimpan.');
    }

    public function edit($id)
    {
        $item = $this->typeModel->find($id);
        if (!$item) return redirect()->to('admin/types')->with('error', 'Data tidak ditemukan');

        $data = [
            'title' => 'Edit Tipe Kantor',
            'item' => $item
        ];
        return view('admin/types/form', $data);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $data['id'] = $id;

        if (!$this->typeModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->typeModel->errors());
        }
        return redirect()->to('admin/types')->with('success', 'Data tipe berhasil diperbarui.');
    }

    public function delete($id)
    {
        // Check usage before delete? Ideally yes, but User can handle FK constraint error or we catch it.
        // DB Config: ON DELETE SET NULL. So it's safe to delete, offices will just have NULL type.
        $this->typeModel->delete($id);
        return redirect()->to('admin/types')->with('success', 'Tipe dihapus.');
    }
}
