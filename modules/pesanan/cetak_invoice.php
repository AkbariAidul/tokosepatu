<?php
require_once '../../config/database.php';
require_once '../../helpers/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) die("Akses tidak sah.");

$order_id = $_GET['id'];

// Ambil data Pengaturan Toko
$stmt_settings = $pdo->query("SELECT * FROM pengaturan");
$pengaturan = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil data Pesanan
$stmt_order = $pdo->prepare("SELECT p.*, c.nama as nama_customer, c.alamat as alamat_customer, c.telepon as telepon_customer FROM pesanan p JOIN customers c ON p.customer_id = c.id WHERE p.id = ?");
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();

if (!$order) die("Pesanan tidak ditemukan.");

// Ambil Item Detail Pesanan
$stmt_items = $pdo->prepare("SELECT dp.*, pr.nama_produk, pr.gambar FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

// Kalkulasi Total
$subtotal = $order['total_harga'];
$biaya_pengiriman = $order['biaya_pengiriman'];
$diskon = $order['diskon'] ?? 0;
$grand_total = $subtotal + $biaya_pengiriman - $diskon;

// Fungsi helper untuk badge status
function get_invoice_status_badge($status) {
    switch (strtolower($status)) {
        case 'selesai': return 'bg-green-100 text-green-800';
        case 'diproses': return 'bg-blue-100 text-blue-800';
        case 'dikirim': return 'bg-indigo-100 text-indigo-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'dibatalkan': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order_id ?> - <?= htmlspecialchars($pengaturan['nama_toko'] ?? 'Rewalk-Store') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="icon" href="/tokosepatu/assets/images/shoes.png" type="image/png">
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        @media print { 
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        } 
    </style>
</head>
<body class="bg-slate-100 p-4 md:p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-lg">
        
        <header class="flex flex-col sm:flex-row justify-between items-start pb-8 border-b">
            <div class="flex items-center gap-4">
    <div class="flex-shrink-0 w-16 h-16 bg-orange-500 text-white flex items-center justify-center rounded-xl shadow-md">
        <i class="fas fa-shoe-prints text-4xl"></i>
    </div>
    <div>
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($pengaturan['nama_toko'] ?? 'Rewalk-Store') ?></h1>
        <p class="text-sm text-gray-500 max-w-xs"><?= nl2br(htmlspecialchars($pengaturan['alamat_toko'] ?? 'Alamat Toko Anda')) ?></p>
    </div>
</div>
            <div class="text-left sm:text-right mt-4 sm:mt-0">
                <h2 class="text-4xl font-extrabold text-orange-500 uppercase tracking-widest">Invoice</h2>
                <div class="mt-2 space-y-1 text-sm text-gray-600">
                    <p><span class="font-semibold">Invoice #:</span> <?= $order_id ?></p>
                    <p><span class="font-semibold">Tanggal:</span> <?= date('d F Y', strtotime($order['tanggal_pesanan'])) ?></p>
                    <div class="flex items-center justify-start sm:justify-end gap-2">
                        <span class="font-semibold">Status:</span>
                        <span class="px-3 py-1 font-semibold rounded-full text-xs <?= get_invoice_status_badge($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-8 my-8">
            <div>
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Ditagihkan Kepada:</h3>
                <div class="text-gray-700 space-y-1">
                    <p class="font-bold text-lg"><?= htmlspecialchars($order['nama_customer']) ?></p>
                    <p><?= nl2br(htmlspecialchars($order['alamat_customer'])) ?></p>
                    <p><?= htmlspecialchars($order['telepon_customer']) ?></p>
                </div>
            </div>
        </section>

        <section class="mb-8">
            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Produk</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Kuantitas</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Harga Satuan</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <img src="/tokosepatu/uploads/<?= htmlspecialchars($item['gambar']) ?>" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($item['nama_produk']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600"><?= $item['jumlah'] ?></td>
                    <td class="px-6 py-4 text-right text-gray-600"><?= format_rupiah($item['harga_saat_pesan']) ?></td>
                    <td class="px-6 py-4 text-right text-gray-600 font-semibold"><?= format_rupiah($item['harga_saat_pesan'] * $item['jumlah']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
            </div>
        </section>

        <section class="flex justify-end mb-8">
            <div class="w-full max-w-sm text-gray-700 space-y-2">
                <div class="flex justify-between"><p>Subtotal Produk</p><p class="font-semibold"><?= format_rupiah($subtotal) ?></p></div>
                <div class="flex justify-between"><p>Biaya Pengiriman</p><p class="font-semibold"><?= format_rupiah($biaya_pengiriman) ?></p></div>
                <?php if ($diskon > 0): ?>
                <div class="flex justify-between text-green-600"><p>Diskon</p><p class="font-semibold">- <?= format_rupiah($diskon) ?></p></div>
                <?php endif; ?>
                <div class="flex justify-between pt-2 border-t text-lg font-bold text-gray-900 bg-orange-100 p-3 rounded-lg">
                    <p>Grand Total</p>
                    <p><?= format_rupiah($grand_total) ?></p>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Informasi Pembayaran</h3>
            <div class="bg-slate-50 p-4 rounded-lg border">
                <p class="text-gray-700">Silakan lakukan pembayaran ke rekening berikut:</p>
                <p class="text-gray-900 font-semibold mt-1"><?= htmlspecialchars($pengaturan['nomor_rekening'] ?? 'Informasi rekening belum diatur.') ?></p>
            </div>
        </section>

        <footer class="text-center mt-12 pt-8 border-t">
             <p class="text-gray-500 text-sm mb-4">Terima kasih telah berbelanja di <?= htmlspecialchars($pengaturan['nama_toko'] ?? 'Rewalk-Store') ?>!</p>
             <div class="no-print">
                <button onclick="window.print()" class="bg-gray-800 hover:bg-orange-500 text-white font-bold py-2 px-6 rounded-lg shadow-lg flex items-center gap-2 mx-auto">
                    <i class="fas fa-print"></i> Cetak Invoice
                </button>
            </div>
        </footer>

    </div>
</body>
</html>