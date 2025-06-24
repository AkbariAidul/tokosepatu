<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// Tentukan rentang tanggal. Defaultnya adalah bulan ini.
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');

// Ambil data ringkasan (KPI) berdasarkan rentang tanggal
$stmt_summary = $pdo->prepare("
    SELECT 
        SUM(total_harga) as total_pendapatan, 
        COUNT(id) as jumlah_pesanan 
    FROM pesanan 
    WHERE status = 'selesai' AND DATE(tanggal_pesanan) BETWEEN ? AND ?
");
$stmt_summary->execute([$tanggal_mulai, $tanggal_akhir]);
$summary = $stmt_summary->fetch();

// Ambil jumlah produk terjual
$stmt_produk = $pdo->prepare("
    SELECT SUM(dp.jumlah) as total_produk_terjual 
    FROM detail_pesanan dp
    JOIN pesanan p ON dp.pesanan_id = p.id
    WHERE p.status = 'selesai' AND DATE(p.tanggal_pesanan) BETWEEN ? AND ?
");
$stmt_produk->execute([$tanggal_mulai, $tanggal_akhir]);
$total_produk_terjual = $stmt_produk->fetchColumn() ?? 0;

// Ambil daftar pesanan dalam rentang tanggal
$stmt_orders = $pdo->prepare("
    SELECT p.*, c.nama as nama_customer 
    FROM pesanan p JOIN customers c ON p.customer_id = c.id 
    WHERE p.status = 'selesai' AND DATE(p.tanggal_pesanan) BETWEEN ? AND ?
    ORDER BY p.tanggal_pesanan DESC
");
$stmt_orders->execute([$tanggal_mulai, $tanggal_akhir]);
$orders = $stmt_orders->fetchAll();

// Ambil produk terlaris
$stmt_top_produk = $pdo->prepare("
    SELECT pr.nama_produk, pr.gambar, SUM(dp.jumlah) as total_terjual
    FROM detail_pesanan dp
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN pesanan p ON dp.pesanan_id = p.id
    WHERE p.status = 'selesai' AND DATE(p.tanggal_pesanan) BETWEEN ? AND ?
    GROUP BY pr.id, pr.nama_produk, pr.gambar
    ORDER BY total_terjual DESC
    LIMIT 5
");
$stmt_top_produk->execute([$tanggal_mulai, $tanggal_akhir]);
$top_produk = $stmt_top_produk->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Laporan Penjualan</h1>
    <p class="text-gray-500 mt-1">Analisis performa penjualan toko Anda.</p>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
    <form action="laporan.php" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
        <div>
            <label for="tanggal_mulai" class="text-sm font-medium text-gray-700">Dari Tanggal</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" class="mt-1 block w-full sm:w-auto rounded-lg border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="tanggal_akhir" class="text-sm font-medium text-gray-700">Sampai Tanggal</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" class="mt-1 block w-full sm:w-auto rounded-lg border-gray-300 shadow-sm">
        </div>
        <div class="flex items-center gap-3 mt-2 sm:mt-6">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">Terapkan Filter</button>
            <a href="export_excel.php?tanggal_mulai=<?= htmlspecialchars($tanggal_mulai) ?>&tanggal_akhir=<?= htmlspecialchars($tanggal_akhir) ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg shadow-sm flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-green-500 text-white p-6 rounded-xl shadow-lg"><p class="text-sm uppercase">Total Pendapatan</p><p class="text-3xl font-bold"><?= format_rupiah($summary['total_pendapatan'] ?? 0) ?></p></div>
    <div class="bg-sky-500 text-white p-6 rounded-xl shadow-lg"><p class="text-sm uppercase">Pesanan Selesai</p><p class="text-3xl font-bold"><?= $summary['jumlah_pesanan'] ?? 0 ?></p></div>
    <div class="bg-orange-500 text-white p-6 rounded-xl shadow-lg"><p class="text-sm uppercase">Produk Terjual</p><p class="text-3xl font-bold"><?= $total_produk_terjual ?></p></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg">
        <h3 class="text-lg font-semibold text-gray-800 p-4 border-b">Rincian Pesanan (<?= date('d M Y', strtotime($tanggal_mulai)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?>)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr><th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ID Pesanan</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Customer</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Total</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($orders as $order): ?>
                    <tr class="hover:bg-gray-50"><td class="px-6 py-4 text-sm text-gray-700">#<?= $order['id'] ?></td><td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($order['nama_customer']) ?></td><td class="px-6 py-4 text-sm font-semibold text-gray-900"><?= format_rupiah($order['total_harga']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="lg:col-span-1 bg-white rounded-2xl shadow-lg self-start">
        <h3 class="text-lg font-semibold text-gray-800 p-4 border-b">Top 5 Produk Terlaris</h3>
        <ul class="divide-y divide-gray-200">
    <?php foreach($top_produk as $produk): ?>
    <li class="p-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="/tokosepatu/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-12 h-12 object-cover rounded-md">
            <span class="text-sm text-gray-800 font-semibold"><?= htmlspecialchars($produk['nama_produk']) ?></span>
        </div>
        <span class="font-bold text-orange-500 bg-orange-100 px-3 py-1 rounded-full text-sm"><?= $produk['total_terjual'] ?>x</span>
    </li>
    <?php endforeach; ?>
</ul>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>