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
        // Ambil nama file gambar untuk dihapus dari server
        $stmt_select = $pdo->prepare("SELECT nama_gambar FROM banner WHERE id = ?");
        $stmt_select->execute([$id]);
        $banner = $stmt_select->fetch();
        
        if ($banner) {
            $gambar_nama = $banner['nama_gambar'];

            // Hapus data dari database
            $stmt_delete = $pdo->prepare("DELETE FROM banner WHERE id = ?");
            $stmt_delete->execute([$id]);

            // Hapus file gambar dari server
            $file_path = "../../uploads/banners/" . $gambar_nama;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Banner berhasil dihapus.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Banner tidak ditemukan.'];
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Gagal menghapus banner.'];
    }
    header('Location: banner.php');
    exit();
}
?>