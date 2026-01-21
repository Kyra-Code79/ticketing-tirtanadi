<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Shapefile\ShapefileReader;

class PolygonController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['title'] = 'Manajemen Area (Polygon)';
        $data['polygons'] = $this->db->table('polygons')->get()->getResultArray();
        $data['user'] = session()->get();
        return view('admin/polygons/index', $data);
    }

    public function store()
    {
        $file = $this->request->getFile('shp_zip');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'File upload failed.');
        }

        if ($file->getExtension() !== 'zip') {
            return redirect()->back()->with('error', 'Please upload a .zip file containing .shp, .shx, and .dbf');
        }

        // 1. Extract ZIP
        $zip = new \ZipArchive;
        if ($zip->open($file->getTempName()) === TRUE) {
            $extractPath = WRITEPATH . 'uploads/temp_shp/' . uniqid();
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0777, true);
            }
            $zip->extractTo($extractPath);
            $zip->close();

            // 2. Find .shp file (Recursive search)
            $shpFile = null;
            $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath));
            foreach ($ritit as $leaf) {
                if ($leaf->isFile() && strtolower($leaf->getExtension()) === 'shp') {
                    $shpFile = $leaf->getPathname();
                    break;
                }
            }

            if (!$shpFile) {
                // Debugging: List found files
                $filesFound = [];
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath)) as $f) {
                    if($f->isFile()) $filesFound[] = $f->getFilename();
                }
                return redirect()->back()->with('error', '.shp file not found. Found: ' . implode(', ', $filesFound));
            }

            // 3. Parse SHP
            try {
                $Shapefile = new ShapefileReader($shpFile);
                
                // We assume the SHP contains multiple records, we will merge them into one GeoJSON FeatureCollection
                // OR store each record as a separate polygon? 
                // Requirement: "Input Polygon via File SHP". Usually a district file has multiple polygons.
                // Let's store each meaningful record as a polygon or the whole file as one.
                // Better approach: User names the uploaded file. We store the *entire* collection as one GeoJSON standard.
                
                $features = [];
                while ($Geometry = $Shapefile->fetchRecord()) {
                    if ($Geometry->isDeleted()) {
                        continue;
                    }
                    $features[] = [
                        'type' => 'Feature',
                        'properties' => $Geometry->getDataArray(),
                        'geometry' => json_decode($Geometry->getGeoJSON(), true)
                    ];
                }

                $geoJson = [
                    'type' => 'FeatureCollection',
                    'features' => $features
                ];
                
                // Sanitize 4D coords to 2D and Fix Types
                foreach ($geoJson['features'] as &$feat) {
                    if(isset($feat['geometry'])) {
                        // Fix Type
                        $type = $feat['geometry']['type'];
                        if (strpos($type, 'PolygonM') !== false || strpos($type, 'PolygonZ') !== false) {
                            $feat['geometry']['type'] = str_replace(['M', 'Z'], '', $type);
                        }
                        
                        // Fix Coordinates
                        if(isset($feat['geometry']['coordinates'])) {
                            $feat['geometry']['coordinates'] = $this->cleanCoordinates($feat['geometry']['coordinates']);
                        }
                    }
                }
                unset($feat);

                $name = $this->request->getPost('name');
                $color = $this->request->getPost('color');

                $this->db->table('polygons')->insert([
                    'name' => $name,
                    'color' => $color,
                    'geometry' => json_encode($geoJson),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Resource Cleanup: STRICTLY REQUIRED for Windows file locks
                unset($Shapefile);

                // Filesystem Cleanup
                $this->rrmdir($extractPath);

                return redirect()->to('admin/polygons')->with('success', 'Polygon imported successfully.');

            } catch (\Exception $e) {
                // Ensure resources are freed even on error before cleanup
                if(isset($Shapefile)) unset($Shapefile);
                $this->rrmdir($extractPath);
                
                return redirect()->back()->with('error', 'Error parsing SHP: ' . $e->getMessage());
            }

        } else {
            return redirect()->back()->with('error', 'Failed to open ZIP file.');
        }
    }

    public function delete($id)
    {
        $this->db->table('polygons')->where('id', $id)->delete();
        return redirect()->to('admin/polygons')->with('success', 'Polygon deleted.');
    }

    // Helper to recursively remove directory
    private function rrmdir($dir) {
        if (!is_dir($dir)) return;
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }

        @rmdir($dir);
    }

    private function cleanCoordinates($coords) {
        if (!is_array($coords) || empty($coords)) return $coords;
        // If it's a point array [x, y, ...]
        if (isset($coords[0]) && is_numeric($coords[0])) {
            return array_slice($coords, 0, 2);
        }
        foreach ($coords as $key => $val) {
            $coords[$key] = $this->cleanCoordinates($val);
        }
        return $coords;
    }
}
