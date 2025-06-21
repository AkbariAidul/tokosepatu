<aside class="w-64 flex-shrink-0 bg-gray-800 text-white flex flex-col">
    <div class="h-16 flex items-center justify-center px-4 border-b border-gray-700/50">
        <h1 class="text-xl font-bold leading-none flex items-center gap-3">
            <i class="fas fa-shoe-prints text-2xl text-orange-500"></i>
            <span>Admin Rewalk</span>
        </h1>
    </div>

    <nav id="sidebar-nav" class="flex-1 px-2 py-4 overflow-y-auto space-y-1">
        <a href="/tokosepatu/index.php" class="flex items-center px-3 py-2 rounded-lg transition-all duration-200 <?= is_active('index.php'); ?>">
            <i class="fas fa-tachometer-alt fa-fw w-6 text-center mr-3"></i> Dashboard
        </a>

        <div>
            <button class="accordion-toggle w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200">
                <span class="flex items-center">
                    <i class="fas fa-store fa-fw w-6 text-center mr-3"></i> Manajemen Toko
                </span>
                <i class="fas fa-chevron-down transform transition-transform duration-200"></i>
            </button>
            <div class="submenu hidden mt-1 pl-7 space-y-1">
                <a href="/tokosepatu/modules/kategori/kategori.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active('kategori.php'); ?>">Kategori</a>
                <a href="/tokosepatu/modules/produk/produk.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active(['produk.php', 'tambah.php', 'edit.php']); ?>">Produk</a>
                <a href="/tokosepatu/modules/stok/stok.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active('stok.php'); ?>">Manajemen Stok</a>
                <a href="/tokosepatu/modules/pesanan/pesanan.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active(['pesanan.php', 'detail_pesanan.php']); ?>">Pesanan</a>
                <a href="/tokosepatu/modules/customers/customers.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active('customers.php'); ?>">Customers</a>
            </div>
        </div>
        
        <div>
            <button class="accordion-toggle w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200">
                <span class="flex items-center">
                    <i class="fas fa-bullhorn fa-fw w-6 text-center mr-3"></i> Marketing & CS
                </span>
                <i class="fas fa-chevron-down transform transition-transform duration-200"></i>
            </button>
            <div class="submenu hidden mt-1 pl-7 space-y-1">
                <a href="/tokosepatu/modules/kupon/kupon.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active('kupon.php'); ?>">Kupon Diskon</a>
                <a href="/tokosepatu/modules/banner/banner.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active(['banner.php', 'edit_banner.php']); ?>">Banner</a>
                <a href="/tokosepatu/modules/pengaduan/pengaduan.php" class="flex items-center px-3 py-2 rounded-lg text-sm <?= is_active(['pengaduan.php', 'detail_pengaduan.php']); ?>">Pengaduan CS</a>
            </div>
        </div>

        <a href="/tokosepatu/modules/laporan/laporan.php" class="flex items-center px-3 py-2 rounded-lg transition-all duration-200 <?= is_active('laporan.php'); ?>">
            <i class="fas fa-chart-line fa-fw w-6 text-center mr-3"></i> Laporan
        </a>
    </nav>
    
    <div class="px-2 py-3 border-t border-gray-700/50">
        <a href="/tokosepatu/modules/profil/profil.php" class="flex items-center w-full mb-1 px-3 py-2 rounded-lg transition-all duration-200 <?= is_active('profil.php'); ?>">
            <i class="fas fa-user-circle fa-fw w-6 text-center mr-3"></i> Profil Saya
        </a>
        <a href="/tokosepatu/modules/pengaturan/pengaturan.php" class="flex items-center w-full mb-1 px-3 py-2 rounded-lg transition-all duration-200 <?= is_active('pengaturan.php'); ?>">
            <i class="fas fa-cogs fa-fw w-6 text-center mr-3"></i> Pengaturan
        </a>
         <a href="/tokosepatu/logout.php" class="flex items-center w-full px-3 py-2 rounded-lg transition-all duration-200 text-red-400 hover:bg-red-500 hover:text-white">
            <i class="fas fa-sign-out-alt fa-fw w-6 text-center mr-3"></i> Logout
        </a>
    </div>
</aside>

<main class="flex-1 overflow-y-auto p-6 md:p-8 lg:p-10">