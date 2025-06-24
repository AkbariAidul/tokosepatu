<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

$range = $_GET['range'] ?? '180'; // Default 180 hari (6 bulan)
$interval_sql = "INTERVAL " . intval($range) . " DAY";

// Sesuaikan format label berdasarkan rentang
$date_format = '%d %b'; // Format untuk 7 & 30 hari (Contoh: 24 Jun)
if ($range > 30) {
    $date_format = '%b %Y'; // Format untuk 6 bulan (Contoh: Jun 2025)
}

$stmt_chart = $pdo->prepare("
    SELECT DATE_FORMAT(tanggal_pesanan, ?) as label, SUM(total_harga + biaya_pengiriman - diskon) as total 
    FROM pesanan 
    WHERE status = 'selesai' AND tanggal_pesanan >= DATE_SUB(CURDATE(), $interval_sql) 
    GROUP BY label
    ORDER BY MIN(tanggal_pesanan) ASC
");
$stmt_chart->execute([$date_format]);

$chart_labels = [];
$chart_data = [];
while ($row = $stmt_chart->fetch()) {
    $chart_labels[] = $row['label'];
    $chart_data[] = (int)$row['total'];
}

echo json_encode(['labels' => $chart_labels, 'data' => $chart_data]);
exit();
?>