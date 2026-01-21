<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="<?= base_url('admin/kantor') ?>" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2"><?= $title ?></h1>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 p-4">
             <h2 class="text-white font-bold text-lg"><i class="fas fa-building mr-2"></i> Form Data Kantor</h2>
        </div>
        
        <form action="<?= $item ? base_url('admin/kantor/update/' . $item['id']) : base_url('admin/kantor/store') ?>" method="post" class="p-6">
            
            <!-- SECTION 1: AUTO FILL -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <label class="block text-blue-900 text-sm font-bold mb-2">
                    <i class="fas fa-magic mr-1"></i> Isi Otomatis dari Google Maps
                </label>
                <div class="flex gap-2">
                    <input type="text" id="gmapsUrl" class="flex-1 shadow-sm border border-blue-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Tempel link Google Maps disini (ex: https://goo.gl/maps/...)">
                    <button type="button" id="btnParseMaps" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition transition-transform active:scale-95">
                        <i class="fas fa-search-location mr-1"></i> Cari & Set Lokasi
                    </button>
                </div>
                <p class="text-xs text-blue-600 mt-1" id="parseStatus">Support link pendek (goo.gl) atau link panjang dari browser.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Left Column: Details -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kantor *</label>
                        <input type="text" name="nama_kantor" value="<?= $item['nama_kantor'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 outline-none transition" placeholder="Contoh: Cabang Medan Kota" required>
                    </div>

                    <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipe / Label *</label>
                    <select name="type_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 outline-none bg-white" required>
                        <option value="" disabled selected>Pilih Tipe...</option>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($item && $item['type_id'] == $t['id']) ? 'selected' : '' ?>>
                                <?= esc($t['nama_tipe']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email Notifikasi</label>
                        <input type="email" name="email_notifikasi" value="<?= $item['email_notifikasi'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 outline-none transition" placeholder="admin.cabang@tirtanadi.co.id">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap</label>
                        <textarea name="alamat_kantor" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 outline-none transition"><?= $item['alamat_kantor'] ?? '' ?></textarea>
                    </div>
                </div>

                <!-- Right Column: Map -->
                <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Titik Koordinat *</label>
                     <div class="border rounded-lg overflow-hidden shadow-sm relative">
                        <div id="map" class="h-64 w-full z-0"></div>
                        <!-- Current Coordinates Overlay -->
                        <div class="absolute bottom-2 left-2 right-2 bg-white/90 backdrop-blur-sm p-2 rounded shadow text-xs grid grid-cols-2 gap-2 z-10">
                            <div>
                                <span class="text-gray-500 block">Latitude</span>
                                <input type="text" name="latitude" id="lat" value="<?= $item['latitude'] ?? '' ?>" class="font-mono font-bold text-gray-800 bg-transparent w-full outline-none" readonly required>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Longitude</span>
                                <input type="text" name="longitude" id="lng" value="<?= $item['longitude'] ?? '' ?>" class="font-mono font-bold text-gray-800 bg-transparent w-full outline-none" readonly required>
                            </div>
                        </div>
                     </div>
                     <p class="text-xs text-gray-500 mt-1 italic"><i class="fas fa-info-circle"></i> Klik pada peta atau geser pin untuk menyesuaikan.</p>
                </div>
            </div>

            <div class="flex items-center justify-end mt-8 pt-4 border-t border-gray-100">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> SIMPAN DATA
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Init Map
    var initialLat = <?= $item['latitude'] ?? 3.595196 ?>;
    var initialLng = <?= $item['longitude'] ?? 98.672223 ?>;
    var zoom = <?= $item ? 16 : 12 ?>;

    var map = L.map('map').setView([initialLat, initialLng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker;

    function setMarker(lat, lng) {
        if(marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            marker.on('dragend', function(e) {
                updateInput(marker.getLatLng());
            });
        }
        map.flyTo([lat, lng], 16);
        updateInput({lat: lat, lng: lng});
    }

    function updateInput(latlng) {
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lng').value = latlng.lng;
    }

    // Initial Marker
    <?php if($item && $item['latitude']): ?>
        setMarker(initialLat, initialLng);
    <?php endif; ?>

    // Click to move
    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    // Parse Maps URL Logic
    document.getElementById('btnParseMaps').addEventListener('click', function() {
        const url = document.getElementById('gmapsUrl').value;
        const status = document.getElementById('parseStatus');
        
        if(!url) {
            alert('Masukkan link terlebih dahulu!');
            return;
        }

        status.innerHTML = '<span class="text-orange-500"><i class="fas fa-spinner fa-spin"></i> Sedang memproses link...</span>';
        
        const formData = new FormData();
        formData.append('url', url);

        fetch('<?= base_url('admin/kantor/parse-map') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                setMarker(data.lat, data.lng);
                status.innerHTML = '<span class="text-green-600 font-bold"><i class="fas fa-check"></i> Lokasi ditemukan! (' + data.lat + ', ' + data.lng + ')</span>';
            } else {
                status.innerHTML = '<span class="text-red-500 font-bold"><i class="fas fa-times"></i> ' + data.message + '</span>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            status.innerHTML = '<span class="text-red-500">Terjadi kesalahan sistem.</span>';
        });
    });
</script>
<?= $this->endSection() ?>
