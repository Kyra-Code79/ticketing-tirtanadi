<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?> - Tirtanadi</title>
    <link rel="icon" href="<?= base_url('logo-tirtanadi-notext.webp') ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <?= $this->renderSection('head') ?>
</head>
<body class="bg-gray-100">
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-xl hidden md:flex flex-col z-10 md:static fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-200">
            <div class="p-6 flex items-center border-b justify-between">
                <div class="flex items-center">
                    <img src="<?= base_url('logo-tirtanadi-notext.webp') ?>" class="h-10 mr-3">
                    <span class="font-bold text-gray-800 text-lg">Admin Panel</span>
                </div>
                <!-- Close Button Mobile -->
                <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-2">
                    <!-- Teknisi Menu -->
                    <?php if(session()->get('role') === 'Teknisi'): ?>
                    <li>
                        <a href="<?= base_url('teknisi/dashboard') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'teknisi/dashboard') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-home w-6"></i> Dashboard Teknisi
                        </a>
                    </li>
                    <li>
                        <!-- Reuse Dashboard or separate list? Using Dashboard as main internal list for now -->
                         <a href="<?= base_url('teknisi/dashboard') ?>#tugas-saya" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition">
                            <i class="fas fa-clipboard-list w-6"></i> Daftar Laporan
                        </a>
                    </li>
                    <?php else: ?>

                    <!-- Admin Menu (Super Admin & Admin Cabang) -->
                    <li>
                        <a href="<?= base_url('admin/dashboard') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (uri_string() == 'admin/dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-home w-6"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/laporan') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'admin/laporan') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-clipboard-list w-6"></i> Data Laporan
                        </a>
                    </li>
                    <?php endif; ?>
                    </li>
                    
                    </li>
                    
                    <!-- MASTER DATA (Permission-based) -->
                    <?php $perms = session()->get('permissions') ?? []; ?>
                    
                    <?php if(in_array('manage_areas', $perms)): ?>
                    <li>
                        <a href="<?= base_url('admin/polygons') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (uri_string() == 'admin/polygons') ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-map-marked-alt w-6"></i> Manajemen Area
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(in_array('manage_offices', $perms)): ?>
                    <li>
                        <a href="<?= base_url('admin/kantor') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'admin/kantor') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-building w-6"></i> Manajemen Kantor
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(in_array('manage_types', $perms)): ?>
                    <li>
                        <a href="<?= base_url('admin/types') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'admin/types') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-tags w-6"></i> Tipe / Label Kantor
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(in_array('manage_users', $perms)): ?>
                    <li>
                        <a href="<?= base_url('admin/users') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'admin/users') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-users w-6"></i> Manajemen User
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(in_array('manage_roles', $perms)): ?>
                    <li>
                        <a href="<?= base_url('admin/roles') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition <?= (strpos(uri_string(), 'admin/roles') !== false) ? 'bg-blue-50 text-blue-600 font-semibold' : '' ?>">
                            <i class="fas fa-user-shield w-6"></i> Manajemen Role
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- Add more menu items here -->
                </ul>
            </nav>

            <div class="p-4 border-t">
                <a href="<?= base_url('logout') ?>" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition w-full">
                    <i class="fas fa-sign-out-alt w-6"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            
            <!-- Mobile Header -->
            <header class="bg-white shadow-sm z-20 md:hidden flex items-center justify-between p-4">
                <span class="font-bold text-lg">Tirtanadi Admin</span>
                <button id="mobileMenuBtn" class="text-gray-600 focus:outline-none"><i class="fas fa-bars fa-lg"></i></button>
            </header>

            <!-- Content Body -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <?= $this->renderSection('content') ?>
            </main>
        </div>

    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        // Toggle Sidebar on Mobile
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.remove('hidden');
            setTimeout(() => { // Small delay to allow 'hidden' removal to register before transition
                 sidebar.classList.remove('-translate-x-full');
            }, 10);
        });

        // Close Sidebar
        closeSidebarBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            setTimeout(() => {
                sidebar.classList.add('hidden');
            }, 200); // Wait for transition
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
