<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /tokosepatu/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt_select = $pdo->prepare("SELECT gambar FROM produk WHERE id = ?");
        $stmt_select->execute([$id]);
        $product = $stmt_select->fetch();
        $gambar_nama = $product['gambar'];

        $stmt_delete = $pdo->prepare("DELETE FROM produk WHERE id = ?");
        $stmt_delete->execute([$id]);

        if ($gambar_nama && file_exists("../../uploads/" . $gambar_nama)) {
            unlink("../../uploads/" . $gambar_nama);
        }
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Produk berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Gagal menghapus produk. Mungkin produk ini terhubung dengan data pesanan.'];
    }
    header('Location: produk.php');
    exit();
}
?>