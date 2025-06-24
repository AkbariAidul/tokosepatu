<?php
// File: index.php (Admin Dashboard Utama Anda)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Data untuk kartu statistik
$total_revenue = $pdo->query("SELECT SUM(total_harga + biaya_pengiriman - diskon) FROM pesanan WHERE status = 'selesai'")->fetchColumn() ?? 0;
$total_produk = $pdo->query("SELECT COUNT(id) FROM produk")->fetchColumn() ?? 0;
$total_customers = $pdo->query("SELECT COUNT(id) FROM customers")->fetchColumn() ?? 0;
$pesanan_baru = $pdo->query("SELECT COUNT(id) FROM pesanan WHERE status = 'pending'")->fetchColumn() ?? 0;

// Data untuk daftar pesanan terbaru
$stmt_recent_orders = $pdo->query("SELECT p.*, c.nama as nama_customer FROM pesanan p JOIN customers c ON p.customer_id = c.id ORDER BY p.tanggal_pesanan DESC LIMIT 5");
$recent_orders = $stmt_recent_orders->fetchAll();

// Data untuk stok menipis
$stok_kritis = 10;
$stmt_low_stock = $pdo->prepare("SELECT id, nama_produk, stok, gambar FROM produk WHERE stok <= ? ORDER BY stok ASC LIMIT 5");
$stmt_low_stock->execute([$stok_kritis]);
$low_stock_products = $stmt_low_stock->fetchAll();
?>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?>!</h1>
    <p class="text-gray-500 mt-1">Ini ringkasan aktivitas toko sepatumu.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-dollar-sign text-2xl text-white"></i></div>
        <div><p class="text-sm uppercase tracking-wider">Total Revenue</p><p class="text-2xl font-bold"><?= format_rupiah_singkat($total_revenue) ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-shoe-prints text-2xl text-white"></i></div>
        <div><p class="text-sm uppercase tracking-wider">Total Produk</p><p class="text-2xl font-bold"><?= $total_produk ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-sky-500 to-sky-600 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-users text-2xl text-white"></i></div>
        <div><p class="text-sm uppercase tracking-wider">Total Customers</p><p class="text-2xl font-bold"><?= $total_customers ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-amber-400 to-amber-500 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-inbox text-2xl text-white"></i></div>
        <div><p class="text-sm uppercase tracking-wider">Pesanan Baru</p><p class="text-2xl font-bold"><?= $pesanan_baru ?></p></div>
    </div>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Grafik Pendapatan</h2>
        <div id="chart-filters" class="flex space-x-1 bg-gray-200 p-1 rounded-lg mt-3 sm:mt-0">
            <button data-range="7" class="chart-filter-btn px-3 py-1 text-sm font-semibold text-gray-600 rounded-md">7 Hari</button>
            <button data-range="30" class="chart-filter-btn px-3 py-1 text-sm font-semibold text-gray-600 rounded-md">30 Hari</button>
            <button data-range="180" class="chart-filter-btn px-3 py-1 text-sm font-semibold text-white bg-orange-500 rounded-md">6 Bulan</button>
        </div>
    </div>
    <div style="height: 350px;"><canvas id="salesChart" class="w-full"></canvas></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Pesanan Terbaru</h3>
        <div class="space-y-4">
            <?php if (count($recent_orders) > 0): ?>
                <?php foreach($recent_orders as $order): ?>
                    <a href="modules/pesanan/detail_pesanan.php?id=<?= $order['id'] ?>" class="block hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($order['nama_customer']) ?></p>
                                <p class="text-sm text-gray-500">ID: #<?= $order['id'] ?> - <?= format_rupiah($order['total_harga']) ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                    switch (strtolower($order['status'])) {
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'diproses': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'selesai': echo 'bg-green-100 text-green-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8">Belum ada pesanan.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Produk Stok Menipis (&lt; <?= $stok_kritis ?>)</h3>
        <div class="space-y-4">
            <?php if (count($low_stock_products) > 0): ?>
                <?php foreach($low_stock_products as $product): ?>
                    <a href="modules/stok/stok.php?search=<?= urlencode($product['nama_produk']) ?>" class="block hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <img src="uploads/<?= htmlspecialchars($product['gambar']) ?>" class="w-12 h-12 object-cover rounded-md">
                                <div>
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($product['nama_produk']) ?></p>
                                    <p class="text-sm text-gray-500">Sisa stok:</p>
                                </div>
                            </div>
                            <span class="bg-red-100 text-red-800 text-lg font-bold w-12 h-12 flex items-center justify-center rounded-full">
                                <?= $product['stok'] ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8">Tidak ada produk dengan stok kritis.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    let myChart; // Variabel untuk menyimpan instance chart

    function renderChart(labels, data) {
        if (myChart) { myChart.destroy(); }
        const gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(249, 115, 22, 0.4)');
        gradient.addColorStop(1, 'rgba(249, 115, 22, 0)');
        myChart = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ label: 'Pendapatan', data: data, backgroundColor: gradient, borderColor: '#f97316', borderWidth: 3, fill: true, tension: 0.4, pointBackgroundColor: '#ffffff', pointBorderColor: '#f97316', pointRadius: 5, pointHoverRadius: 7 }] },
            options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); } } } }, responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y); } } } } }
        });
    }

    function fetchChartData(range) {
        $.ajax({
            url: 'modules/ajax/get_chart_data.php', // Path ini sudah benar
            type: 'GET',
            data: { range: range },
            dataType: 'json',
            success: function(response) {
                renderChart(response.labels, response.data);
            },
            error: function() { console.error("Gagal memuat data chart."); }
        });
    }

    $('.chart-filter-btn').on('click', function() {
        $('.chart-filter-btn').removeClass('bg-orange-500 text-white').addClass('text-gray-600');
        $(this).removeClass('text-gray-600').addClass('bg-orange-500 text-white');
        const range = $(this).data('range');
        fetchChartData(range);
    });

    // Muat data awal untuk 6 bulan
    fetchChartData(180);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>