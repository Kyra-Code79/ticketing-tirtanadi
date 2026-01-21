<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LaporanModel;
use App\Models\SettingsModel;

class Admin extends BaseController
{
    protected $laporanModel;
    protected $settingsModel;
    protected $session;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->settingsModel = new SettingsModel();
        $this->session = session();
    }

    public function index()
    {
        $idKantor = $this->session->get('id_kantor');
        $role = $this->session->get('role');
        $db = \Config\Database::connect();

        // Inputs
        $filterStatus = $this->request->getGet('status');
        $filterPolygonId = $this->request->getGet('polygon_id');

        // Stats Logic
        $builder = $this->laporanModel->builder();
        
        // If Role is Admin Cabang (NOT Super Admin), filter by id_kantor_tujuan
        if($role !== 'Super Admin' && $idKantor) {
             $builder->where('id_kantor_tujuan', $idKantor);
        }

        $totalLaporan = $builder->countAllResults(false); // false to not reset query
        $menunggu = $this->laporanModel->where('status', 'Menunggu');
        if($role !== 'Super Admin' && $idKantor) $menunggu->where('id_kantor_tujuan', $idKantor);
        $countMenunggu = $menunggu->countAllResults();

        $proses = $this->laporanModel->where('status', 'Proses');
        if($role !== 'Super Admin' && $idKantor) $proses->where('id_kantor_tujuan', $idKantor);
        $countProses = $proses->countAllResults();

        $selesai = $this->laporanModel->where('status', 'Selesai');
        if($role !== 'Super Admin' && $idKantor) $selesai->where('id_kantor_tujuan', $idKantor);
        $countSelesai = $selesai->countAllResults();
        
        // Urgent Count
        $urgent = $this->laporanModel->where('is_urgent', 1)->where('status !=', 'Selesai');
        if($role !== 'Super Admin' && $idKantor) $urgent->where('id_kantor_tujuan', $idKantor);
        $countUrgent = $urgent->countAllResults();

        // Recent Reports
        $recentReports = $this->laporanModel->orderBy('created_at', 'DESC')->limit(5);
        if($role !== 'Super Admin' && $idKantor) $recentReports->where('id_kantor_tujuan', $idKantor);
        $data['recent_reports'] = $recentReports->findAll();

        $data['stats'] = [
            'total' => $totalLaporan,
            'menunggu' => $countMenunggu,
            'proses' => $countProses,
            'selesai' => $countSelesai,
            'urgent' => $countUrgent
        ];

        // --- MAP DATA LOGIC ---
        $mapBuilder = $this->laporanModel->select('laporan.id, laporan.nomor_tiket, laporan.latitude, laporan.longitude, laporan.status, laporan.isi_laporan, laporan.nama_kecamatan, laporan.nama_pelapor, laporan.alamat_detail, master_kantor.nama_kantor')
        ->join('master_kantor', 'master_kantor.id = laporan.id_kantor_tujuan', 'left');
        
        // Role Filter
        if($role !== 'Super Admin' && $idKantor) {
            $mapBuilder->where('id_kantor_tujuan', $idKantor);
        }

        // Status Filter
        if ($filterStatus === 'default_active' || empty($filterStatus)) {
             // DEFAULT: Hide 'Selesai'
             $mapBuilder->where('status !=', 'Selesai');
             if(empty($filterStatus)) $filterStatus = 'default_active';
        } elseif ($filterStatus === 'all') {
            // Show All: Do nothing (no where clause)
        } else {
            // Specific Status (Menunggu, Proses, etc.)
            $mapBuilder->where('status', $filterStatus);
        }

        $reports = $mapBuilder->findAll();
        
        // Polygon Filter (Spatial)
        $selectedPolygon = null;
        if ($filterPolygonId) {
            $polyRow = $db->table('polygons')->where('id', $filterPolygonId)->get()->getRowArray();
            if ($polyRow) {
                $selectedPolygon = $polyRow;
                $geoJson = json_decode($polyRow['geometry'], true);
                
                // Filter reports inside polygon
                $filteredReports = [];
                foreach ($reports as $rpt) {
                    if ($this->isPointInGeoJson($rpt['latitude'], $rpt['longitude'], $geoJson)) {
                        $filteredReports[] = $rpt;
                    }
                }
                $reports = $filteredReports;
            }
        }
        
        $data['map_data'] = $reports;
        $data['polygons'] = $db->table('polygons')->get()->getResultArray();
        $data['selected_polygon'] = $selectedPolygon;
        $data['filter_status'] = $filterStatus;
        $data['filter_polygon_id'] = $filterPolygonId;
        
        $data['title'] = 'Dashboard Admin';
        $data['user'] = $this->session->get();

        return view('admin/dashboard', $data);
    }

    // Helper: Check if point is inside GeoJSON (FeatureCollection or Feature)
    private function isPointInGeoJson($lat, $lng, $geoJson)
    {
        if (!$lat || !$lng) return false;
        
        // Convert GeoJSON to simple polygons array of outer rings
        $polygons = [];
        
        $features = [];
        if ($geoJson['type'] === 'FeatureCollection') {
            $features = $geoJson['features'];
        } elseif ($geoJson['type'] === 'Feature') {
            $features[] = $geoJson;
        }

        foreach ($features as $feature) {
            $geom = $feature['geometry'];
            $type = $geom['type'];
            $coords = $geom['coordinates'];

            // Handle Polygon (Standard or with M/Z stripped conceptually)
            if (strpos($type, 'Polygon') !== false && strpos($type, 'Multi') === false) {
                 // Polygon: [ [outer_ring], [inner_ring], ... ]
                 if(isset($coords[0])) $polygons[] = $coords[0];
            }
            // Handle MultiPolygon
            elseif (strpos($type, 'MultiPolygon') !== false) {
                // MultiPolygon: [ [ [outer_ring], ... ], [ [outer_ring], ... ] ]
                foreach ($coords as $poly) {
                    if(isset($poly[0])) $polygons[] = $poly[0];
                }
            }
        }

        // log_message('error', 'Checking Point (' . $lat . ',' . $lng . ') against ' . count($polygons) . ' polygons.');

        foreach ($polygons as $polyCoords) {
            // Ensure 2D coords cleaning just in case (though Controller does it on save time now)
            // But old data might still have 4D
            $cleanPoly = [];
            foreach($polyCoords as $pt) {
                $cleanPoly[] = [$pt[0], $pt[1]]; // Force Lng, Lat
            }

            if ($this->pointInPolygon($lat, $lng, $cleanPoly)) {
                return true;
            }
        }
        return false;
    }

    // Ray-Casting Algorithm
    private function pointInPolygon($lat, $lng, $polygon)
    {
        $c = false;
        $count = count($polygon);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            // GeoJSON coordinates are [lng, lat]
            $lng_i = $polygon[$i][0];
            $lat_i = $polygon[$i][1];
            $lng_j = $polygon[$j][0];
            $lat_j = $polygon[$j][1];

            if ((($lat_i > $lat) != ($lat_j > $lat)) &&
                ($lng < ($lng_j - $lng_i) * ($lat - $lat_i) / ($lat_j - $lat_i) + $lng_i)) {
                $c = !$c;
            }
        }
        return $c;
    }
    // --- TICKET MANAGEMENT ---

    public function laporan()
    {
        $idKantor = $this->session->get('id_kantor');
        $role = $this->session->get('role');
        
        $filterStatus = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');

        $builder = $this->laporanModel->select('laporan.*, master_kantor.nama_kantor')
            ->join('master_kantor', 'master_kantor.id = laporan.id_kantor_tujuan', 'left');

        // Role Filter
        if ($role !== 'Super Admin' && $idKantor) {
            $builder->groupStart()
                    ->where('id_kantor_tujuan', $idKantor)
                    ->orWhere('id_kantor_tujuan', NULL) // Optional: Show unassigned? Maybe not.
                    ->groupEnd();
            // Strictly:
             $builder->where('id_kantor_tujuan', $idKantor);
        }

        // Status Filter
        if ($filterStatus && $filterStatus !== 'all') {
            $builder->where('laporan.status', $filterStatus);
        }

        // Search Keyword
        if ($keyword) {
            $builder->groupStart()
                ->like('nomor_tiket', $keyword)
                ->orLike('nama_pelapor', $keyword)
                ->orLike('isi_laporan', $keyword)
                ->groupEnd();
        }

        $data = [
            'title' => 'Data Laporan',
            'user' => $this->session->get(),
            'loran' => $builder->orderBy('created_at', 'DESC')->paginate(10, 'laporan'),
            'pager' => $this->laporanModel->pager,
            'filter_status' => $filterStatus,
            'keyword' => $keyword
        ];

        return view('admin/laporan/index', $data);
    }

    public function detail($id)
    {
        $idKantor = $this->session->get('id_kantor');
        $role = $this->session->get('role');

        $report = $this->laporanModel->select('laporan.*, master_kantor.nama_kantor')
            ->join('master_kantor', 'master_kantor.id = laporan.id_kantor_tujuan', 'left')
            ->where('laporan.id', $id)
            ->first();

        if (!$report) {
            return redirect()->to('admin/laporan')->with('error', 'Laporan tidak ditemukan.');
        }

        // Access Check
        if ($role !== 'Super Admin' && $idKantor && $report['id_kantor_tujuan'] != $idKantor) {
            return redirect()->to('admin/laporan')->with('error', 'Anda tidak memiliki akses ke laporan ini.');
        }

        $data = [
            'title' => 'Detail Laporan - ' . $report['nomor_tiket'],
            'user' => $this->session->get(),
            'item' => $report
        ];

        return view('admin/laporan/detail', $data);
    }

    public function update_status($id)
    {
        $status = $this->request->getPost('status');
        
        // Simple validation
        if (!in_array($status, ['Menunggu', 'Terverifikasi','Sedang Dikerjakan', 'Selesai', 'Ditolak'])) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $this->laporanModel->update($id, ['status' => $status]);

        return redirect()->back()->with('success', 'Status laporan berhasil diperbarui.');
    }
}
