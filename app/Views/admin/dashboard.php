<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
        <p class="text-gray-500 text-sm">Selamat datang, <span class="font-semibold text-blue-600"><?= $user['nama_lengkap'] ?></span> (<?= $user['role'] ?>)</p>
    </div>
    <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded shadow-sm">
        <?= date('d F Y, H:i') ?>
    </div>
</div>

<!-- Map Section -->
<div class="bg-white p-6 rounded-xl shadow-sm mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <div>
            <h3 class="font-bold text-gray-700">Peta Sebaran Laporan</h3>
            <span class="text-xs font-mono bg-gray-200 px-2 py-1 rounded">Debug Data: <?= count($map_data) ?> items</span>
        </div>
        
        <!-- Filter Form -->
        <form action="<?= base_url('admin/dashboard') ?>" method="get" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-1.5">
                <option value="default_active" <?= ($filter_status == 'default_active') ? 'selected' : '' ?>>Default (Hide Selesai)</option>
                <option value="all" <?= ($filter_status == 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="Menunggu" <?= ($filter_status == 'Menunggu') ? 'selected' : '' ?>>Menunggu</option>
                <option value="Proses" <?= ($filter_status == 'Proses') ? 'selected' : '' ?>>Sedang Proses</option>
                <option value="Selesai" <?= ($filter_status == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
            </select>

            <select name="polygon_id" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-1.5 max-w-[150px]">
                <option value="">-- Semua Area --</option>
                <?php foreach($polygons as $poly): ?>
                    <option value="<?= $poly['id'] ?>" <?= ($filter_polygon_id == $poly['id']) ? 'selected' : '' ?>>
                        <?= $poly['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if($filter_status != 'default_active' || $filter_polygon_id): ?>
                <a href="<?= base_url('admin/dashboard') ?>" class="text-sm text-red-500 hover:text-red-700 font-medium">Reset</a>
            <?php endif; ?>

            <!-- Office Toggle -->
            <div class="ml-auto flex items-center bg-white border rounded-md px-3 py-1.5 shadow-sm">
                <input type="checkbox" id="toggleOffice" checked onchange="toggleOffices(this)" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                <label for="toggleOffice" class="ml-2 text-sm text-gray-700 font-medium cursor-pointer select-none">
                    <i class="fas fa-building text-blue-500 mr-1"></i> Kantor
                </label>
            </div>
        </form>
    </div>
    <div id="adminMap" class="h-96 w-full rounded-lg z-0"></div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Card Total -->
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Laporan</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['total'] ?></h3>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>

    <!-- Card Menunggu -->
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Menunggu</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['menunggu'] ?></h3>
            </div>
            <div class="p-2 bg-yellow-100 rounded-lg text-yellow-600">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Card Proses -->
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Sedang Proses</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['proses'] ?></h3>
            </div>
            <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                <i class="fas fa-wrench"></i>
            </div>
        </div>
    </div>

    <!-- Card Selesai -->
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Selesai</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['selesai'] ?></h3>
            </div>
            <div class="p-2 bg-green-100 rounded-lg text-green-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

</div>

<!-- Sections Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Urgent Alerts -->
    <?php if($stats['urgent'] > 0): ?>
    <div class="bg-red-50 p-6 rounded-xl border border-red-100 mb-6 lg:mb-0">
        <div class="flex items-center mb-4">
            <div class="bg-red-100 text-red-600 p-2 rounded-lg mr-3 animate-pulse">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="font-bold text-red-700 text-lg">Perhatian: <?= $stats['urgent'] ?> Laporan Urgent!</h3>
        </div>
        <p class="text-red-600 text-sm mb-4">Terdeteksi penumpukan laporan di lokasi yang berdekatan. Segera tindak lanjuti.</p>
        <a href="<?= base_url('admin/laporan?filter=urgent') ?>" class="inline-block bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">
            Lihat Laporan Urgent <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- Recent Reports -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-700">Laporan Terbaru</h3>
            <a href="<?= base_url('admin/laporan') ?>" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach($recent_reports as $rpt): ?>
            <div class="p-4 hover:bg-gray-50 transition cursor-pointer">
                <div class="flex justify-between items-start mb-1">
                    <span class="font-bold text-gray-800 text-sm"><?= $rpt['nomor_tiket'] ?></span>
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $rpt['status'] == 'Menunggu' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $rpt['status'] ?>
                    </span>
                </div>
                <p class="text-sm text-gray-600 truncate w-full mb-1"><?= $rpt['isi_laporan'] ?></p>
                <div class="flex items-center text-xs text-gray-400">
                    <span><i class="far fa-clock mr-1"></i> <?= date('d M H:i', strtotime($rpt['created_at'])) ?></span>
                    <span class="mx-2">•</span>
                    <span><i class="fas fa-map-marker-alt mr-1"></i> <?= $rpt['nama_kecamatan'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($recent_reports)): ?>
                <div class="p-8 text-center text-gray-400">Belum ada laporan masuk.</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize Map
    var map = L.map('adminMap').setView([3.595196, 98.672223], 12); // Default Medan

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Map Data from Controller
    var reports = <?= json_encode($map_data) ?>;
    console.log("DEBUG: Map Reports Data:", reports);

    if (reports.length === 0) {
        console.warn("DEBUG: No reports data found!");
    } else {
        console.log("DEBUG: Found " + reports.length + " reports. Adding markers...");
    }

    // Custom Icons
    var icons = {
        'Menunggu': L.divIcon({className: 'marker-menunggu', html: '<div class="w-4 h-4 rounded-full bg-yellow-500 border-2 border-white shadow-lg"></div>'}),
        'Terverifikasi': L.divIcon({className: 'marker-verif', html: '<div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-lg"></div>'}),
        'Sedang Dikerjakan': L.divIcon({className: 'marker-proses', html: '<div class="w-4 h-4 rounded-full bg-purple-500 border-2 border-white shadow-lg"></div>'}),
        'Selesai': L.divIcon({className: 'marker-selesai', html: '<div class="w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow-lg"></div>'}),
        'Ditolak': L.divIcon({className: 'marker-tolak', html: '<div class="w-4 h-4 rounded-full bg-red-500 border-2 border-white shadow-lg"></div>'})
    };

    // Add Markers
    var bounds = new L.LatLngBounds();
    var hasMarkers = false;

    reports.forEach(function(rpt) {
        if(rpt.latitude && rpt.longitude) {
            var marker = L.marker([rpt.latitude, rpt.longitude], {
                icon: icons[rpt.status] || icons['Menunggu']
            }).addTo(map);
            
            var popupContent = `
                <div class="p-2 w-[220px]">
                    <div class="flex justify-between items-center mb-2 border-b pb-1">
                        <b class="text-blue-700 text-sm">${rpt.nomor_tiket}</b>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${getStatusColor(rpt.status)}">
                            ${rpt.status}
                        </span>
                    </div>
                    
                    <div class="space-y-1 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-500 uppercase font-bold text-xs">Nama Pelapor</span>
                            <div class="font-medium text-gray-800 truncate">${rpt.nama_pelapor || '-'}</div>
                        </div>
                        
                        <div>
                            <span class="text-[10px] text-gray-500 uppercase font-bold">Isi Laporan</span>
                            <div class="text-gray-700 bg-gray-50 p-1 rounded border border-gray-100 italic line-clamp-2" title="${rpt.isi_laporan}">
                                "${rpt.isi_laporan}"
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] text-gray-500 uppercase font-bold">Kantor Tujuan</span>
                            <div class="font-medium text-gray-800 truncate" title="${rpt.nama_kantor}">${rpt.nama_kantor || '-'}</div>
                        </div>

                        <div>
                            <span class="text-[10px] text-gray-500 uppercase font-bold">Alamat</span>
                            <div class="text-gray-600 leading-tight line-clamp-3" title="${rpt.alamat_detail || rpt.nama_kecamatan}">
                                ${rpt.alamat_detail || rpt.nama_kecamatan}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
            
            bounds.extend([rpt.latitude, rpt.longitude]);
            hasMarkers = true;
        }
    });

    // Draw Polygons
    var selectedPoly = <?= json_encode($selected_polygon) ?>;
    var allPolygons = <?= json_encode($polygons) ?>;
    
    // console.log("DEBUG: Selected Poly:", selectedPoly);

    if (selectedPoly && selectedPoly.geometry) {
        // Draw ACTIVE Polygon
        drawPolygon(selectedPoly, true);
    } else if (allPolygons && allPolygons.length > 0) {
        // Draw ALL Polygons (Overview)
        // console.log("DEBUG: Drawing all " + allPolygons.length + " polygons overview.");
        allPolygons.forEach(function(poly) {
             if(poly.geometry) {
                 drawPolygon(poly, false);
             }
        });
        if (hasMarkers) map.fitBounds(bounds);
    } else if (hasMarkers) {
        // Only fit to markers if no polygon selected
        map.fitBounds(bounds);
    }

    function drawPolygon(polyData, isActive) {
        try {
            var geoJsonData = JSON.parse(polyData.geometry);
            
            // Fix Types & Sanitize Coordinates (Reuse logic)
            if (geoJsonData.features && geoJsonData.features.length > 0) {
                 geoJsonData.features.forEach(function(f) {
                    if (f.geometry) {
                        if (f.geometry.type) f.geometry.type = f.geometry.type.replace(/[MZ]$/, '');
                        if (f.geometry.coordinates) f.geometry.coordinates = stripExtraDimensions(f.geometry.coordinates);
                    }
                 });
            }

            var layer = L.geoJSON(geoJsonData, {
                style: function (feature) {
                    return {
                        color: polyData.color || '#3388ff',
                        weight: isActive ? 3 : 1, // Thicker if active
                        opacity: 1,
                        fillOpacity: isActive ? 0.2 : 0.05 // Fainter if overview
                    };
                },
                onEachFeature: function (feature, layer) {
                     // Add tooltip for Name
                     layer.bindTooltip(polyData.name, {permanent: false, direction: "center"});
                }
            }).addTo(map);

            if (isActive) {
                 var b = layer.getBounds();
                 if(b.isValid()) map.fitBounds(b);
            }
        } catch(e) {
            console.error("Invalid GeoJSON for " + polyData.name, e);
        }
    }


    
    function getStatusColor(status) {
        switch(status) {
            case 'Menunggu': return 'bg-yellow-100 text-yellow-700';
            case 'Terverifikasi': return 'bg-blue-100 text-blue-700';
            case 'Proses': return 'bg-purple-100 text-purple-700';
            case 'Sedang Dikerjakan': return 'bg-purple-100 text-purple-700';
            case 'Selesai': return 'bg-green-100 text-green-700';
            case 'Ditolak': return 'bg-red-100 text-red-700';
            default: return 'bg-gray-100 text-gray-700';
        }
    }

    // --- OFFICE MARKERS LOGIC ---
    var offices = <?= json_encode($offices) ?>;
    var officeLayer = L.layerGroup();
    var showOffices = true; // Default ON

    var officeIcon = L.icon({
        iconUrl: '<?= base_url('logo-tirtanadi-notext.webp') ?>',
        iconSize: [32, 32], // Adjust size
        iconAnchor: [16, 16],
        popupAnchor: [0, -16],
        className: 'office-marker-icon' // For extra CSS styling (e.g. shadow/border)
    });

    offices.forEach(function(office) {
        if(office.latitude && office.longitude) {
            // Optional: Circle behind icon to show Type Color
            var color = office.warna || '#6c757d';
            
            // Create a custom DivIcon that combines the Image + Colored Border/Halo
            var customIcon = L.divIcon({
                className: 'custom-office-marker',
                html: `<div style="
                        width: 36px; height: 36px; 
                        background: white; 
                        border: 3px solid ${color}; 
                        border-radius: 50%; 
                        display: flex; align-items: center; justify-content: center;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                        overflow: hidden;
                       ">
                        <img src="<?= base_url('logo-tirtanadi-notext.webp') ?>" style="width: 24px; height: 24px; object-fit: contain;">
                       </div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            });

            var marker = L.marker([office.latitude, office.longitude], {icon: customIcon});
            
            var popup = `
                <div class="p-2 text-center">
                    <b class="text-gray-800 block mb-1">${office.nama_kantor}</b>
                    <span class="text-xs text-white px-2 py-0.5 rounded-full font-bold" style="background-color: ${color}">
                        ${office.nama_tipe || 'Kantor'}
                    </span>
                    <p class="text-xs text-gray-600 mt-2 text-left">${office.alamat_kantor || ''}</p>
                </div>
            `;
            marker.bindPopup(popup);
            officeLayer.addLayer(marker);
        }
    });

    // Add by default
    officeLayer.addTo(map);

    // Toggle Function
    function toggleOffices(checkbox) {
        if (checkbox.checked) {
            map.addLayer(officeLayer);
        } else {
            map.removeLayer(officeLayer);
        }
    }

    // --- RECURSIVE Function ---
    function stripExtraDimensions(coords) {
        if (!Array.isArray(coords)) return coords;
        if (coords.length === 0) return coords;

        // Check if it is a coordinate point [x, y, z, m]
        if (typeof coords[0] === 'number') {
            return coords.slice(0, 2); // Keep only [lng, lat]
        } 
        
        // Recursive for arrays of arrays
        return coords.map(stripExtraDimensions);
    }
</script>
<?= $this->endSection() ?>
