<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die('Akses ditolak.');
}

$order_id = $_GET['id'];

// Ambil data pengaturan toko
$stmt_settings = $pdo->query("SELECT * FROM pengaturan");
$settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil data pesanan dan customer
$stmt_order = $pdo->prepare("SELECT p.*, c.nama as nama_customer, c.alamat, c.telepon FROM pesanan p JOIN customers c ON p.customer_id = c.id WHERE p.id = ?");
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();

// Ambil detail item pesanan
$stmt_items = $pdo->prepare("SELECT dp.*, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto my-10 bg-white p-8 shadow-md">
        <div class="flex justify-between items-start border-b pb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($settings['nama_toko'] ?? 'Toko Anda') ?></h1>
                <p class="text-sm text-gray-500"><?= nl2br(htmlspecialchars($settings['alamat_toko'] ?? 'Alamat Toko')) ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold uppercase text-gray-700">Invoice</h2>
                <p class="text-sm text-gray-500">#<?= $order['id'] ?></p>
                <p class="text-sm text-gray-500">Tanggal: <?= date('d M Y', strtotime($order['tanggal_pesanan'])) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-8">
            <div>
                <h3 class="font-semibold text-gray-700">Ditagihkan Kepada:</h3>
                <p class="text-gray-600"><?= htmlspecialchars($order['nama_customer']) ?></p>
                <p class="text-gray-600"><?= nl2br(htmlspecialchars($order['alamat'])) ?></p>
                <p class="text-gray-600"><?= htmlspecialchars($order['telepon']) ?></p>
            </div>
        </div>
        
        <div class="mt-8">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Produk</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold text-gray-600">Kuantitas</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-600">Harga Satuan</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-600">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): 
                        $item_subtotal = $item['harga_saat_pesan'] * $item['jumlah'];
                        $subtotal += $item_subtotal;
                    ?>
                    <tr class="border-b">
                        <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td class="px-4 py-3 text-center text-gray-700"><?= $item['jumlah'] ?></td>
                        <td class="px-4 py-3 text-right text-gray-700"><?= format_rupiah($item['harga_saat_pesan']) ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 font-semibold"><?= format_rupiah($item_subtotal) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-8">
            <div class="w-full md:w-1/3">
                <div class="flex justify-between text-gray-700"><p>Subtotal</p><p><?= format_rupiah($subtotal) ?></p></div>
                <div class="flex justify-between text-gray-700"><p>Pengiriman</p><p><?= format_rupiah($settings['biaya_pengiriman'] ?? 0) ?></p></div>
                <div class="flex justify-between text-gray-900 font-bold mt-2 pt-2 border-t-2">
                    <p>Grand Total</p><p><?= format_rupiah($order['total_harga']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="mt-12 border-t pt-6">
            <h3 class="font-semibold text-gray-700">Informasi Pembayaran</h3>
            <p class="text-sm text-gray-600">Silakan lakukan pembayaran ke rekening berikut:</p>
            <p class="text-sm text-gray-800 font-semibold mt-1"><?= htmlspecialchars($settings['nomor_rekening'] ?? 'Informasi rekening belum diatur') ?></p>
        </div>

        <div class="text-center mt-12">
            <p class="text-gray-500">Terima kasih telah berbelanja!</p>
        </div>
    </div>

    <div class="text-center my-6 no-print">
        <button onclick="window.print()" class="bg-orange-500 text-white py-2 px-6 rounded hover:bg-orange-600">
            <i class="fas fa-print mr-2"></i>Cetak Halaman Ini
        </button>
    </div>
</body>
</html>