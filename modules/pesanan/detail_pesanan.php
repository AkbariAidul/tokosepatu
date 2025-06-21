<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header('Location: /tokosepatu/login.php'); exit(); }

$order_id = $_GET['id'];

$stmt_order = $pdo->prepare("SELECT p.*, c.nama as nama_customer, c.email, c.telepon, c.alamat FROM pesanan p JOIN customers c ON p.customer_id = c.id WHERE p.id = ?");
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();
if (!$order) { header('Location: pesanan.php'); exit(); }

$stmt_items = $pdo->prepare("SELECT dp.*, pr.nama_produk, pr.gambar FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Detail Pesanan #<?= $order_id ?></h1>
    </div>
    <div class="flex items-center gap-3">
        <a href="pesanan.php" class="text-orange-500 hover:underline font-semibold">&larr; Kembali ke Daftar Pesanan</a>
        <a href="cetak_invoice.php?id=<?= $order['id'] ?>" target="_blank" class="bg-gray-700 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg shadow-sm flex items-center">
            <i class="fas fa-print mr-2"></i> Cetak Invois
        </a>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-xl font-semibold border-b pb-3 mb-4 text-gray-800">Item Pesanan</h2>
        <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between py-4 border-b last:border-b-0">
                <div class="flex items-center">
                    <img src="/tokosepatu/uploads/<?= htmlspecialchars($item['gambar']) ?>" class="w-16 h-16 object-cover rounded-lg mr-4">
                    <div>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_produk']) ?></p>
                        <p class="text-sm text-gray-600"><?= $item['jumlah'] ?> x <?= format_rupiah($item['harga_saat_pesan']) ?></p>
                    </div>
                </div>
                <p class="font-semibold text-gray-800"><?= format_rupiah($item['jumlah'] * $item['harga_saat_pesan']) ?></p>
            </div>
        <?php endforeach; ?>
        <div class="flex justify-end mt-4 pt-4 border-t">
            <div class="text-right">
                <p class="text-gray-600">Total Harga Pesanan:</p>
                <p class="text-2xl font-bold text-gray-900"><?= format_rupiah($order['total_harga']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg self-start">
        <h2 class="text-xl font-semibold border-b pb-3 mb-4 text-gray-800">Informasi Customer</h2>
        <div class="space-y-4">
            <div><p class="text-sm text-gray-500">Nama</p><p class="font-medium text-gray-800"><?= htmlspecialchars($order['nama_customer']) ?></p></div>
            <div><p class="text-sm text-gray-500">Email</p><p class="font-medium text-gray-800"><?= htmlspecialchars($order['email']) ?></p></div>
            <div><p class="text-sm text-gray-500">Telepon</p><p class="font-medium text-gray-800"><?= htmlspecialchars($order['telepon']) ?></p></div>
            <div><p class="text-sm text-gray-500">Alamat Pengiriman</p><p class="font-medium text-gray-800"><?= nl2br(htmlspecialchars($order['alamat'])) ?></p></div>
            <div><p class="text-sm text-gray-500">Status Pesanan</p><p class="font-bold text-orange-500 uppercase"><?= htmlspecialchars($order['status']) ?></p></div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>