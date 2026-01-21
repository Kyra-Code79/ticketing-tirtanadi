<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lapor Gangguan Air</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('logo-tirtanadi-notext.webp') ?>" type="image/x-icon">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0056b3',
                            green: '#28a745',
                        }
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                    }
                }
            }
        }
    </script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .input-large {
            font-size: 1.125rem; /* text-lg */
            padding: 0.75rem 1rem; /* py-3 px-4 */
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            width: 100%;
            margin-bottom: 1rem;
        }
        .btn-large {
            width: 100%;
            padding: 1rem;
            font-size: 1.25rem; /* text-xl */
            font-weight: bold;
            border-radius: 0.75rem;
            text-align: center;
            transition: all 0.2s;
        }
        /* Fix Map Overlap: Set z-0 to push it behind fixed elements if necessary, but keep it interactive */
        #map { height: 300px; width: 100%; border-radius: 0.75rem; display: none; margin-top: 1rem; z-index: 0; position: relative; }
    </style>
</head>
<body class="pb-24">

    <!-- Header -->
    <div class="bg-brand-blue text-white p-6 rounded-b-3xl shadow-lg relative z-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Halo Tirtanadi</h1>
                <p class="text-blue-100 text-sm">Lapor Gangguan Air Secepat Kilat</p>
            </div>
            <img src="<?= base_url('logo-tirtanadi-notext.webp') ?>" alt="Logo" class="h-12 bg-white rounded-full p-1">
        </div>
    </div>

    <div class=" px-4 -mt-2">
        <!-- Success/Error Alerts -->
        <?php if(session()->getFlashdata('success')): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-6 rounded shadow" role="alert">
                <p class="font-bold">Laporan Terkirim!</p>
                <p><?= session()->getFlashdata('success') ?></p>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('errors')): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-6 rounded shadow" role="alert">
                <p class="font-bold">Ada Kesalahan Data</p>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('pengaduan/store') ?>" method="POST" id="complaintForm" class="mt-6 space-y-6">
            
            <!-- SECTION 1: DATA PELAPOR -->
            <div class="bg-white p-6 rounded-2xl shadow-card">
                <h2 class="text-gray-800 font-bold text-lg mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-2">1</span>
                    Data Pelapor
                </h2>
                
                <label class="block text-gray-600 font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="nama_pelapor" class="input-large focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Budi Santoso" value="<?= old('nama_pelapor') ?>" required>

                <label class="block text-gray-600 font-medium mb-1">Nomor HP / WhatsApp</label>
                <input type="tel" name="no_hp" class="input-large focus:ring-2 focus:ring-blue-500 outline-none" placeholder="08xxxxxxxxxx" value="<?= old('no_hp') ?>" required>

                <label class="block text-gray-600 font-medium mb-1">Jenis Gangguan</label>
                <select name="jenis_aduan" class="input-large bg-white focus:ring-2 focus:ring-blue-500 outline-none" required>
                    <option value="" disabled selected>Pilih Masalah...</option>
                    <option value="Air Mati">Air Mati Total</option>
                    <option value="Air Kecil">Air Keluar Kecil</option>
                    <option value="Air Keruh">Air Keruh / Kotor</option>
                    <option value="Pipa Bocor">Pipa Bocor / Pecah</option>
                    <option value="Meteran Rusak">Meteran Rusak</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <!-- SECTION 2: LOKASI (MAGIC SECTION) -->
            <div class="bg-white p-6 rounded-2xl shadow-card border-2 border-dashed border-gray-200">
                <h2 class="text-gray-800 font-bold text-lg mb-2 flex items-center">
                    <span class="bg-green-100 text-green-600 rounded-full w-8 h-8 flex items-center justify-center mr-2">2</span>
                    Lokasi Gangguan
                </h2>
                <p class="text-gray-500 text-sm mb-4">Kami butuh lokasi Anda agar petugas cepat sampai.</p>

                <!-- BIG TRIGGER BUTTON -->
                <button type="button" id="btnDetect" class="btn-large bg-brand-green text-white hover:bg-green-700 shadow-lg active:scale-95 transform">
                    <i class="fas fa-map-marker-alt mr-2 animate-bounce"></i> DETEKSI LOKASI SAYA
                </button>

                <!-- Status & Validation -->
                <div id="locationStatus" class="mt-3 hidden text-center font-medium"></div>

                <!-- HIDDEN MAP (Shows on click) -->
                <div id="map"></div>
                
                <!-- Address Result -->
                <div class="mt-4">
                    <label class="block text-gray-600 font-medium mb-1">Alamat (Otomatis)</label>
                    <textarea name="alamat_detail" id="alamat_detail" rows="3" class="input-large bg-gray-50 mb-0" readonly placeholder="Alamat akan muncul otomatis disini..." required><?= old('alamat_detail') ?></textarea>
                    <p class="text-xs text-gray-400 mt-1 italic" id="manualHint">Jika alamat salah, Anda bisa ketik manual.</p>
                </div>

                <!-- Hidden Inputs -->
                <input type="hidden" name="latitude" id="lat" value="<?= old('latitude') ?>">
                <input type="hidden" name="longitude" id="lng" value="<?= old('longitude') ?>">
                <input type="hidden" name="nama_kecamatan" id="nama_kecamatan" value="<?= old('nama_kecamatan') ?>">
            </div>

            <!-- Footer Action -->
            <div class="fixed bottom-0 left-0 w-full bg-white p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50">
                <button type="submit" class="btn-large bg-brand-blue text-white hover:bg-blue-700 shadow-xl">
                    KIRIM LAPORAN <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </div>
            <div class="h-16"></div> <!-- Spacer for fixed footer -->

        </form>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const btnDetect = document.getElementById('btnDetect');
        const mapDiv = document.getElementById('map');
        const statusDiv = document.getElementById('locationStatus');
        const alamatInput = document.getElementById('alamat_detail');
        
        // Modal Elements
        const modal = document.createElement('div');
        modal.id = 'locationModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300" id="modalContent">
                <div class="bg-brand-blue p-4 text-white text-center">
                    <h3 class="font-bold text-lg"><i class="fas fa-map-marked-alt mr-2"></i>Konfirmasi Lokasi</h3>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Map Preview in Modal -->
                    <div id="modalMap" class="h-40 w-full rounded-lg border border-gray-300 shadow-inner"></div>
                    
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Alamat Terdeteksi:</p>
                        <p id="modalAddressText" class="font-bold text-gray-800 text-sm bg-gray-50 p-2 rounded border border-blue-100"></p>
                    </div>

                    <div id="manualSearchSection" class="hidden space-y-2 pt-2 border-t">
                         <label class="block text-sm font-medium text-gray-700">Cari Alamat Manual</label>
                         <div class="flex gap-2">
                            <input type="text" id="manualInput" class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Jl. Mongonsidi No. 5 Medan">
                            <button type="button" id="btnSearchManual" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm"><i class="fas fa-search"></i></button>
                         </div>
                    </div>

                    <p class="text-center text-gray-600 text-sm" id="confirmQuestion">Apakah lokasi ini sudah sesuai?</p>

                    <div class="grid grid-cols-2 gap-3 pt-2" id="actionButtons">
                        <button type="button" id="btnManualMode" class="border border-gray-300 text-gray-700 font-bold py-2 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition text-sm">
                            <i class="fas fa-edit mr-1"></i> Tidak, Ubah
                        </button>
                        <button type="button" id="btnConfirmLocation" class="bg-brand-green text-white font-bold py-2 rounded-xl hover:bg-green-700 shadow-lg transform hover:scale-105 transition text-sm">
                            <i class="fas fa-check mr-1"></i> Ya, Sesuai
                        </button>
                    </div>
                    
                    <button type="button" id="btnSaveManual" class="hidden w-full bg-brand-green text-white font-bold py-3 rounded-xl hover:bg-green-700 shadow-lg text-sm">
                        <i class="fas fa-save mr-1"></i> SIMPAN LOKASI INI
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        let map, marker, modalMap, modalMarker;
        let currentTempLat, currentTempLng, currentTempAddr;

        // Helper to init map
        function initMainMap(lat, lng) {
            mapDiv.style.display = 'block';
            if (!map) {
                map = L.map('map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                marker.bindPopup("Geser pin untuk sesuaikan lokasi").openPopup();
                
                // DRAG LISTENER (Updated requirement)
                marker.on('dragend', async function(e) {
                    const pos = marker.getLatLng();
                    updateCoordinates(pos.lat, pos.lng);
                    
                    // Visual feedback
                    statusDiv.innerHTML = '<span class="text-orange-500"><i class="fas fa-spinner fa-spin"></i> Memperbarui alamat...</span>';
                    
                    try {
                        const data = await fetchAddress(pos.lat, pos.lng);
                        if(data) {
                             let simpleAddr = data.address.road ? `${data.address.road}, ${data.address.suburb || ''}` : data.display_name;
                             alamatInput.value = data.display_name; // Full address
                             statusDiv.innerHTML = `<span class="text-green-600 font-bold block bg-green-50 p-2 rounded border border-green-200 mt-2"><i class="fas fa-check-circle"></i> Lokasi: ${simpleAddr}</span>`;
                             
                             // Update persistence
                             saveStateToLocal(pos.lat, pos.lng, data.display_name);
                        }
                    } catch(err) {
                        console.error(err);
                    }
                });
            } else {
                map.flyTo([lat, lng], 16);
                marker.setLatLng([lat, lng]);
            }
            // Fix overlap/tile loading issues
            setTimeout(() => { map.invalidateSize(); }, 300);
        }

        // Init Modal Map
        function initModalMap(lat, lng) {
            if (!modalMap) {
                modalMap = L.map('modalMap', { attributionControl: false, zoomControl: false }).setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(modalMap);
                modalMarker = L.marker([lat, lng], {draggable: true}).addTo(modalMap); // Make modal marker draggable too!
                
                 // Modal Drag Listener
                modalMarker.on('dragend', async function(e) {
                     const pos = modalMarker.getLatLng();
                     currentTempLat = pos.lat;
                     currentTempLng = pos.lng;
                     
                     document.getElementById('modalAddressText').innerText = "Memuat ulang alamat...";
                     try{
                         const data = await fetchAddress(pos.lat, pos.lng);
                         currentTempAddr = data.display_name;
                         let simpleAddr = data.address.road ? `${data.address.road}, ${data.address.suburb || ''}` : data.display_name;
                         document.getElementById('modalAddressText').innerText = simpleAddr;
                     } catch(e) {}
                });

            } else {
                modalMap.setView([lat, lng], 16);
                modalMarker.setLatLng([lat, lng]);
            }
            // Fix Leaflet sizing in hidden modal
            setTimeout(() => { modalMap.invalidateSize(); }, 350); 
        }

        function updateCoordinates(lat, lng) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        }

        function saveStateToLocal(lat, lng, addr) {
            localStorage.setItem('trt_lat', lat);
            localStorage.setItem('trt_lng', lng);
            localStorage.setItem('trt_addr', addr);
        }

        function showModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                document.getElementById('modalContent').classList.remove('scale-95');
                document.getElementById('modalContent').classList.add('scale-100');
                // Double check map size
                if(modalMap) modalMap.invalidateSize();
            }, 10);
        }

        function hideModal() {
            modal.classList.add('opacity-0');
            document.getElementById('modalContent').classList.remove('scale-100');
            document.getElementById('modalContent').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                resetModalState();
            }, 300);
        }
        
        function resetModalState() {
             document.getElementById('manualSearchSection').classList.add('hidden');
             document.getElementById('confirmQuestion').innerText = "Apakah lokasi ini sudah sesuai?";
             document.getElementById('actionButtons').classList.remove('hidden');
             document.getElementById('btnSaveManual').classList.add('hidden');
        }

        // NOMINATIM API HELPERS
        async function fetchAddress(lat, lng) {
             const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
             return await response.json();
        }

        async function fetchCoords(query) {
             const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
             return await response.json();
        }

        // --- ON LOAD CHECK (Data Persistence) ---
        // --- ON LOAD CHECK (Data Persistence) ---
        window.addEventListener('load', () => {
            // Check Server Side Flash Data first (if validation failed)
            const oldLat = document.getElementById('lat').value;
            const oldLng = document.getElementById('lng').value;
            const oldAddr = document.getElementById('alamat_detail').value;

            if (oldLat && oldLng && oldAddr) {
                restoreUI(oldLat, oldLng, oldAddr);
            } else {
                // Check LocalStorage (if refreshed page)
                const lsLat = localStorage.getItem('trt_lat');
                const lsLng = localStorage.getItem('trt_lng');
                const lsAddr = localStorage.getItem('trt_addr');
                
                if (lsLat && lsLng && lsAddr) {
                     // Fill inputs
                     updateCoordinates(lsLat, lsLng);
                     alamatInput.value = lsAddr;
                     restoreUI(lsLat, lsLng, lsAddr);
                }
            }
        });
        
        function restoreUI(lat, lng, addr) {
            initMainMap(lat, lng);
            statusDiv.innerHTML = `<span class="text-green-600 font-bold block bg-green-50 p-2 rounded border border-green-200 mt-2"><i class="fas fa-check-circle"></i> Lokasi Terkunci: ${addr.split(',')[0]}...</span>`;
            statusDiv.classList.remove('hidden');
            btnDetect.innerHTML = '<i class="fas fa-check"></i> Lokasi Terkunci';
            btnDetect.classList.remove('bg-brand-green');
            btnDetect.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
        }

        // --- EVENT FLOW ---

        // 1. Trigger Detection
        btnDetect.addEventListener('click', () => {
            // Check if already locked (Prevent re-click loop if user wants to keep data)
            if (btnDetect.classList.contains('cursor-not-allowed')) return;

             if (navigator.geolocation) {
                btnDetect.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendeteksi...';
                
                // Timeout logic for slower devices
                const geoOptions = {
                    enableHighAccuracy: true,
                    timeout: 15000, 
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(async (position) => {
                    const { latitude, longitude } = position.coords;
                    
                    currentTempLat = latitude;
                    currentTempLng = longitude;

                    // Reverse Geocode
                    try {
                        const data = await fetchAddress(latitude, longitude);
                        currentTempAddr = data.display_name;
                        
                        // Parse simple address for display
                        let simpleAddr = data.address.road ? `${data.address.road}, ${data.address.suburb || ''}` : data.display_name;
                        
                        // Show Modal
                        document.getElementById('modalAddressText').innerText = simpleAddr;
                        initModalMap(latitude, longitude);
                        showModal();

                        // Store temp data
                        currentTempAddr = data.display_name;

                    } catch (e) {
                        alert("Gagal mengambil alamat dari satelit. Silakan gunakan Mode Manual di modal.");
                        // Fallback: still show modal but with empty address
                        currentTempAddr = "Alamat tidak ditemukan";
                        document.getElementById('modalAddressText').innerText = "Alamat tidak detil. Silakan ubah manual.";
                        initModalMap(latitude, longitude);
                        showModal();
                    }
                    
                    btnDetect.innerHTML = '<i class="fas fa-map-marker-alt"></i> DETEKSI LOKASI SAYA';
                }, (err) => {
                    console.warn("Geo Error:", err);
                    let msg = "Gagal deteksi GPS.";
                    if(err.code === 1) msg = "Izin lokasi ditolak. Harap izinkan di pengaturan browser.";
                    else if(err.code === 2) msg = "Sinyal GPS lemah/tidak tersedia.";
                    else if(err.code === 3) msg = "Waktu deteksi habis (Timeout).";
                    
                    alert(msg + " Silakan masukkan alamat manual.");
                    
                    // Fallback to manual entry immediately?
                    document.getElementById('alamat_detail').readOnly = false;
                    document.getElementById('alamat_detail').focus();
                    btnDetect.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal - Ketik Manual';
                }, geoOptions);
             } else {
                 alert("Browser tidak support GPS. Gunakan Chrome/Safari terbaru.");
                 document.getElementById('alamat_detail').readOnly = false;
             }
        });

        // 2. Modal Actions
        
        // "Ya, Sesuai"
        document.getElementById('btnConfirmLocation').addEventListener('click', () => {
            // Commit Data
            alamatInput.value = currentTempAddr;
            updateCoordinates(currentTempLat, currentTempLng);
            initMainMap(currentTempLat, currentTempLng);
            
            // Save to LocalStorage
            saveStateToLocal(currentTempLat, currentTempLng, currentTempAddr);
            
            // UI Feedback
            statusDiv.innerHTML = `<span class="text-green-600 font-bold block bg-green-50 p-2 rounded border border-green-200 mt-2"><i class="fas fa-check-circle"></i> Lokasi Terkunci: ${currentTempAddr.split(',')[0]}...</span>`;
            statusDiv.classList.remove('hidden');
            btnDetect.innerHTML = '<i class="fas fa-check"></i> Lokasi Terkunci';
            btnDetect.classList.remove('bg-brand-green');
            btnDetect.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
            // Prevent re-clicking logic handled in click event
            
            hideModal();
        });
        
         // Clear LocalStorage on Submit Success (Optional, we can do this on page load if success flash exists)
         document.getElementById('complaintForm').addEventListener('submit', () => {
             // We keep it for now in case submission fails. 
             // Ideally clear it only when server returns success.
         });

        // "Tidak, Ubah Manual"
        document.getElementById('btnManualMode').addEventListener('click', () => {
             document.getElementById('manualSearchSection').classList.remove('hidden');
             document.getElementById('confirmQuestion').innerText = "Cari alamat, lalu klik Simpan.";
             document.getElementById('actionButtons').classList.add('hidden');
             document.getElementById('btnSaveManual').classList.remove('hidden');
        });

        // "Cari Manual" (Forward Geocoding)
        document.getElementById('btnSearchManual').addEventListener('click', async () => {
             const query = document.getElementById('manualInput').value;
             if(!query) return;

             document.getElementById('btnSearchManual').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

             try {
                 const results = await fetchCoords(query);
                 if (results && results.length > 0) {
                     const hit = results[0];
                     currentTempLat = parseFloat(hit.lat);
                     currentTempLng = parseFloat(hit.lon);
                     currentTempAddr = hit.display_name;

                     // Update Modal View
                     modalMap.setView([currentTempLat, currentTempLng], 16);
                     modalMarker.setLatLng([currentTempLat, currentTempLng]);
                     document.getElementById('modalAddressText').innerText = hit.display_name;
                     // Force resize
                     modalMap.invalidateSize();
                 } else {
                     alert("Alamat tidak ditemukan di peta. Coba nama jalan yang lebih spesifik.");
                 }
             } catch (e) {
                 alert("Gagal mencari alamat.");
             }
             document.getElementById('btnSearchManual').innerHTML = '<i class="fas fa-search"></i>';
        });

        // "Simpan Lokasi Ini" (From Manual)
        document.getElementById('btnSaveManual').addEventListener('click', () => {
             // Commit Data logic same as Confirm
             document.getElementById('btnConfirmLocation').click();
        });

    </script>
</body>
</html>
