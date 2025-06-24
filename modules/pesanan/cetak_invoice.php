<?php
require_once '../../config/database.php';
require_once '../../helpers/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) die("Akses tidak sah.");

$order_id = $_GET['id'];

// Ambil data Pengaturan Toko
$stmt_settings = $pdo->query("SELECT * FROM pengaturan");
$pengaturan = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil data Pesanan (termasuk biaya_pengiriman yang sudah tersimpan)
$stmt_order = $pdo->prepare("SELECT p.*, c.nama as nama_customer, c.alamat as alamat_customer, c.telepon as telepon_customer FROM pesanan p JOIN customers c ON p.customer_id = c.id WHERE p.id = ?");
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();

if (!$order) die("Pesanan tidak ditemukan.");

// Ambil Item Detail Pesanan
$stmt_items = $pdo->prepare("SELECT dp.*, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

// Kalkulasi Total yang Benar dari data yang sudah ada
$subtotal = $order['total_harga'];
$biaya_pengiriman = $order['biaya_pengiriman']; // <-- AMBIL DARI PESANAN, BUKAN PENGATURAN
$grand_total = $subtotal + $biaya_pengiriman;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } @media print { .no-print { display: none; } } </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-lg shadow-lg">
        
        <div class="flex justify-between items-start mb-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($pengaturan['nama_toko'] ?? 'Rewalk-Store') ?></h1>
                <p class="text-sm text-gray-500"><?= nl2br(htmlspecialchars($pengaturan['alamat_toko'] ?? 'Alamat Toko Anda')) ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-gray-400 uppercase">Invoice</h2>
                <p class="text-sm text-gray-600">#<?= $order_id ?></p>
                <p class="text-sm text-gray-600">Tanggal: <?= date('d M Y', strtotime($order['tanggal_pesanan'])) ?></p>
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Ditagihkan Kepada:</h3>
            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($order['nama_customer']) ?></p>
            <p class="text-gray-600"><?= nl2br(htmlspecialchars($order['alamat_customer'])) ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($order['telepon_customer']) ?></p>
        </div>

        <div>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Produk</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Kuantitas</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td class="px-6 py-4 text-center text-gray-600"><?= $item['jumlah'] ?></td>
                        <td class="px-6 py-4 text-right text-gray-600"><?= format_rupiah($item['harga_saat_pesan']) ?></td>
                        <td class="px-6 py-4 text-right text-gray-600 font-semibold"><?= format_rupiah($item['harga_saat_pesan'] * $item['jumlah']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            <div class="w-full max-w-sm text-gray-700">
                <div class="flex justify-between py-2 border-b">
                    <span>Subtotal Produk</span>
                    <span><?= format_rupiah($subtotal) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span>Biaya Pengiriman</span>
                    <span><?= format_rupiah($biaya_pengiriman) ?></span>
                </div>
                <div class="flex justify-between py-2 text-black font-bold text-lg bg-gray-100 px-4 rounded-md">
                    <span>Grand Total</span>
                    <span><?= format_rupiah($grand_total) ?></span>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Informasi Pembayaran</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-700">Silakan lakukan pembayaran ke rekening berikut:</p>
                <p class="text-gray-900 font-semibold mt-1"><?= htmlspecialchars($pengaturan['nomor_rekening'] ?? 'Informasi rekening belum diatur.') ?></p>
            </div>
        </div>

        <div class="text-center mt-12 no-print">
            <button onclick="window.print()" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg shadow-lg">Cetak Halaman Ini</button>
        </div>

    </div>
</body>
</html>