<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $kategori_id = $_POST['kategori_id'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambar_nama = '';

    if (empty($nama_produk) || empty($kategori_id) || empty($harga) || empty($stok)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Semua field wajib diisi.'];
        header('Location: tambah.php');
        exit();
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/";
        $gambar_nama = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_nama;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'File bukan gambar.'];
            header('Location: tambah.php');
            exit();
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Hanya format JPG, JPEG, & PNG yang diizinkan.'];
            header('Location: tambah.php');
            exit();
        }

        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Terjadi kesalahan saat upload gambar.'];
            header('Location: tambah.php');
            exit();
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Gambar produk wajib diupload.'];
        header('Location: tambah.php');
        exit();
    }
    
    try {
        $sql = "INSERT INTO produk (nama_produk, kategori_id, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_produk, $kategori_id, $deskripsi, $harga, $stok, $gambar_nama]);
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Produk berhasil ditambahkan.'];
        header('Location: produk.php');
    } catch (PDOException $e) {
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Terjadi kesalahan: ' . $e->getMessage()];
        header('Location: tambah.php');
    }
}
?>