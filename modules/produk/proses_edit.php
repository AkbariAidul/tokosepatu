<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nama_produk = trim($_POST['nama_produk']);
    $kategori_id = $_POST['kategori_id'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambar_lama = $_POST['gambar_lama'];
    $gambar_baru = $gambar_lama;

    if (empty($id) || empty($nama_produk) || empty($kategori_id) || empty($harga) || empty($stok)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Semua field wajib diisi.'];
        header("Location: edit.php?id=$id");
        exit();
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "../../uploads/";
        $gambar_baru = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_baru;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'File bukan gambar.'];
            header("Location: edit.php?id=$id");
            exit();
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Hanya format JPG, JPEG, & PNG yang diizinkan.'];
            header("Location: edit.php?id=$id");
            exit();
        }

        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        } else {
             $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Terjadi kesalahan saat upload gambar baru.'];
             header("Location: edit.php?id=$id");
             exit();
        }
    }
    
    try {
        $sql = "UPDATE produk SET nama_produk = ?, kategori_id = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_produk, $kategori_id, $deskripsi, $harga, $stok, $gambar_baru, $id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Produk berhasil diperbarui.'];
        header('Location: produk.php');
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()];
        header("Location: edit.php?id=$id");
    }
} else {
    header('Location: produk.php');
    exit();
}
?>