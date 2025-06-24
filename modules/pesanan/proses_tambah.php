<?php
// Menggunakan __DIR__ agar path lebih aman dan pasti
require_once __DIR__ . '/../../config/database.php';

// Pastikan session dimulai jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ... sisa kode (tidak perlu diubah) ...

$customer_id = $_POST['customer_id'] ?? null;
$status = $_POST['status'] ?? null;
$produk_ids = $_POST['produk_id'] ?? [];
$jumlahs = $_POST['jumlah'] ?? [];
// Data baru dari form
$kupon_id = !empty($_POST['kupon_id']) ? $_POST['kupon_id'] : null;
$diskon = (int)($_POST['diskon'] ?? 0);

if (empty($customer_id) || empty($status) || empty($produk_ids) || count($produk_ids) !== count($jumlahs)) {
    $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Data tidak lengkap. Customer dan minimal satu produk harus dipilih.'];
    header('Location: tambah.php');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt_setting = $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key = 'biaya_pengiriman'");
    $biaya_per_kg = $stmt_setting->fetchColumn() ?? 10000;

    $total_harga = 0;
    $total_berat = 0;
    $items_to_process = [];
    for ($i = 0; $i < count($produk_ids); $i++) {
        if (empty($produk_ids[$i]) || empty($jumlahs[$i]) || (int)$jumlahs[$i] <= 0) continue;
        $id = (int)$produk_ids[$i];
        $jml = (int)$jumlahs[$i];
        $stmt_produk = $pdo->prepare("SELECT nama_produk, harga, stok, berat FROM produk WHERE id = ?");
        $stmt_produk->execute([$id]);
        $produk = $stmt_produk->fetch();
        if (!$produk) throw new Exception("Produk dengan ID $id tidak ditemukan.");
        if ($produk['stok'] < $jml) throw new Exception("Stok untuk '{$produk['nama_produk']}' tidak cukup.");
        $total_harga += $produk['harga'] * $jml;
        $total_berat += $produk['berat'] * $jml;
        $items_to_process[] = ['id' => $id, 'jumlah' => $jml, 'harga_saat_pesan' => $produk['harga']];
    }
    
    $total_berat_kg = ceil($total_berat / 1000);
    if ($total_berat > 0 && $total_berat_kg == 0) $total_berat_kg = 1;
    $final_biaya_pengiriman = $total_berat_kg * $biaya_per_kg;

    // Masukkan data baru (kupon_id, diskon) ke query INSERT
    $stmt_pesanan = $pdo->prepare(
        "INSERT INTO pesanan (customer_id, tanggal_pesanan, total_harga, biaya_pengiriman, kupon_id, diskon, status) 
         VALUES (?, NOW(), ?, ?, ?, ?, ?)"
    );
    $stmt_pesanan->execute([$customer_id, $total_harga, $final_biaya_pengiriman, $kupon_id, $diskon, $status]);
    $pesanan_id = $pdo->lastInsertId();

    $stmt_detail = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_saat_pesan) VALUES (?, ?, ?, ?)");
    $stmt_update_stok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
    foreach ($items_to_process as $item) {
        $stmt_detail->execute([$pesanan_id, $item['id'], $item['jumlah'], $item['harga_saat_pesan']]);
        $stmt_update_stok->execute([$item['jumlah'], $item['id']]);
    }
    
    $pdo->commit();
    $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Pesanan baru berhasil dibuat dengan ID #' . $pesanan_id];
    header('Location: pesanan.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Operasi Gagal!', 'text' => $e->getMessage()];
    header('Location: tambah.php');
    exit();
}
?>