<?= $this->extend('admin/layout') ?>

<?= $this->section('head') ?>
<!-- Leaflet Routing Machine CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    #map { height: 500px; width: 100%; z-index: 1; }
    .routing-btn { margin-top: 10px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- Task List (Left) -->
    <div class="md:col-span-1 space-y-4 h-screen overflow-y-auto pr-2">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Tugas Saya</h2>
        
        <?php if(empty($tasks)): ?>
            <div class="bg-white p-6 rounded shadow text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-2 text-green-300"></i>
                <p>Tidak ada tugas aktif.</p>
            </div>
        <?php else: ?>
            <?php foreach($tasks as $task): ?>
                <div class="bg-white p-4 rounded-lg shadow border-l-4 <?= $task['status'] == 'Sedang Dikerjakan' ? 'border-blue-500 ring-2 ring-blue-100' : 'border-yellow-400' ?>">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold px-2 py-1 rounded <?= $task['status'] == 'Sedang Dikerjakan' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' ?>">
                            <?= $task['status'] ?>
                        </span>
                        <span class="text-xs text-gray-500"><?= $task['nomor_tiket'] ?></span>
                    </div>
                    
                    <h3 class="font-bold text-gray-800 mb-1"><?= esc($task['nama_pelapor']) ?></h3>
                    <p class="text-sm text-gray-600 mb-2 truncate"><?= esc($task['alamat_detail']) ?></p>
                    
                    <div class="flex gap-2 mt-3">
                        <button onclick="routerTo(<?= $task['latitude'] ?>, <?= $task['longitude'] ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded text-sm font-semibold">
                            <i class="fas fa-directions text-blue-500"></i> Rute
                        </button>
                        
                        <!-- Action Button -->
                         <form action="<?= base_url('teknisi/update-status/' . $task['id']) ?>" method="post" class="flex-1">
                            <?= csrf_field() ?>
                            <?php if($task['status'] == 'Terverifikasi'): ?>
                                <input type="hidden" name="status" value="Sedang Dikerjakan">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm font-bold">
                                    Mulai
                                </button>
                            <?php elseif($task['status'] == 'Sedang Dikerjakan'): ?>
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded text-sm font-bold">
                                    Selesai
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Map (Right) -->
    <div class="md:col-span-2 relative">
        <div class="bg-white rounded-lg shadow-lg p-1">
            <div id="map" class="rounded-lg"></div>
        </div>
        
        <!-- Route Estimations Info -->
        <div id="routeInfo" class="hidden absolute top-4 right-4 bg-white p-4 rounded-lg shadow-lg border-l-4 border-blue-600 z-[1000] max-w-xs">
            <h4 class="font-bold text-gray-800 mb-2 border-b pb-1">Estimasi Perjalanan</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600"><i class="fas fa-road mr-1"></i> Jarak:</span>
                    <span class="font-bold" id="distDisplay">-- km</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><i class="fas fa-clock mr-1"></i> Waktu:</span>
                    <span class="font-bold text-blue-600" id="timeDisplay">-- menit</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2 italic">*Estimasi menggunakan mode berkendara (Driving)</p>
        </div>

        <div class="mt-4 bg-blue-50 p-4 rounded text-sm text-blue-800 border border-blue-100">
            <i class="fas fa-info-circle mr-1"></i> Klik tombol <b>Rute</b> pada daftar tugas untuk menampilkan jalur dari Kantor ke Lokasi.
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Leaflet Routing Machine JS -->
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    // Initialize Map centered on Office or default Medan
    var officeLat = <?= $office['latitude'] ?? 3.5952 ?>;
    var officeLng = <?= $office['longitude'] ?? 98.6722 ?>;
    
    var map = L.map('map').setView([officeLat, officeLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'OpenStreetMap'
    }).addTo(map);

    // Office Marker
    var officeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    L.marker([officeLat, officeLng], {icon: officeIcon}).addTo(map)
        .bindPopup("<b>Kantor Anda</b><br><?= $office['nama_kantor'] ?? 'Kantor Pusat' ?>").openPopup();

    // Task Markers
    var tasks = <?= json_encode($tasks) ?>;
    var taskMarkers = [];

    tasks.forEach(function(task) {
        var marker = L.marker([task.latitude, task.longitude]).addTo(map)
            .bindPopup("<b>" + task.nomor_tiket + "</b><br>" + task.nama_pelapor);
        
        taskMarkers.push(marker);
    });

    // Routing Control
    var routingControl = null;

    function routerTo(lat, lng) {
        // Remove existing route
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }

        // Create new route from Office to Task
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(officeLat, officeLng),
                L.latLng(lat, lng)
            ],
            routeWhileDragging: false,
            // Use OSRM demo server (default)
            lineOptions: {
                styles: [{color: 'blue', opacity: 0.6, weight: 6}]
            },
            createMarker: function() { return null; }, // Hide default markers created by routing
            show: false, // Minimize instructions by default
            addWaypoints: false
        }).addTo(map);

        // Listen for route found to update info box
        routingControl.on('routesfound', function(e) {
            var routes = e.routes;
            var summary = routes[0].summary;
            
            // Format distance (meters to km)
            var distKm = (summary.totalDistance / 1000).toFixed(1);
            
            // Format time (seconds to minutes)
            var timeMin = Math.round(summary.totalTime / 60);
            
            // Update UI
            document.getElementById('distDisplay').innerText = distKm + ' km';
            document.getElementById('timeDisplay').innerText = timeMin + ' menit';
            document.getElementById('routeInfo').classList.remove('hidden');
        });
    }
</script>
<?= $this->endSection() ?>
