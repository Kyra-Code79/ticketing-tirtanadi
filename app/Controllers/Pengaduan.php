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

        // 2. Logic Auto Routing
        $idKantorTujuan = null;
        if (!empty($input['nama_kecamatan'])) {
            // Try specific lookup
            $idKantorTujuan = $this->coverageModel->getKantorByKecamatan($input['nama_kecamatan']);
        }
        
        // If still null (Reverse geocode failed or Kecamatan not in DB COVERAGE), default to NULL (Manual Review)
        // or a specific fallback office if logic dictates. 
        // User logic: "If GPS fails and user types manual address, assign to a 'Default/Pending' branch for manual review."
        // Leaving id_kantor_tujuan as NULL satisfies "Manual Review" (admin sees no office assigned).

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

        // Combine Jenis Aduan into Isi Laporan for storage
        $finalIsiLaporan = "[Gangguan: " . $input['jenis_aduan'] . "] " . ($input['alamat_detail'] ?? ''); // Simple append or just store generic text

        // 4. Save Data
        $data = [
            'nomor_tiket' => 'TRT-' . date('Ymd') . '-' . rand(1000, 9999), 
            'nama_pelapor' => $input['nama_pelapor'],
            'no_hp' => $input['no_hp'],
            'nama_kecamatan' => $input['nama_kecamatan'] ?? 'Manual/Unknown',
            'alamat_detail' => $input['alamat_detail'],
            'latitude' => $currentLat,
            'longitude' => $currentLng,
            'id_kantor_tujuan' => $idKantorTujuan,
            'isi_laporan' => $finalIsiLaporan,
            'status' => 'Menunggu',
            'is_urgent' => $isUrgent,
        ];

        $this->laporanModel->insert($data);

        return redirect()->to('/')->with('success', 'Laporan Berhasil! Petugas akan segera meluncur. Tiket: ' . $data['nomor_tiket']);
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
