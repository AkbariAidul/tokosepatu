<?php
// Menggunakan __DIR__ untuk path yang pasti benar
// Ini akan naik 2 tingkat folder dari lokasi file saat ini untuk menemukan folder root
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Gunakan class-class dari PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// ... sisa kode di bawahnya tidak perlu diubah ...

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Ambil rentang tanggal dari URL
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');

// Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- STYLING ---
// Style untuk header tabel
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
];
// Atur judul laporan
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'Laporan Penjualan Toko Sepatu');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->setCellValue('A2', 'Periode: ' . date('d M Y', strtotime($tanggal_mulai)) . ' - ' . date('d M Y', strtotime($tanggal_akhir)));
$sheet->mergeCells('A2:E2');

// Tulis header tabel di baris ke-4
$sheet->setCellValue('A4', 'ID Pesanan');
$sheet->setCellValue('B4', 'Tanggal Pesanan');
$sheet->setCellValue('C4', 'Nama Customer');
$sheet->setCellValue('D4', 'Total Harga');
$sheet->setCellValue('E4', 'Status');
$sheet->getStyle('A4:E4')->applyFromArray($headerStyle);


// --- AMBIL DATA DARI DATABASE ---
$stmt = $pdo->prepare("
    SELECT p.id, p.tanggal_pesanan, c.nama as nama_customer, (p.total_harga + p.biaya_pengiriman - p.diskon) as grand_total, p.status 
    FROM pesanan p 
    JOIN customers c ON p.customer_id = c.id 
    WHERE p.status = 'selesai' AND DATE(p.tanggal_pesanan) BETWEEN ? AND ?
    ORDER BY p.tanggal_pesanan DESC
");
$stmt->execute([$tanggal_mulai, $tanggal_akhir]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- TULIS DATA KE SPREADSHEET ---
$row = 5; // Mulai tulis data dari baris ke-5
foreach ($orders as $order) {
    $sheet->setCellValue('A' . $row, '#' . $order['id']);
    $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($order['tanggal_pesanan'])));
    $sheet->setCellValue('C' . $row, $order['nama_customer']);
    $sheet->setCellValue('D' . $row, $order['grand_total']);
    $sheet->setCellValue('E' . $row, $order['status']);

    // Formatting kolom harga sebagai Rupiah
    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
    
    $row++;
}

// Atur lebar kolom agar otomatis menyesuaikan
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);
$sheet->getColumnDimension('C')->setAutoSize(true);
$sheet->getColumnDimension('D')->setAutoSize(true);
$sheet->getColumnDimension('E')->setAutoSize(true);


// --- OUTPUT KE BROWSER ---
// Siapkan nama file .xlsx
$filename = "Laporan Penjualan - " . $tanggal_mulai . " sampai " . $tanggal_akhir . ".xlsx";

// Set header HTTP untuk format .xlsx
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Buat writer dan simpan output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>