<?php

namespace App\Models;

use CodeIgniter\Model;

class KantorModel extends Model
{
    protected $table            = 'master_kantor';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_kantor', 'tipe', 'alamat_kantor', 'email_notifikasi', 'latitude', 'longitude'];

    public function getNearestOffice($lat, $lng)
    {
        // Fetch all offices that have lat/long
        $offices = $this->where('latitude !=', null)
                        ->where('longitude !=', null)
                        ->findAll();
        
        if (empty($offices)) return null;

        $nearest = null;
        $minDist = 999999999; // Infinite meters

        foreach ($offices as $office) {
            $dist = $this->calculateHaversine($lat, $lng, $office['latitude'], $office['longitude']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $office;
            }
        }

        return $nearest;
    }

    private function calculateHaversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
