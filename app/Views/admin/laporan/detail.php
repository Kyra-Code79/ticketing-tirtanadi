<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Report Information (2/3 width) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 border-b border-gray-100 pb-4 mb-4">
                <div>
                    <div class="text-sm text-gray-400 font-medium uppercase tracking-wider mb-1">Nomor Tiket</div>
                    <h1 class="text-3xl font-bold text-blue-700 tracking-tight"><?= $item['nomor_tiket'] ?></h1>
                    <div class="text-sm text-gray-500 mt-1">
                        Dibuat pada: <?= date('d F Y, H:i', strtotime($item['created_at'])) ?>
                    </div>
                </div>
                
                 <?php
                    $color = 'bg-gray-100 text-gray-600';
                    switch($item['status']) {
                        case 'Menunggu': $color = 'bg-yellow-100 text-yellow-700'; break;
                        case 'Proses': 
                        case 'Sedang Dikerjakan':
                        case 'Terverifikasi': 
                            $color = 'bg-blue-100 text-blue-700'; break;
                        case 'Selesai': $color = 'bg-green-100 text-green-700'; break;
                        case 'Ditolak': $color = 'bg-red-100 text-red-700'; break;
                    }
                ?>
                <div class="flex items-center gap-3">
                    <?php if($item['is_urgent']): ?>
                        <span class="bg-red-100 text-red-600 px-3 py-1 rounded font-bold uppercase text-xs animate-pulse">
                            Urgent Priority
                        </span>
                    <?php endif; ?>
                    <span class="px-4 py-2 rounded-lg font-bold text-sm shadow-sm <?= $color ?>">
                        <?= $item['status'] ?>
                    </span>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-2">
                <h3 class="text-gray-900 font-bold mb-2 text-lg">Isi Laporan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 text-gray-700 leading-relaxed italic">
                    "<?= esc($item['isi_laporan']) ?>"
                </div>
            </div>
        </div>

        <!-- Location Card with Map -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-gray-900 font-bold mb-4 text-lg border-b pb-2">Lokasi Kejadian</h3>
            
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold mb-1">Kecamatan</div>
                    <div class="font-medium text-gray-800"><?= esc($item['nama_kecamatan']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold mb-1">Kantor Wilayah</div>
                    <div class="font-medium text-gray-800"><?= esc($item['nama_kantor']) ?></div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="text-xs text-gray-500 uppercase font-bold mb-1">Alamat Detail</div>
                <div class="text-gray-700"><?= esc($item['alamat_detail']) ?></div>
            </div>

            <!-- Map Container -->
            <div id="detailMap" class="w-full h-80 rounded-lg shadow-inner z-0"></div>
        </div>

    </div>

    <!-- Right Column: Meta Info & Actions (1/3 width) -->
    <div class="space-y-6">
        
        <!-- Action Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-gray-900 font-bold mb-4 border-b pb-2">Tindak Lanjut</h3>
            
            <form action="<?= base_url('admin/laporan/update/' . $item['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Update Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                        <option value="Menunggu" <?= $item['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Terverifikasi" <?= $item['status'] == 'Terverifikasi' ? 'selected' : '' ?>>Terverifikasi</option>
                        <option value="Sedang Dikerjakan" <?= $item['status'] == 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                        <option value="Selesai" <?= $item['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Ditolak" <?= $item['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-sm transition active:scale-95">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Reporter Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-gray-900 font-bold mb-4 border-b pb-2">Data Pelapor</h3>
            
            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold mb-1">Nama Lengkap</div>
                    <div class="font-medium text-gray-800"><?= esc($item['nama_pelapor']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold mb-1">Nomor HP / WhatsApp</div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800"><?= esc($item['no_hp']) ?></span>
                        <a href="https://wa.me/<?= preg_replace('/^0/', '62', $item['no_hp']) ?>" target="_blank" class="text-green-500 hover:text-green-600">
                             <!-- WA Icon -->
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evidence Photo -->
        <?php if(!empty($item['foto_bukti'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-gray-900 font-bold mb-4 border-b pb-2">Foto Bukti</h3>
            <div class="rounded-lg overflow-hidden border border-gray-200 cursor-pointer" onclick="openPhotoModal(this)">
                <img src="<?= base_url('uploads/pengaduan/' . $item['foto_bukti']) ?>" alt="Bukti Laporan" class="w-full h-auto hover:opacity-90 transition">
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">Klik foto untuk memperbesar</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Scripts for Map -->
<?= $this->section('scripts') ?>
<script>
    var lat = <?= $item['latitude'] ?>;
    var lng = <?= $item['longitude'] ?>;

    var map = L.map('detailMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("<b>Lokasi Kejadian</b><br><?= esc($item['alamat_detail']) ?>")
        .openPopup();

    function openPhotoModal(el) {
        // Implement simple lightbox if needed, or just open in new tab
        var src = el.querySelector('img').src;
        window.open(src, '_blank');
    }
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
