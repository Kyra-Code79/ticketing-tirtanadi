<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto max-w-lg">
    <div class="mb-6">
        <a href="<?= base_url('admin/roles') ?>" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2"><?= $title ?></h1>
    </div>

    <!-- Error Messages -->
    <?php if(session()->getFlashdata('errors')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= $item ? base_url('admin/roles/update/' . $item['id']) : base_url('admin/roles/store') ?>" method="post">
            
            <!-- Nama Role -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Role *</label>
                <input type="text" name="role_name" value="<?= $item['role_name'] ?? '' ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                <textarea name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"><?= $item['description'] ?? '' ?></textarea>
            </div>

            <!-- Permissions -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Hak Akses (Permissions)</label>
                <div class="grid grid-cols-2 gap-4">
                    <?php 
                    $availablePermissions = [
                        'manage_users' => 'Manajemen User',
                        'manage_roles' => 'Manajemen Role',
                        'manage_offices' => 'Manajemen Kantor',
                        'manage_types' => 'Manajemen Tipe Kantor',
                        'manage_areas' => 'Manajemen Area (Polygon)',
                        'view_all_reports' => 'Lihat Semua Laporan (Global)',
                    ];
                    $currentPermissions = $item['permissions'] ?? [];
                    ?>
                    <?php foreach($availablePermissions as $key => $label): ?>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="permissions[]" value="<?= $key ?>" class="form-checkbox h-5 w-5 text-blue-600" 
                            <?= in_array($key, $currentPermissions) ? 'checked' : '' ?>>
                        <span class="ml-2 text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex items-center justify-end border-t pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
