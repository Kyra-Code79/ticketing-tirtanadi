<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <h1 class="text-2xl font-bold text-gray-800">Data Laporan Masuk</h1>
    
    <form action="" method="get" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <select name="status" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua Status</option>
            <option value="Menunggu" <?= $filter_status == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
            <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>Proses</option>
            <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
        
        <input type="text" name="keyword" value="<?= esc($keyword) ?>" placeholder="Cari No. Tiket / Pelapor..." class="border rounded px-3 py-2 text-sm w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition">
            Filter
        </button>
    </form>
</div>

<!-- Table Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">No. Tiket / Tgl</th>
                    <th class="px-6 py-4">Pelapor</th>
                    <th class="px-6 py-4">Lokasi / Kantor</th>
                    <th class="px-6 py-4">Isi Laporan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($loran)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">
                            Tidak ada data laporan ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($loran as $item): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 align-top">
                            <div class="font-bold text-blue-600"><?= $item['nomor_tiket'] ?></div>
                            <div class="text-xs text-gray-400 mt-1"><?= date('d M Y H:i', strtotime($item['created_at'])) ?></div>
                            <?php if($item['is_urgent']): ?>
                                <span class="bg-red-100 text-red-600 text-[10px] px-1.5 py-0.5 rounded font-bold mt-1 inline-block">URGENT</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="font-medium text-gray-800"><?= esc($item['nama_pelapor']) ?></div>
                            <div class="text-xs text-gray-500 mt-0.5"><?= esc($item['no_hp']) ?></div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="text-gray-800 mb-0.5 font-medium"><?= esc($item['nama_kantor'] ?? 'Belum Ditentukan') ?></div>
                            <div class="text-xs text-gray-500 leading-tight w-48 truncate" title="<?= esc($item['alamat_detail']) ?>">
                                <?= esc($item['nama_kecamatan']) ?><br>
                                <?= esc($item['alamat_detail']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="text-gray-600 italic line-clamp-2 w-64 text-xs bg-gray-50 p-2 rounded border border-gray-100">
                                "<?= esc($item['isi_laporan']) ?>"
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-center">
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
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $color ?>">
                                <?= $item['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top text-center">
                            <a href="<?= base_url('admin/laporan/detail/' . $item['id']) ?>" 
                               class="inline-flex items-center justify-center w-8 h-8 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-blue-600 hover:border-blue-300 shadow-sm transition"
                               title="Lihat Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
        <?= $pager->links('laporan', 'default_full') ?>
    </div>
</div>
<?= $this->endSection() ?>
