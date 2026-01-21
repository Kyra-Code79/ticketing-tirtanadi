<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\LaporanModel;
use App\Models\CoverageAreaModel;

class Pengaduan extends BaseController
{
    protected $settingsModel;
    protected $laporanModel;
    protected $coverageModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->laporanModel = new LaporanModel();
        $this->coverageModel = new CoverageAreaModel();
    }

    public function index()
    {
        // If logged in as admin, redirect to dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('landing_page');
    }

    public function store()
    {
        // 1. Validation
        // Note: nama_kecamatan might be empty if GPS fail/manual entry. Only lat/long/isi/nama/hp required.
        if (!$this->validate([
            'nama_pelapor' => 'required',
            'no_hp' => 'required',
            'jenis_aduan' => 'required', // Replaces/augments isi_laporan
            // 'nama_kecamatan' => 'required', // Made optional for fallback logic
            'alamat_detail' => 'required',
            // 'latitude' => 'required', // Could be empty on manual override? Let's keep required for now as DB needs it.
            // Actually, if user types manual, we might NOT have lat/long. But DB requires it.
            // Requirement said: "If GPS fails... assign to Default/Pending".
            // So we might need dummy lat/long or allow null? DB schema: latitude decimal NOT NULL.
            // Assumption: If GPS fails, frontend might not send lat/long.
            // Ideally we need lat/long. If manual, maybe we set 0,0 or default?
            // Let's assume we REQUIRE partial lat/long or we default to Map Center from settings.
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $input = $this->request->getPost();
        
        // Prepare Lat/Long (Default to Map Center if missing - e.g. manual address without pin)
        $defaultLat = $this->settingsModel->getValue('map_center_lat') ?? 0;
        $defaultLng = $this->settingsModel->getValue('map_center_long') ?? 0;
        
        $currentLat = !empty($input['latitude']) ? (float)$input['latitude'] : (float)$defaultLat;
        $currentLng = !empty($input['longitude']) ? (float)$input['longitude'] : (float)$defaultLng;

        // 2. Logic Auto Routing (Nearest Office)
        // Replaces old "Coverage Area" logic as primary method if lat/long exists.
        // If specific coverage via kecamatan exists, we could prefer that, but User request says "Based on distance".
        // Let's TRY generic distance first.
        
        $kantorModel = new \App\Models\KantorModel();
        $nearestOffice = $kantorModel->getNearestOffice($currentLat, $currentLng);
        $idKantorTujuan = $nearestOffice ? $nearestOffice['id'] : null;

        // Fallback: If no lat/long (0,0) or no nearest found, try Kecamatan match?
        // But user explicitly asked for Distance Based. So we stick to that first.

        // 3. Logic GIS (Haversine Formula) - Radius Alert
        // Only run if we valid lat/long (not 0,0 default)
        $isUrgent = 0;
        if ($currentLat != 0 && $currentLng != 0) {
            $radiusMeter = (float) ($this->settingsModel->getValue('alert_radius_meter') ?? 500);
            $thresholdCount = (int) ($this->settingsModel->getValue('alert_threshold_count') ?? 5);

            $pendingReports = $this->laporanModel->getActiveReports();
            $nearbyCount = 0;

            foreach ($pendingReports as $report) {
                $dist = $this->calculateHaversine(
                    $currentLat, 
                    $currentLng, 
                    (float)$report['latitude'], 
                    (float)$report['longitude']
                );

                if ($dist <= $radiusMeter) {
                    $nearbyCount++;
                }
            }

            if (($nearbyCount + 1) >= $thresholdCount) {
                $isUrgent = 1;
            }
        }

        // Combine Jenis Aduan into Isi Laporan for display, keep Detail separately
        $finalIsiLaporan = "[Gangguan: " . $input['jenis_aduan'] . "]"; 
        
        // 4. Save Data
        $data = [
            'nomor_tiket' => 'TRT-' . date('Ymd') . '-' . rand(1000, 9999), 
            'nama_pelapor' => $input['nama_pelapor'],
            'no_hp' => $input['no_hp'],
            'email' => $input['email'], // New
            'nama_kecamatan' => $input['nama_kecamatan'] ?? 'Manual/Unknown',
            'alamat_detail' => $input['alamat_detail'],
            'latitude' => $currentLat,
            'longitude' => $currentLng,
            'id_kantor_tujuan' => $idKantorTujuan, // Auto assigned
            'isi_laporan' => $finalIsiLaporan,
            'detail_aduan' => $input['detail_aduan'], // New
            'status' => 'Menunggu',
            'is_urgent' => $isUrgent,
        ];

        $this->laporanModel->insert($data);

        return redirect()->to('/')->with('success', 'Laporan Berhasil! Petugas akan segera meluncur.')->with('new_ticket', $data['nomor_tiket']);
    }

    public function cetak_pdf($nomorTiket)
    {
        $report = $this->laporanModel->where('nomor_tiket', $nomorTiket)->first();

        if (!$report) {
            return redirect()->to('/')->with('errors', ['Tiket tidak ditemukan']);
        }

        // Prepare Data
        $data = [
            'item' => $report,
            'logo_base64' => $this->getBase64Image('logo-tirtanadi.png') // Helper to get base64
        ];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('pdf/laporan_tiket', $data);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'landscape'); // Or A4, based on preference. Ticket usually small. Let's start A4 portrait.
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("Tiket-" . $nomorTiket . ".pdf", ["Attachment" => true]);
    }

    private function getBase64Image($filename)
    {
        $path = FCPATH . $filename; // public/logo-tirtanadi.png usually is at root public? Or public/assets?
        // User said @[public/logo-tirtanadi.png], checks file tool: c:\laragon\www\ticketing-tirtanadi\public\logo-tirtanadi.png
        // FCPATH points to public folder in CI4
        
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    public function cek_status()
    {
        $keyword = $this->request->getGet('keyword');
        $data = [
            'keyword' => $keyword,
            'result' => null,
            'history' => []
        ];

        if ($keyword) {
            $report = $this->laporanModel->select('laporan.*, master_kantor.nama_kantor')
                ->join('master_kantor', 'master_kantor.id = laporan.id_kantor_tujuan', 'left')
                ->where('nomor_tiket', $keyword)
                ->first();
            
            if ($report) {
                $data['result'] = $report;
                
                // Simulate History based on current status for UI Visualization
                // In a real app, we would query a separate 'status_history' table.
                // Here we infer milestones based on current status level.
                
                $history = [];
                $createdAt = strtotime($report['created_at']);
                
                // 1. Created
                $history[] = [
                    'status' => 'Laporan Diterima',
                    'desc' => 'Laporan berhasil masuk ke sistem.',
                    'time' => date('d M Y H:i', $createdAt),
                    'active' => true
                ];

                // 2. Verified (If status is NOT Menunggu)
                if ($report['status'] != 'Menunggu') {
                     $history[] = [
                        'status' => 'Terverifikasi',
                        'desc' => 'Laporan telah diverifikasi oleh petugas.',
                        'time' => date('d M Y H:i', $createdAt + 3600), // Fake time +1h
                        'active' => true
                    ];
                }

                // 3. Process (If status is Proses/Sedang Dikerjakan or Selesai)
                if (in_array($report['status'], ['Proses', 'Sedang Dikerjakan', 'Selesai'])) {
                     $history[] = [
                        'status' => 'Sedang Dikerjakan',
                        'desc' => 'Petugas sedang melakukan perbaikan di lokasi.',
                        'time' => date('d M Y H:i', $createdAt + 7200), // Fake time +2h
                        'active' => true
                    ];
                }
                
                // 4. Finished (If status is Selesai) or Rejected
                if ($report['status'] == 'Selesai') {
                    $history[] = [
                        'status' => 'Selesai',
                        'desc' => 'Perbaikan telah selesai.',
                        'time' => date('d M Y H:i', $createdAt + 18000), // Fake time +5h
                        'active' => true
                    ];
                } elseif ($report['status'] == 'Ditolak') {
                     $history[] = [
                        'status' => 'Ditolak',
                        'desc' => 'Laporan ditolak. Silakan hubungi call center.',
                        'time' => date('d M Y H:i', $createdAt + 3600),
                        'active' => true,
                        'is_rejected' => true
                    ];
                }

                // Reverse to show latest first
                $data['history'] = array_reverse($history);
            }
        }

        return view('cek_status', $data);
    }

    /**
     * Calculate distance in meters between two coordinates
     */
    private function calculateHaversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }
}
