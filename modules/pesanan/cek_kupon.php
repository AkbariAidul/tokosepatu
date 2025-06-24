<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Akses tidak sah.'];

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_kupon = trim(strtoupper($_POST['kode_kupon']));
    $subtotal = (int)($_POST['subtotal'] ?? 0);

    if (empty($kode_kupon)) {
        $response['message'] = 'Kode kupon tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM kupon WHERE kode_kupon = ?");
    $stmt->execute([$kode_kupon]);
    $kupon = $stmt->fetch();

    if (!$kupon) {
        $response['message'] = 'Kode kupon tidak ditemukan.';
    } elseif ($kupon['status'] != 'aktif') {
        $response['message'] = 'Kupon ini sudah tidak aktif.';
    } elseif (strtotime($kupon['tanggal_kadaluarsa']) < time()) {
        $response['message'] = 'Kupon ini sudah kadaluarsa.';
    } else {
        // Kupon valid, hitung diskon
        $diskon = 0;
        if ($kupon['jenis_kupon'] == 'persen') {
            $diskon = ($kupon['nilai'] / 100) * $subtotal;
        } else { // Jenis 'tetap'
            $diskon = $kupon['nilai'];
        }
        
        $response = [
            'success' => true,
            'message' => 'Kupon berhasil diterapkan!',
            'kupon_id' => $kupon['id'],
            'diskon' => (int)$diskon,
            'diskon_formatted' => format_rupiah((int)$diskon)
        ];
    }
}

// Memanggil fungsi format_rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

echo json_encode($response);
exit();
?>