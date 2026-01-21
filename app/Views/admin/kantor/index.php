<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?= $title ?></h1>
        <a href="<?= base_url('admin/kantor/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fas fa-plus mr-2"></i> Tambah Kantor
        </a>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kantor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi (Lat, Lng)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($items as $item): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= esc($item['nama_kantor']) ?></div>
                        <div class="text-sm text-gray-500"><?= esc($item['alamat_kantor']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <!-- Use Dynamic Type Logic -->
                        <?php if(!empty($item['nama_tipe'])): ?>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full text-white shadow-sm" style="background-color: <?= $item['warna'] ?? '#6c757d' ?>;">
                                <?= esc($item['nama_tipe']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs italic">Tanpa Tipe</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $item['latitude'] ?>, <?= $item['longitude'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= esc($item['email_notifikasi']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= base_url('admin/kantor/edit/' . $item['id']) ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <a href="<?= base_url('admin/kantor/delete/' . $item['id']) ?>" onclick="return confirm('Hapus data ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada data kantor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
