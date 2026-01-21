<?php

namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\KantorModel;
use App\Models\OfficeTypeModel;

class KantorController extends BaseController
{
    protected $kantorModel;
    protected $typeModel;

    public function __construct()
    {
        $this->kantorModel = new KantorModel();
        $this->typeModel = new OfficeTypeModel();
    }

    public function index()
    {
        // Join with Types for display
        $items = $this->kantorModel->select('master_kantor.*, master_types.nama_tipe, master_types.warna')
                                   ->join('master_types', 'master_types.id = master_kantor.type_id', 'left')
                                   ->findAll();

        $data = [
            'title' => 'Manajemen Kantor',
            'items' => $items
        ];
        return view('admin/kantor/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Kantor Baru',
            'item' => null,
            'types' => $this->typeModel->findAll()
        ];
        return view('admin/kantor/form', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'nama_kantor' => 'required',
            'type_id' => 'required',
            'latitude' => 'required|decimal',
            'longitude' => 'required|decimal'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kantorModel->save([
            'nama_kantor' => $this->request->getPost('nama_kantor'),
            'type_id' => $this->request->getPost('type_id'),
            'alamat_kantor' => $this->request->getPost('alamat_kantor'),
            'email_notifikasi' => $this->request->getPost('email_notifikasi'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
        ]);

        return redirect()->to('admin/kantor')->with('success', 'Data kantor berhasil disimpan.');
    }

    public function edit($id)
    {
        $item = $this->kantorModel->find($id);
        if (!$item) return redirect()->to('admin/kantor')->with('error', 'Data tidak ditemukan');

        $data = [
            'title' => 'Edit Kantor',
            'item' => $item,
            'types' => $this->typeModel->findAll()
        ];
        return view('admin/kantor/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'nama_kantor' => 'required',
            'type_id' => 'required',
            'latitude' => 'required|decimal',
            'longitude' => 'required|decimal'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kantorModel->save([
            'id' => $id,
            'nama_kantor' => $this->request->getPost('nama_kantor'),
            'type_id' => $this->request->getPost('type_id'),
            'alamat_kantor' => $this->request->getPost('alamat_kantor'),
            'email_notifikasi' => $this->request->getPost('email_notifikasi'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
        ]);

        return redirect()->to('admin/kantor')->with('success', 'Data kantor berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->kantorModel->delete($id);
        return redirect()->to('admin/kantor')->with('success', 'Data kantor dihapus.');
    }

    // Helper to extract Lat/Lng from Google Maps Link
    public function field_extract() 
    {
        // ... (This function name is weird, I'll use a proper route)
    }

    /**
     * AJAX Endpoint to parse Google Maps URL
     */
    public function parse_maps_url()
    {
        $url = $this->request->getPost('url');
        
        if (!$url) return $this->response->setJSON(['status' => 'error', 'message' => 'URL kosong']);

        // Handle Short URL expansion (e.g. goo.gl, maps.app.goo.gl)
        if (strpos($url, 'goo.gl') !== false || strpos($url, 'maps.app.goo.gl') !== false || strpos($url, 'bit.ly') !== false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // We only need headers/final URL
            curl_exec($ch);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
        }

        // Regex Strategies
        $lat = null; 
        $lng = null;

        // 1. Check for @lat,lng
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // 2. Check for q=lat,lng
        elseif (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // 3. Check for search/lat,lng
        elseif (preg_match('/search\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // 4. Check for !3dlat!4dlng (Embed style)
        elseif (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }

        if ($lat && $lng) {
            return $this->response->setJSON([
                'status' => 'success', 
                'lat' => $lat, 
                'lng' => $lng,
                'final_url' => $url
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Koordinat tidak ditemukan dalam link tersebut.']);
    }
}
