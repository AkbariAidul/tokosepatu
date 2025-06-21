<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Akses tidak sah.'];

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $allowed_statuses = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

    if (!in_array($status, $allowed_statuses)) {
        $response['message'] = 'Status tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            $response = ['success' => true, 'message' => 'Status pesanan berhasil diubah.'];
        } catch (PDOException $e) {
            $response['message'] = 'Gagal memperbarui status di database.';
        }
    }
}

echo json_encode($response);
exit();
?>