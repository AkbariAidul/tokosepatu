<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $kategori_id = $_POST['kategori_id'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $berat = $_POST['berat'];
    // ... (di dalam blok try)
    // Ubah SQL INSERT menjadi:
    $sql = "INSERT INTO produk (nama_produk, kategori_id, deskripsi, harga, stok, gambar, berat) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // Tambahkan $berat di execute:
    $stmt->execute([$nama_produk, $kategori_id, $deskripsi, $harga, $stok, $gambar_nama, $berat]);
    $gambar_nama = '';

    if (empty($nama_produk) || empty($kategori_id) || empty($harga) || empty($stok)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Semua field wajib diisi.'];
        header('Location: tambah.php');
        exit();
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "../../uploads/";
        
        // Buat nama file unik
        $imageFileType = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $gambar_nama = uniqid('prod_') . '.' . $imageFileType;
        $destination_path = $target_dir . $gambar_nama;
        $source_path = $_FILES["gambar"]["tmp_name"];

        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Hanya format JPG, JPEG, PNG, & GIF yang diizinkan.'];
            header('Location: tambah.php');
            exit();
        }

        // Panggil fungsi optimasi
        if (optimize_image($source_path, $destination_path)) {
            // Jika berhasil, lanjutkan proses simpan ke database
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Terjadi kesalahan saat mengoptimasi gambar.'];
            header('Location: tambah.php');
            exit();
        }

    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Gambar produk wajib diupload.'];
        header('Location: tambah.php');
        exit();
    }
    
    // --- AKHIR DARI PROSES GAMBAR ---

    try {
        $sql = "INSERT INTO produk (nama_produk, kategori_id, deskripsi, harga, stok, gambar, berat) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        // Pastikan variabel $berat sudah didefinisikan dari $_POST['berat'] di atas
        $berat = $_POST['berat'] ?? 1000;
        $stmt->execute([$nama_produk, $kategori_id, $deskripsi, $harga, $stok, $gambar_nama, $berat]);
        
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