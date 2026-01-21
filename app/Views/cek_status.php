<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pengaduan - Tirtanadi</title>
    <link rel="icon" href="<?= base_url('logo-tirtanadi-notext.webp') ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { brand: { blue: '#0056b3', green: '#28a745' } } }
            }
        }
    </script>
    <style>body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }</style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="<?= base_url() ?>" class="flex items-center gap-2 group">
                <i class="fas fa-arrow-left text-gray-500 group-hover:text-blue-600 transition"></i>
                <span class="font-semibold text-gray-700 group-hover:text-blue-600 transition">Kembali ke Beranda</span>
            </a>
            <img src="<?= base_url('logo-tirtanadi-notext.webp') ?>" class="h-8">
        </div>
    </header>

    <main class="flex-grow p-4 md:p-8">
        <div class="max-w-4xl mx-auto">
            
            <div class="text-center mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Lacak Status Pengaduan</h1>
                <p class="text-gray-500">Masukkan Nomor Tiket Anda untuk melihat progress penanganan.</p>
            </div>

            <!-- Search Box -->
            <div class="bg-white rounded-xl shadow-card p-6 mb-8 max-w-xl mx-auto">
                <form action="" method="get" class="relative">
                    <input type="text" name="keyword" value="<?= esc($keyword) ?>" 
                           placeholder="Contoh: TRT-20231025-1234" 
                           class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-lg font-mono uppercase transition shadow-sm"
                           required autofocus>
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xl"></i>
                    </div>
                    <button type="submit" class="absolute right-2 top-2 bottom-2 bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-md font-bold transition">
                        Cek
                    </button>
                </form>
            </div>

            <?php if($keyword && !$result): ?>
                <div class="bg-red-50 border border-red-100 rounded-xl p-8 text-center max-w-xl mx-auto">
                    <div class="bg-red-100 text-red-500 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fas fa-times"></i>
                    </div>
                    <h3 class="font-bold text-red-800 text-lg mb-1">Tiket Tidak Ditemukan</h3>
                    <p class="text-red-600">Nomor tiket "<?= esc($keyword) ?>" tidak terdaftar di sistem kami. Mohon cek kembali.</p>
                </div>
            <?php elseif($result): ?>

                <!-- Tracking UI -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Stepper Header -->
                    <div class="p-6 md:p-10 border-b border-gray-100 bg-gray-50">
                        <!-- Progress Bar Logic -->
                        <?php
                            $steps = ['Menunggu', 'Terverifikasi', 'Sedang Dikerjakan', 'Selesai'];
                            $currentStatus = $result['status'];
                            // Map status to index
                            $statusIndex = 0;
                            if ($currentStatus == 'Terverifikasi') $statusIndex = 1;
                            if ($currentStatus == 'Proses' || $currentStatus == 'Sedang Dikerjakan') $statusIndex = 2;
                            if ($currentStatus == 'Selesai') $statusIndex = 3;
                            if ($currentStatus == 'Ditolak') $statusIndex = -1; // Special case
                        ?>

                        <?php if($statusIndex == -1): ?>
                             <!-- Rejected State -->
                             <div class="text-center py-6">
                                <div class="inline-block bg-red-100 text-red-600 p-4 rounded-full mb-4 text-2xl"><i class="fas fa-ban"></i></div>
                                <h2 class="text-2xl font-bold text-red-700">Laporan Ditolak</h2>
                                <p class="text-gray-500 mt-2">Maaf, laporan Anda tidak dapat diproses saat ini.</p>
                             </div>
                        <?php else: ?>
                            <!-- Stepper UI -->
                            <div class="relative flex items-center justify-between w-full max-w-3xl mx-auto">
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10 rounded"></div>
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-green-500 -z-10 rounded transition-all duration-1000" style="width: <?= ($statusIndex / 3) * 100 ?>%"></div>

                                <?php foreach($steps as $idx => $stepLabel): ?>
                                    <?php 
                                        $isActive = $idx <= $statusIndex;
                                        $isCurrent = $idx == $statusIndex;
                                    ?>
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-4 text-sm font-bold transition-all duration-500 <?= $isActive ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-gray-200 text-gray-400' ?>">
                                            <?php if($isActive): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <?= $idx + 1 ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-3 text-xs md:text-sm font-semibold <?= $isActive ? 'text-green-600' : 'text-gray-400' ?>"><?= $stepLabel ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                        
                        <!-- Left: Info -->
                        <div class="p-6 md:p-8 col-span-1">
                            <h3 class="font-bold text-gray-900 mb-6">Informasi Tiket</h3>
                            
                            <div class="mb-5">
                                <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Nomor Tiket</span>
                                <div class="text-xl font-mono font-bold text-blue-600 mt-1"><?= $result['nomor_tiket'] ?></div>
                            </div>
                            
                            <div class="mb-5">
                                <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Nama Pelapor</span>
                                <div class="font-bold text-gray-700 mt-1"><?= esc($result['nama_pelapor']) ?></div>
                                <div class="text-sm text-gray-500"><?= esc($result['no_hp']) ?></div>
                            </div>

                            <div class="mb-5">
                                <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Lokasi</span>
                                <div class="font-medium text-gray-700 mt-1">
                                    <?= esc($result['nama_kecamatan']) ?>
                                </div>
                                <div class="text-sm text-gray-500 mt-1 line-clamp-3">
                                    <?= esc($result['alamat_detail']) ?>
                                </div>
                            </div>
                            
                            <?php if(!empty($result['nama_kantor'])): ?>
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                                <span class="text-xs text-blue-500 font-bold uppercase">Ditangani Oleh</span>
                                <div class="text-sm font-bold text-blue-800 mt-1"><?= $result['nama_kantor'] ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Right: Timeline -->
                        <div class="p-6 md:p-8 col-span-2 bg-white">
                            <h3 class="font-bold text="<?= ($statusIndex == 3) ? 'text-green-600' : 'text-blue-600' ?> mb-6">Riwayat Status</h3>

                            <div class="space-y-8 relative pl-4">
                                <!-- Vertical Line -->
                                <div class="absolute left-6 top-2 bottom-6 w-0.5 bg-gray-100"></div>

                                <?php foreach($history as $log): ?>
                                <div class="relative flex items-start group">
                                    <!-- Dot -->
                                    <div class="absolute left-4 -translate-x-1/2 mt-1.5 w-4 h-4 rounded-full border-2 border-white shadow-sm z-10 <?= isset($log['is_rejected']) ? 'bg-red-500' : 'bg-green-500' ?>"></div>
                                    
                                    <div class="ml-10 w-full">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-1">
                                            <span class="font-bold text-gray-800 text-lg group-hover:text-blue-600 transition">
                                                <?= $log['status'] ?>
                                            </span>
                                            <span class="text-sm text-gray-400 font-mono bg-gray-50 px-2 py-1 rounded">
                                                <?= $log['time'] ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            <?= $log['desc'] ?>
                                            <?php if(isset($log['is_rejected'])): ?>
                                                <span class="text-red-500 font-bold">[!]</span>
                                            <?php endif; ?>
                                        </p>
                                        <?php if($log['status'] == 'Selesai'): ?>
                                            <div class="mt-3 inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                                Case Closed
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-100 py-6 mt-12 text-center text-sm text-gray-400">
        &copy; <?= date('Y') ?> Perumda Tirtanadi. All rights reserved.
    </footer>

</body>
</html>
