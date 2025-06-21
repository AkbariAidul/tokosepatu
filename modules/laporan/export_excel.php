<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Ambil rentang tanggal dari URL
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');

// Siapkan nama file
$filename = "Laporan Penjualan - " . $tanggal_mulai . " sampai " . $tanggal_akhir . ".xls";

// Ambil data pesanan dari database sesuai rentang tanggal
$stmt = $pdo->prepare("
    SELECT p.id, p.tanggal_pesanan, c.nama as nama_customer, p.total_harga, p.status 
    FROM pesanan p 
    JOIN customers c ON p.customer_id = c.id 
    WHERE p.status = 'selesai' AND DATE(p.tanggal_pesanan) BETWEEN ? AND ?
    ORDER BY p.tanggal_pesanan DESC
");
$stmt->execute([$tanggal_mulai, $tanggal_akhir]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set header HTTP untuk memicu unduhan file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Buat pointer file yang terhubung ke output PHP
$output = fopen("php://output", "w");

// Tulis baris header ke file CSV/Excel
fputcsv($output, array('ID Pesanan', 'Tanggal Pesanan', 'Nama Customer', 'Total Harga', 'Status'), "\t");

// Tulis setiap baris data pesanan
foreach ($orders as $order) {
    // Format total harga tanpa "Rp" agar menjadi angka di Excel
    $order['total_harga'] = number_format($order['total_harga'], 0, ',', '');
    fputcsv($output, $order, "\t");
}

fclose($output);
exit();