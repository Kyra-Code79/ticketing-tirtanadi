<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LaporanModel;
use App\Models\KantorModel;

class TeknisiController extends BaseController
{
    protected $laporanModel;
    protected $kantorModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->kantorModel = new KantorModel();
    }

    public function index()
    {
        // Ensure role is Teknisi ? Middleware handles authentication, but role check is good.
        if (session()->get('role') !== 'Teknisi' && session()->get('role') !== 'Super Admin') {
            // For testing, Super Admin can view too? Or redirect.
            // allowed.
        }

        $idTeknisi = session()->get('user_id');
        $idKantor = session()->get('id_kantor');

        // Fetch assigned tasks (Active: Terverifikasi, Sedang Dikerjakan)
        $tasks = $this->laporanModel->where('id_teknisi', $idTeknisi)
                                    ->whereIn('status', ['Terverifikasi', 'Sedang Dikerjakan'])
                                    ->findAll();

        // Fetch Office Location for Routing Start Point
        $office = $this->kantorModel->find($idKantor);

        $data = [
            'title' => 'Dashboard Teknisi',
            'user' => session()->get(),
            'tasks' => $tasks,
            'office' => $office
        ];

        return view('teknisi/dashboard', $data);
    }

    public function update_status($id)
    {
        $status = $this->request->getPost('status'); // 'Sedang Dikerjakan' or 'Selesai'
        
        // Validation
        $report = $this->laporanModel->find($id);
        if (!$report || $report['id_teknisi'] != session()->get('user_id')) {
             return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $this->laporanModel->update($id, ['status' => $status]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }
}
