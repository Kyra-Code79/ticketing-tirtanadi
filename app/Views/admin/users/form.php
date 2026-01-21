<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto max-w-lg">
    <div class="mb-6">
        <a href="<?= base_url('admin/users') ?>" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
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
        <form action="<?= $item ? base_url('admin/users/update/' . $item['id']) : base_url('admin/users/store') ?>" method="post">
            
            <!-- Nama Lengkap -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" value="<?= $item['nama_lengkap'] ?? '' ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username *</label>
                <input type="text" name="username" value="<?= $item['username'] ?? '' ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password <?= $item ? '(Kosongkan jika tidak ubah)' : '*' ?></label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" <?= $item ? '' : 'required' ?>>
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Role *</label>
                <select name="role_id" id="roleSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                    <option value="">-- Pilih Role --</option>
                    <?php foreach($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" data-name="<?= $role['role_name'] ?>" <?= ($item && $item['role_id'] == $role['id']) ? 'selected' : '' ?>>
                            <?= $role['role_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kantor (Dynamic Show/Hide via JS) -->
            <div class="mb-6" id="kantorContainer">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Kantor *</label>
                <select name="id_kantor" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">-- Pilih Kantor --</option>
                    <?php foreach($offices as $office): ?>
                        <option value="<?= $office['id'] ?>" <?= ($item && $item['id_kantor'] == $office['id']) ? 'selected' : '' ?>>
                            <?= $office['nama_kantor'] ?> (<?= $office['tipe'] ?? 'NA' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Wajib diisi jika role bukan Super Admin.</p>
            </div>

            <div class="flex items-center justify-end border-t pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const kantorContainer = document.getElementById('kantorContainer');
    
    function toggleKantor() {
        // Get selected option text
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.getAttribute('data-name');
        
        if(roleName === 'Super Admin') {
            kantorContainer.style.display = 'none';
        } else {
            kantorContainer.style.display = 'block';
        }
    }

    roleSelect.addEventListener('change', toggleKantor);
    toggleKantor(); // Run on init
</script>
<?= $this->endSection() ?>
