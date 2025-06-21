<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Akses tidak sah atau data tidak valid.'];

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['product_id']) && isset($_POST['stok'])) {
        $product_id = $_POST['product_id'];
        $stok = $_POST['stok'];

        // Validasi sederhana
        if (!is_numeric($stok) || $stok < 0) {
            $response['message'] = 'Jumlah stok tidak valid.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE produk SET stok = ? WHERE id = ?");
                $stmt->execute([$stok, $product_id]);
                
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Stok berhasil diperbarui.'];
                } else {
                    $response['message'] = 'Tidak ada perubahan pada stok.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Gagal memperbarui database.';
            }
        }
    }
}

echo json_encode($response);
exit();
?>