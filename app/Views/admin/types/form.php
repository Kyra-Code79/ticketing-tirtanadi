<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto max-w-lg">
    <div class="mb-6">
        <a href="<?= base_url('admin/types') ?>" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
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
        <form action="<?= $item ? base_url('admin/types/update/' . $item['id']) : base_url('admin/types/store') ?>" method="post">
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Tipe / Label</label>
                <input type="text" name="nama_tipe" value="<?= $item['nama_tipe'] ?? '' ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: Cabang Pembantu" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Warna Identitas</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="warna" id="colorPicker" value="<?= $item['warna'] ?? '#000000' ?>" class="h-10 w-20 p-1 border rounded cursor-pointer">
                    <input type="text" id="hexText" value="<?= $item['warna'] ?? '#000000' ?>" class="shadow appearance-none border rounded w-28 py-2 px-3 text-gray-700 leading-tight uppercase font-mono" maxlength="7" placeholder="#000000">
                    <span class="text-gray-500 text-sm">Pilih warna untuk badge.</span>
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

<script>
    const picker = document.getElementById('colorPicker');
    const hex = document.getElementById('hexText');
    
    // Sync Picker -> Text
    picker.addEventListener('input', (e) => {
        hex.value = e.target.value.toUpperCase();
    });

    // Sync Text -> Picker
    hex.addEventListener('input', (e) => {
        let val = e.target.value;
        if(val.length >= 6 && val.charAt(0) !== '#') {
            val = '#' + val;
        }
        
        // Simple regex for hex color
        if(/^#[0-9A-F]{6}$/i.test(val)) {
            picker.value = val;
            e.target.value = val.toUpperCase(); // Normalize
        }
    });

    // Auto-prepend # on blur if missing
    hex.addEventListener('blur', (e) => {
        let val = e.target.value;
        if(val && val.charAt(0) !== '#') {
            e.target.value = '#' + val;
        }
    });
</script>
<?= $this->endSection() ?>
