<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ---- Ambil data statistik dari database ----
$total_revenue = $pdo->query("SELECT SUM(total_harga) FROM pesanan WHERE status = 'selesai'")->fetchColumn() ?? 0;
$total_produk = $pdo->query("SELECT COUNT(id) FROM produk")->fetchColumn() ?? 0;
$total_customers = $pdo->query("SELECT COUNT(id) FROM customers")->fetchColumn() ?? 0;
$pesanan_baru = $pdo->query("SELECT COUNT(id) FROM pesanan WHERE status = 'pending'")->fetchColumn() ?? 0;

// ---- Ambil data untuk Chart (6 bulan terakhir) ----
$chart_labels = [];
$chart_data = [];
$stmt_chart = $pdo->query("
    SELECT DATE_FORMAT(tanggal_pesanan, '%b %Y') as bulan, SUM(total_harga) as total 
    FROM pesanan 
    WHERE status = 'selesai' AND tanggal_pesanan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
    GROUP BY bulan 
    ORDER BY MIN(tanggal_pesanan) ASC
");
while ($row = $stmt_chart->fetch()) {
    $chart_labels[] = $row['bulan'];
    $chart_data[] = $row['total'];
}

// ===== KODE BARU: Ambil 5 Pesanan Terbaru =====
$stmt_recent_orders = $pdo->query("
    SELECT p.*, c.nama as nama_customer
    FROM pesanan p
    JOIN customers c ON p.customer_id = c.id
    ORDER BY p.tanggal_pesanan DESC
    LIMIT 5
");
$recent_orders = $stmt_recent_orders->fetchAll();

// ===== KODE BARU: Ambil 5 Produk Stok Menipis =====
$stok_kritis = 10;
$stmt_low_stock = $pdo->prepare("
    SELECT id, nama_produk, stok, gambar
    FROM produk
    WHERE stok <= ?
    ORDER BY stok ASC
    LIMIT 5
");
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
        <div><p class="text-sm font-light uppercase tracking-wider">Total Revenue</p><p class="text-2xl font-bold"><?= format_rupiah($total_revenue) ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-shoe-prints text-2xl text-white"></i></div>
        <div><p class="text-sm font-light uppercase tracking-wider">Total Produk</p><p class="text-2xl font-bold"><?= $total_produk ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-sky-500 to-sky-600 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-users text-2xl text-white"></i></div>
        <div><p class="text-sm font-light uppercase tracking-wider">Total Customers</p><p class="text-2xl font-bold"><?= $total_customers ?></p></div>
    </div>
    <div class="bg-gradient-to-br from-amber-400 to-amber-500 text-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform transform hover:scale-105">
        <div class="bg-white/20 p-4 rounded-full"><i class="fas fa-inbox text-2xl text-white"></i></div>
        <div><p class="text-sm font-light uppercase tracking-wider">Pesanan Baru</p><p class="text-2xl font-bold"><?= $pesanan_baru ?></p></div>
    </div>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Grafik Pendapatan (6 Bulan Terakhir)</h2>
    <div style="height: 350px;"><canvas id="salesChart" class="w-full"></canvas></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Pesanan Terbaru</h3>
        <div class="space-y-4">
            <?php if (count($recent_orders) > 0): ?>
                <?php foreach($recent_orders as $order): ?>
                    <a href="/tokosepatu/modules/pesanan/detail_pesanan.php?id=<?= $order['id'] ?>" class="block hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($order['nama_customer']) ?></p>
                                <p class="text-sm text-gray-500">ID: #<?= $order['id'] ?> - <?= format_rupiah($order['total_harga']) ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                    switch ($order['status']) {
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
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Produk Stok Menipis (< <?= $stok_kritis ?>)</h3>
        <div class="space-y-4">
             <?php if (count($low_stock_products) > 0): ?>
                <?php foreach($low_stock_products as $product): ?>
                    <a href="/tokosepatu/modules/produk/edit.php?id=<?= $product['id'] ?>" class="block hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <img src="/tokosepatu/uploads/<?= htmlspecialchars($product['gambar']) ?>" class="w-12 h-12 object-cover rounded-md">
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

    // Membuat gradasi warna untuk area di bawah garis
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(249, 115, 22, 0.4)'); // Oranye di atas
    gradient.addColorStop(1, 'rgba(249, 115, 22, 0)');   // Transparan di bawah

    new Chart(ctx, {
        type: 'line', // <-- Mengubah tipe chart menjadi 'line'
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: gradient, // <-- Menggunakan warna gradasi
                borderColor: '#f97316',     // Warna garis oranye solid
                borderWidth: 3,
                fill: true, // <-- Mengisi area di bawah garis
                tension: 0.4, // <-- Membuat garis melengkung halus
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#f97316',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                } 
            },
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>